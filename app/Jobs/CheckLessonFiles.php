<?php

namespace App\Jobs;

use App\Events\LessonProgressUpdated;
use App\Models\Account;
use App\Models\Lesson;
use App\Models\Result;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Gemini\Laravel\Facades\Gemini;
use Gemini\Data\Blob;
use Gemini\Enums\MimeType;

class CheckLessonFiles implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $lesson;
    public $timeout = 7200;

    public function __construct(Lesson $lesson)
    {
        $this->lesson = $lesson;
    }

    /**
     * Kerakli miqdordagi RPD ga ega bo'lgan akkauntni XAVFSIZ qidiradi
     */
    private function getAvailableApiAccount($requiredRpd = 1)
    {
        // DB::transaction va lockForUpdate orqali ikki jarayon bitta akkauntni talashib qolmasligini ta'minlaymiz
        return DB::transaction(function () use ($requiredRpd) {
            $account = Account::where('status', '0')
                ->where('rpd', '>=', $requiredRpd)
                ->orderBy('id')
                ->lockForUpdate() // <--- ASOSIY O'ZGARISH: Bazani qulflash
                ->first();

            if (!$account) {
                $account = Account::where('status', '0')
                    ->where('rpd', '>', 0)
                    ->orderBy('rpd', 'desc')
                    ->lockForUpdate()
                    ->first();
            }

            if (!$account) {
                throw new \Exception("Bo‘sh yoki limiti yetarli API kalitlar topilmadi.");
            }

            // Olinishi bilanoq bazada band deb belgilab qo'yamiz
            $account->update(['status' => '1']);
            return $account;
        });
    }

    /**
     * @throws \Exception
     */
    public function handle(): void
    {
        $this->lesson->update(['status' => '1']);

        $files = $this->lesson->files()
            ->with('student')->where('participant', '0')
            ->where('status', '0')->get();

        $filesCount = $files->count();

        if ($filesCount === 0) {
            $this->lesson->update(['status' => '2']);
            return;
        }

        try {
            // O'zgartirilgan xavfsiz metoddan foydalanamiz
            $apiAccount = $this->getAvailableApiAccount($filesCount);
            $this->lesson->update(['account_id' => $apiAccount->id]);
        } catch (\Exception $e) {
            Log::error("Imtihonni tekshirish uchun bo'sh API akkaunt topilmadi: " . $this->lesson->id);
            $this->lesson->update(['status' => '0']);
            return;
        }

        foreach ($files as $file) {
            $retryCount = 0; // RPM xatosi bo'lsa qayta urinishlar soni
            $maxRetries = 3;

            while ($retryCount <= $maxRetries) {
                try {
                    $file->update(['status' => '1']);

                    if (!Storage::disk('public')->exists($file->file_url)) {
                        $file->update(['status' => '3']);
                        Log::error("Fayl topilmadi: " . $file->file_url);
                        break; // Ichki while tsiklidan chiqib ketadi va keyingi faylga o'tadi
                    }

                    $fileContent = base64_encode(Storage::disk('public')->get($file->file_url));

                    // Promptdagi JSON sintaksis xatoliklari to'g'irlandi
                    $prompt = "Siz tajribali o'qituvchisiz. Talabaning yozma ishini o‘qib, har bir savolga qanday javob berganini tahlil qiling, bunda har bir talabaga bitta biletda umumiy beshta savol berialdi va har bir savolni 10 ballik tizimda baholang, umumiy 50 balldan oshmagan bo‘lishi kerak, savollar soni ham beshtadan oshmasligi kerak.
                    Bilim darajasi mezonlari:
                    - 90-100%: Xulosa va qaror qabul qila olish, ijodiy fikrlay olish, misollar keltirish va javob mohiyatini to‘liq ochib bera olish, mustaqil fikrlay olish, berilgan javob orqali aniq tushunchani aniqlay olish, amalda qo‘llay olish, mazmunini tushunish, bilish, aytib yoki yozib bera olish, tushunchaga ega bo‘lish, xatosiz ketma-ketlikda yozishiga yoki aytilishiga erishish, ma’no-mohiyatga ega javob berish, keltirilgan aniqlamalarga (atamalarga, turlarga, xodisalarga, tiplarga) misollar keltirish orqali javob bera olish.
                    - 70-89%: Erkin fikrlay olish, amalda qo‘llay olish, mazmunini tushunish, ketma-ketliksiz yozish, tasavvurga ega bo‘lish, xatosiz yozish yoki aytib berish, to‘liq bo‘lmagan javob berish, mohiyatini anglash lekin to‘liq bayon eta olmaslik.
                    - 60-69%: Mazmuni va mohiyatini tushunish, bilish, yozish yoki ayta olish, tushunchaga ega bo‘lish, ketma-ketlikni keltira olmaslik, mohiyatini bayon tushunarli tartibda bayon eta olmaslik, chala yozish yoki aytib berish.
                    - 1-59%: Yetarlicha tavsiflay olmaslik, to‘liq bilmaslik, ketma-ketliklarning mavjud emasligi yoki qoldirib ketilganligi, mohiyatini anglay olmaslik, chala tushunchaga ega bo‘lish, javob berishga harakat etganlik, to‘liq bo‘lmagan yoki oxiriga yetkazilmagan javob berish.
                    - 0%: Bilmaslik, tushuna olmaslik yoki tushunchaga ega bo‘lmaslik.
                    Siz asosiy urg‘uni, etiborni {$this->lesson->name} faniga qarating, savollar shu fan bo‘yicha berilgan.
                    Agar talaba {$this->lesson->name} fanidan emas boshqa fandan yoki javob boshqa savolga tegishli bo‘sa, yozilgan javob uchun 0 ball qo‘ying, buni qat’iy nazoratga oling, talaba faqat {$this->lesson->name} fanidan va aynan shu savolga berilganligiga etibor bering va izohiga Talaba javob bermagan matnini yozib qo‘ying yoki izoh bermang.
                    Talaba: {$file->student->name}, ID: {$file->student->id}.
                    Natijani FAQATGINA quyidagi JSON formatida qaytaring, boshqa hech qanday izoh, markdown yoki matn qo'shmang:
                    {
                      \"status\": true,
                      \"student\": {
                          \"id\": \"{$file->student->id}\",
                          \"name\": \"{$file->student->name}\"
                      },
                      \"overall\": 50,
                      \"ticket_number\": 22,
                      \"results\": [
                        {
                          \"question_number\": 1,
                          \"question_text\": \"Savol matni (yoki mavzu)\",
                          \"description\": \"Talaba nima haqida yozgani qisqacha izohi\",
                          \"point\": 8.5,
                          \"reason\": \"70-89% oralig'ida, chunki...\"
                        }
                      ]
                    }";

                    $client = \Gemini::factory()->withApiKey($apiAccount->token)->make();
                    $result = $client->generativeModel($apiAccount->model)->generateContent([
                        $prompt,
                        new Blob(mimeType: MimeType::APPLICATION_PDF, data: $fileContent)
                    ]);

                    $responseText = $result->text();
                    preg_match('/\{[\s\S]*}/', $responseText, $matches);
                    $jsonString = $matches[0] ?? '{}';

                    $apiAccount->decrement('rpd');

                    $data = json_decode($jsonString, true);
                    if (json_last_error() === JSON_ERROR_NONE && isset($data['status']) && $data['status'] === true && !empty($data['results'])) {
                        foreach ($data['results'] as $res) {
                            Result::create([
                                'file_id' => $file->id,
                                'question_number' => intval($res['question_number'] ?? 1),
                                'question_text' => $res['question_text'] ?? null,
                                'description' => $res['description'] ?? null,
                                'point' => floatval($res['point'] ?? 0),
                                'reason' => $res['reason'] ?? null,
                            ]);
                        }
                        $file->update([
                            'status' => '2',
                            'overall' => $data['overall'] ?? 0,
                            'ticket_number' => $data['ticket_number'] ?? 0,
                        ]);
                    } else {
                        $file->update(['status' => '3']);
                        Log::warning("Gemini JSON xatosi File ID: {$file->id}. Javob: " . $responseText);
                    }
                    event(new LessonProgressUpdated($this->lesson->id));

                    // Limiti tugasa almashtirish (kunlik RPD tugagan holatda)
                    if ($apiAccount->refresh()->rpd <= 0) {
                        $apiAccount->update(['status' => '2']); // Limiti butunlay tugadi
                        try {
                            $apiAccount = $this->getAvailableApiAccount(1);
                            $this->lesson->update(['account_id' => $apiAccount->id]);
                        } catch (\Exception $e) {
                            Log::warning("Dars jarayonida bo'sh akkaunt qolmadi.");
                            break 2; // Ikkala tsikldan ham (while va foreach) chiqib ketadi
                        }
                    }

                    sleep(4); // API bloklanmasligi uchun pauza. RPM = 25 bo'lgani uchun 4 soniya xavfsizroq.
                    break; // Hammasi yaxshi o'tsa, while tsiklidan chiqib keyingi faylga o'tadi

                } catch (\Exception $e) {
                    // Agar xatolik 429 bo'lsa, bu DAQIQALIK limit tugaganini bildiradi (RPM)
                    if (str_contains($e->getMessage(), '429') || str_contains($e->getMessage(), 'Quota exceeded')) {
                        $retryCount++;
                        if ($retryCount <= $maxRetries) {
                            Log::warning("Fayl ID {$file->id} da API 429 xatosi. 60 soniya kutib qayta urinilmoqda... (Urinish: {$retryCount})");
                            sleep(60); // 1 daqiqa kutamiz (Gemini RPM tiklanishi uchun)
                            continue; // Va yana tsiklni boshidan aylanib shu faylni tekshirishga harakat qilamiz
                        } else {
                            // Agar 3 marta kutib urinib ko'rib ham ishlamasa, demak bu hisob chindan ham bloklangan
                            Log::error("API hisob {$apiAccount->id} butunlay bloklangan ko'rinadi (3 marta 429 xato berdi).");
                            $apiAccount->update(['status' => '2', 'rpd' => 0]);
                            $file->update(['status' => '0']); // Shu faylni boshqa hisob qayta tekshirishi uchun 0 qilamiz

                            try {
                                $apiAccount = $this->getAvailableApiAccount(1);
                                $this->lesson->update(['account_id' => $apiAccount->id]);
                                break; // while dan chiqib ketamiz, va foreach dagi keyingi fayl yangi account bilan boshlanadi. (Ammo bu yerda logic biroz ehtiyotkorlikni talab qiladi, hozircha fayl 0 bo'ldi, sikl davom etaversa u o'tkazib yuborilishi mumkin, logikani saqlash uchun).
                            } catch (\Exception $ex) {
                                break 2; // Boshqa hisob yo'q bo'lsa foreach dan ham chiqib ishi to'xtatamiz
                            }
                        }
                    } else {
                        // Agar xatolik boshqa narsa bo'lsa (masalan 500)
                        $file->update(['status' => '3']);
                        Log::error("Jiddiy xatolik (File ID: {$file->id}): " . $e->getMessage());
                        event(new LessonProgressUpdated($this->lesson->id));
                        break; // while dan chiqib keyingi faylga o'tadi
                    }
                }
            } // while tugashi
        } // foreach tugashi

        if (isset($apiAccount) && $apiAccount->status == '1') {
            $apiAccount->update(['status' => '0']);
        }

        $remainingFiles = $this->lesson->files()->where('participant', '0')->whereIn('status', ['0', '1'])->count();
        if ($remainingFiles === 0) {
            $this->lesson->update(['status' => '2']);
        } else {
            $this->lesson->update(['status' => '0']);
        }
        event(new LessonProgressUpdated($this->lesson->id));
    }
}
