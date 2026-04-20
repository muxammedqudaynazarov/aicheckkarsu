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
use Gemini\Laravel\Facades\Gemini;
use Gemini\Data\Blob;
use Gemini\Enums\MimeType;

class CheckLessonFiles implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $lesson;
    public $timeout = 7200; // Job ishlash vaqti 2 soat

    public function __construct(Lesson $lesson)
    {
        $this->lesson = $lesson;
    }

    /**
     * Kerakli miqdordagi RPD ga ega bo'lgan akkauntni qidiradi
     */
    private function getAvailableApiAccount($requiredRpd = 1)
    {
        // 1. Avval RPD soni fayllar sonidan katta yoki teng bo'lgan eng birinchi akkauntni olamiz
        $account = Account::where('status', '0')
            ->where('rpd', '>=', $requiredRpd)
            ->orderBy('id')
            ->first();

        // 2. Agar aynan shuncha limitli akkaunt topilmasa, borini (RPD > 0) olamiz
        if (!$account) {
            $account = Account::where('status', '0')
                ->where('rpd', '>', 0)
                ->orderBy('rpd', 'desc') // Limiti eng ko'pini birinchi oladi
                ->first();
        }

        if (!$account) {
            throw new \Exception("Bo‘sh yoki limiti yetarli API kalitlar topilmadi.");
        }

        return $account;
    }

    /**
     * @throws \Exception
     */
    public function handle(): void
    {
        $this->lesson->update(['status' => '1']);

        // Tekshirilishi kerak bo'lgan fayllarni olish
        $files = $this->lesson->files()
            ->with('student')
            ->where('participant', '0')
            ->where('status', '0')
            ->get();

        $filesCount = $files->count();

        if ($filesCount === 0) {
            $this->lesson->update(['status' => '2']);
            return;
        }

        // Dastlabki akkauntni tanlash (RPD >= filesCount mantiqida)
        $apiAccount = $this->getAvailableApiAccount($filesCount);
        $apiAccount->update(['status' => '1']); // Akkauntni band qilish
        $this->lesson->update(['account_id' => $apiAccount->id]);

        foreach ($files as $file) {
            try {
                $file->update(['status' => '1']); // Faylni 'tekshirilmoqda' holatiga o'tkazish

                if (!Storage::disk('public')->exists($file->file_url)) {
                    $file->update(['status' => '3']); // Xato holati
                    Log::error("Fayl topilmadi: " . $file->file_url);
                    continue;
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
                    new Blob(
                        mimeType: MimeType::APPLICATION_PDF,
                        data: $fileContent
                    )
                ]);

                $responseText = $result->text();
                preg_match('/\{[\s\S]*}/', $responseText, $matches);
                $jsonString = $matches[0] ?? '{}';

                // Muvaffaqiyatli zaprosdan keyin RPD ni kamaytiramiz
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
                    $file->update(['status' => '3']); // JSON parseda xatolik bo'lsa
                    Log::warning("Gemini JSON xatosi File ID: {$file->id}. Javob: " . $responseText);
                }
                event(new LessonProgressUpdated($this->lesson->id));
                // AKKAUNT LIMITI TUGASA ALMASHTIRISH MANTIG'I
                if ($apiAccount->refresh()->rpd <= 0) {
                    $apiAccount->update(['status' => '2']); // Limiti tugadi deb belgilaymiz
                    try {
                        // Keyingi fayllar uchun yangi akkaunt qidiramiz
                        $apiAccount = $this->getAvailableApiAccount(1);
                        $apiAccount->update(['status' => '1']); // Yangisini band qilamiz
                        $this->lesson->update(['account_id' => $apiAccount->id]);
                    } catch (\Exception $e) {
                        Log::warning("Dars jarayonida bo'sh akkaunt qolmadi. Qolgan fayllar keyin tekshiriladi.");
                        break; // Boshqa akkaunt yo'q bo'lsa, tsiklni to'xtatamiz
                    }
                }

                sleep(3); // API bloklanmasligi uchun pauza

            } catch (\Exception $e) {
                // Xatolik 429 (Too Many Requests) bo'lsa
                if (str_contains($e->getMessage(), '429') || str_contains($e->getMessage(), 'Quota exceeded')) {
                    $apiAccount->update(['status' => '2', 'rpd' => 0]);
                    $file->update(['status' => '0']); // Faylni qayta tekshirish uchun 0 ga qaytaramiz
                    Log::warning("API Limit tugadi (429). Akkaunt bloklandi.");

                    try {
                        $apiAccount = $this->getAvailableApiAccount(1);
                        $apiAccount->update(['status' => '1']);
                        $this->lesson->update(['account_id' => $apiAccount->id]);
                    } catch (\Exception $ex) {
                        break;
                    }
                } else {
                    $file->update(['status' => '3']); // Boshqa noma'lum xato
                    Log::error("Jiddiy xatolik (File ID: {$file->id}): " . $e->getMessage());
                }
                event(new LessonProgressUpdated($this->lesson->id));
            }
        }

        // Ish tugagach oxirgi foydalanilgan akkaunt holatini bo'shatish
        if (isset($apiAccount) && $apiAccount->status == '1') {
            $apiAccount->update(['status' => '0']);
        }

        // Agar barcha fayllar muvaffaqiyatli (2) yoki xato (3) bilan tugagan bo'lsa darsni yakunlaymiz
        $remainingFiles = $this->lesson->files()->where('participant', '0')->whereIn('status', ['0', '1'])->count();
        if ($remainingFiles === 0) {
            $this->lesson->update(['status' => '2']);
        } else {
            $this->lesson->update(['status' => '0']); // Hali tugamagan bo'lsa, qolganini tugatish uchun 0 ga qaytaramiz
        }
        event(new LessonProgressUpdated($this->lesson->id));
    }
}
