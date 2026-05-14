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
        return DB::transaction(function () use ($requiredRpd) {
            $account = Account::where('status', '0')
                ->where('rpd', '>=', $requiredRpd)
                ->orderBy('id')
                ->lockForUpdate()
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

        // Tekshirilishi kerak bo'lgan yoki oldingi safar xatolik bergan (status=3) fayllarni ham olishimiz mumkin,
        // Lekin hozircha faqat yangilarini (0) olamiz.
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

        try {
            $apiAccount = $this->getAvailableApiAccount($filesCount);
            $this->lesson->update(['account_id' => $apiAccount->id]);
        } catch (\Exception $e) {
            Log::error("Imtihonni tekshirish uchun bo'sh API akkaunt topilmadi: " . $this->lesson->id);
            $this->lesson->update(['status' => '0']);
            return;
        }

        foreach ($files as $file) {
            $retryCount = 0;
            $maxRetries = 3;

            while ($retryCount <= $maxRetries) {
                try {
                    $file->update(['status' => '1']);

                    if (!Storage::disk('public')->exists($file->file_url)) {
                        $file->update(['status' => '3']);
                        Log::error("Fayl topilmadi: " . $file->file_url);
                        break;
                    }

                    $fileContent = base64_encode(Storage::disk('public')->get($file->file_url));

                    // PROMPT TO'LIQ O'ZGARTIRILDI: Bitta qat'iy JSON qolipga keltirildi
                    $prompt = "Siz tajribali o'qituvchisiz. Talabaning yozma ishini o‘qib, tahlil qiling. Bu oddiy bilet (5 ta savol) yoki assisment (ixtiyoriy savollar soni) bo'lishi mumkin.
                    Maksimal jami ball 50 ball. Agar savollar soni 5 ta bo'lsa, har biri 10 balldan. Agar savollar soni boshqacha bo'lsa, 50 ballni savollar soniga teng proporsional taqsimlang.
                    Javob berilmagan yoki {$this->lesson->name} faniga aloqasi bo'lmagan javoblarga 0 ball qo'ying.

                    Bilim darajasi mezonlari:
                    - 90-100%: Xulosa va qaror qabul qila olish, ijodiy fikrlay olish, misollar keltirish va javob mohiyatini to‘liq ochib bera olish, mustaqil fikrlay olish, berilgan javob orqali aniq tushunchani aniqlay olish, amalda qo‘llay olish, mazmunini tushunish, bilish, aytib yoki yozib bera olish, tushunchaga ega bo‘lish, xatosiz ketma-ketlikda yozishiga yoki aytilishiga erishish, ma’no-mohiyatga ega javob berish, keltirilgan aniqlamalarga (atamalarga, turlarga, xodisalarga, tiplarga) misollar keltirish orqali javob bera olish.
                    - 70-89%: Erkin fikrlay olish, amalda qo‘llay olish, mazmunini tushunish, ketma-ketliksiz yozish, tasavvurga ega bo‘lish, xatosiz yozish yoki aytib berish, to‘liq bo‘lmagan javob berish, mohiyatini anglash lekin to‘liq bayon eta olmaslik.
                    - 60-69%: Mazmuni va mohiyatini tushunish, bilish, yozish yoki ayta olish, tushunchaga ega bo‘lish, ketma-ketlikni keltira olmaslik, mohiyatini bayon tushunarli tartibda bayon eta olmaslik, chala yozish yoki aytib berish.
                    - 1-59%: Yetarlicha tavsiflay olmaslik, to‘liq bilmaslik, ketma-ketliklarning mavjud emasligi yoki qoldirib ketilganligi, mohiyatini anglay olmaslik, chala tushunchaga ega bo‘lish, javob berishga harakat etganlik, to‘liq bo‘lmagan yoki oxiriga yetkazilmagan javob berish.
                    - 0%: Bilmaslik, tushuna olmaslik yoki tushunchaga ega bo‘lmaslik.

                    Talaba nomi: {$file->student->name}

                    DIQQAT: Javobingiz FAQATGINA bitta YAGONA JSON formatida bo'lishi shart. Hech qanday Markdown (```json) va qo'shimcha matnlarsiz aynan shu strukturani qaytaring:
                    {
                      \"status\": true,
                      \"overall\": 45.5,
                      \"ticket_number\": 22,
                      \"results\": [
                        {
                          \"question_number\": 1,
                          \"question_text\": \"Savol matni yoki tartibi\",
                          \"description\": \"Talaba javobining qisqacha izohi\",
                          \"point\": 9.5,
                          \"reason\": \"Nega shuncha ball qo'yilgani izohi\"
                        }
                      ]
                    }";

                    $client = \Gemini::factory()->withApiKey($apiAccount->token)->make();
                    $result = $client->generativeModel($apiAccount->model)->generateContent([
                        $prompt,
                        new Blob(mimeType: MimeType::APPLICATION_PDF, data: $fileContent)
                    ]);

                    // JSON'ni Markdown qoldiqlaridan tozalash
                    $responseText = $result->text();
                    $responseText = str_replace(['```json', '```'], '', $responseText);
                    $responseText = trim($responseText);

                    // Eng birinchi { va eng oxirgi } oraliqni ajratib olish
                    preg_match('/\{[\s\S]*\}/', $responseText, $matches);
                    $jsonString = $matches[0] ?? '{}';

                    $apiAccount->decrement('rpd');

                    $data = json_decode($jsonString, true);

                    if (json_last_error() === JSON_ERROR_NONE && isset($data['status']) && $data['status'] === true && !empty($data['results'])) {

                        // Eski natijalarni tozalash (agar qayta tekshirayotgan bo'lsa duplikat bo'lmasligi uchun)
                        Result::where('file_id', $file->id)->delete();

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
                        Log::warning("Gemini JSON parse xatosi (File ID: {$file->id}). Javob: " . $responseText);
                    }
                    event(new LessonProgressUpdated($this->lesson->id));

                    // Kunlik Limit (RPD) tugasa almashtirish
                    if ($apiAccount->refresh()->rpd <= 0) {
                        $apiAccount->update(['status' => '2']);
                        try {
                            $apiAccount = $this->getAvailableApiAccount(1);
                            $this->lesson->update(['account_id' => $apiAccount->id]);
                        } catch (\Exception $e) {
                            Log::warning("Dars jarayonida bo'sh akkaunt qolmadi.");
                            break 2; // Ikkala tsikldan chiqish
                        }
                    }

                    sleep(4);
                    break; // Muvaffaqiyatli o'tsa While (retry) tsiklidan chiqadi va keyingi faylga o'tadi

                } catch (\Exception $e) {
                    if (str_contains($e->getMessage(), '429') || str_contains($e->getMessage(), 'Quota exceeded')) {
                        $retryCount++;
                        if ($retryCount <= $maxRetries) {
                            $waitTime = 20 * $retryCount; // 20s, 40s, 60s o'sib boruvchi pauza
                            Log::warning("Fayl ID {$file->id} API 429 xatosi. {$waitTime}s kutib qayta urinilmoqda... ({$retryCount}/{$maxRetries})");
                            sleep($waitTime);
                            continue; // Shu talaba faylini qayta tekshiradi
                        } else {
                            Log::error("API hisob {$apiAccount->id} bloklandi.");
                            $apiAccount->update(['status' => '2', 'rpd' => 0]);

                            try {
                                $apiAccount = $this->getAvailableApiAccount(1);
                                $this->lesson->update(['account_id' => $apiAccount->id]);
                                // YECHIM: Break o'rniga continue, shunda yangi akkaunt bilan aynan SHU TALABANI tekshirishni qayta boshlaydi!
                                $retryCount = 0;
                                continue;
                            } catch (\Exception $ex) {
                                $file->update(['status' => '0']); // Agar umuman hisob qolmasa nolga qaytaramiz
                                break 2;
                            }
                        }
                    } else {
                        // Boshqa ichki xatolar (Server 500, Timeout)
                        $file->update(['status' => '3']);
                        Log::error("Jiddiy xatolik (File ID: {$file->id}): " . $e->getMessage());
                        event(new LessonProgressUpdated($this->lesson->id));
                        break;
                    }
                }
            } // while
        } // foreach

        if (isset($apiAccount) && $apiAccount->status == '1') {
            $apiAccount->update(['status' => '0']);
        }

        $remainingFiles = $this->lesson->files()->where('participant', '0')->whereIn('status', ['0', '1', '3'])->count();
        if ($remainingFiles === 0) {
            $this->lesson->update(['status' => '2']);
        } else {
            $this->lesson->update(['status' => '0']);
        }
        event(new LessonProgressUpdated($this->lesson->id));
    }
}
