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
        $this->profileId = $profileId ?? '2fdc9526-cccd';
    }

    public function sendOtp(string $phone, string $message): bool
    {
        return $this->sendViaWhatsapp($phone, $message);
    }

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

    public function sendViaWhatsappWithImage(string $phone, string $type, string $title, string $message, ?string $imagePath = null): bool
    {
        if (empty($phone)) {
            Log::error('Phone number is missing or invalid.', ['phone' => $phone]);

            return false;
        }

        $caption = trim("{$type}\n{$title}\n{$message}");

        $payload = [
            'recipient' => $phone,
            'caption' => $caption,
        ];

        if ($imagePath) {
            // $fullPath = storage_path("app/{$imagePath}");
            $fullPath = storage_path('app/public/'.$imagePath);

            if (file_exists($fullPath)) {
                Log::info("✅ File found at: {$fullPath}");
                $imageBase64 = base64_encode(file_get_contents($fullPath));
                $payload['b64_file'] = $imageBase64;
            } else {
                Log::warning("❌ File does not exist at: {$fullPath}");
            }
        }
        $mimeType = mime_content_type($fullPath);
        Log::info("🔍 File MIME type: {$mimeType}");

        Log::info('📤 Sending WhatsApp Message...', [
            'phone' => $phone,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'image' => $imagePath ? 'Included' : 'Not included',
            'profile_id' => $this->profileId,
            'payload' => $payload,
        ]);

        $response = Http::withHeaders([
            'accept' => 'application/json',
            'Authorization' => '40703bb7812b727ec01c24f2da518c407342559c',
            'Content-Type' => 'application/json',
        ])->post("{$this->imageBaseUrl}?profile_id={$this->profileId}", $payload);

        Log::info('📨 WhatsApp API Response', [
            'status_code' => $response->status(),
            'response_body' => $response->body(),
        ]);

        return $response->ok();
    }
}
