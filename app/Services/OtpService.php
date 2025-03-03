<?php
namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OtpService
{
    protected string $baseUrl = 'https://login.isender360.com/api/sync/message/send';
    protected string $imageBaseUrl = 'https://wappi.pro/api/sync/message/img/send';
    protected string $profileId;

    public function __construct(?string $profileId = null)
    {
        // تعيين `profile_id` تلقائيًا، وإذا لم يتم تمريره يتم استخدام القيمة الافتراضية
        $this->profileId = $profileId ?? '2fdc9526-cccd';
    }

    /**
     * 📌 إرسال OTP عبر واتساب.
     */
    public function sendOtp(string $phone, string $message): bool
    {
        return $this->sendViaWhatsapp($phone, $message);
    }

    /**
     * 📌 إرسال رسالة نصية عبر واتساب.
     */
    public function sendViaWhatsapp(string $phone, string $message): bool
    {
        if (empty($phone)) {
            Log::error('Phone number is missing or invalid.', ['phone' => $phone]);
            return false;
        }

        Log::info('Sending WhatsApp message...', ['phone' => $phone, 'message' => $message]);

        $response = Http::asForm()->post("{$this->baseUrl}?profile_id={$this->profileId}", [
            'recipient' => $phone,
            'body' => $message,
        ]);

        Log::info('WhatsApp API Response', [
            'status' => $response->status(),
            'body' => $response->body(),
        ]);

        return $response->ok();
    }

    /**
     * 📌 إرسال رسالة عبر واتساب مع صورة.
     */
    public function sendViaWhatsappWithImage(string $phone, string $type, string $title, string $message, ?string $imagePath = null): bool
    {
        if (empty($phone)) {
            Log::error('Phone number is missing or invalid.', ['phone' => $phone]);
            return false;
        }

        // تكوين النص المرسل بعد الصورة
        $caption = trim("{$type}\n{$title}\n{$message}");

        // إعداد البيانات الأساسية
        $payload = [
            'recipient' => $phone,
            'caption' => $caption,
        ];

        // إضافة الصورة إذا كانت موجودة
        if ($imagePath && file_exists(storage_path("app/{$imagePath}"))) {
            $imageBase64 = base64_encode(file_get_contents(storage_path("app/{$imagePath}")));
            $payload['b64_file'] = $imageBase64;
        }

        Log::info('Sending WhatsApp message with image...', [
            'phone' => $phone,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'image' => $imagePath ? 'Included' : 'Not included',
        ]);

        $response = Http::withHeaders([
            'accept' => 'application/json',
            'Authorization' => '40703bb7812b727ec01c24f2da518c407342559c',
            'Content-Type' => 'application/json',
        ])->post("{$this->imageBaseUrl}?profile_id={$this->profileId}", $payload);

        Log::info('WhatsApp API Response', [
            'status' => $response->status(),
            'body' => $response->body(),
        ]);

        return $response->ok();
    }
}
