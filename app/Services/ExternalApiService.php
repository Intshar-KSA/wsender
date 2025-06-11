<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class ExternalApiService
{
    protected $baseUrl;

    protected $headers;

    public function __construct()
    {
        $this->baseUrl = 'https://wappi.pro/api';
        $this->headers = [
            'accept' => 'application/json',
            'Authorization' => '40703bb7812b727ec01c24f2da518c407342559c',
            'Content-Type' => 'application/json',
        ];
    }

    public function addProfile($profileId, $name, $webhookUrl)
    {
        $webhookUrl = route('webhook.handle');
        $url = 'https://wappi.pro/api/profile/add';
        $url .= '?profile_id='.urlencode($profileId);
        $url .= '&name='.urlencode($name);
        $url .= '&webhook_url='.urlencode($webhookUrl);

        $response = Http::withHeaders($this->headers)->post($url, []);

        return $response->json();
    }

    public function getProfiles()
    {
        $response = Http::withHeaders($this->headers)->timeout(30)->get($this->baseUrl.'/profile/all/get');
        \Log::info('response: '.$response->body());
        if ($response->ok()) {
            return $response->json()['profiles'];
        }

        return [];
    }

    public function deleteProfile(string $profileId)
    {
        $url = $this->baseUrl.'/profile/delete?profile_id='.urlencode($profileId);

        $response = Http::withHeaders($this->headers)->post($url);

        \Log::info('response: '.$response->body());
        if ($response->successful()) {
            return $response->json();
        }

        // التحقق من نوع الخطأ في الاستجابة
        $responseBody = $response->json();
        if (isset($responseBody['status']) && $responseBody['status'] === 'error' && $responseBody['detail'] === 'Profile not found') {
            \Log::warning("Profile not found for ID: {$profileId}. Proceeding with local deletion.");

            return ['status' => 'done', 'detail' => 'Profile not found']; // السماح بالحذف المحلي
        }

        throw new \Exception('Failed to delete profile via API. Response: '.$response->body());
    }

    // public function getQrCode(string $profileId)
    // {
    //     $url = $this->baseUrl.'/sync/qr/get?profile_id='.urlencode($profileId);

    //     $response = Http::withHeaders($this->headers)->get($url);

    //     if ($response->successful()) {
    //         return $response->json();
    //     }

    //     throw new \Exception('Failed to get QR code via API. Response: '.$response->body());
    // }

    // app/Services/ExternalApiService.php
// app/Services/ExternalApiService.php
public function getQrCode(string $profileId): array
{
    $url = $this->baseUrl . '/sync/qr/get?profile_id=' . urlencode($profileId);

    $response = Http::withHeaders($this->headers)
                    // ->timeout(60)              // زمن انتظار اختياري
                    // ->retry(1, 250)            // إعادة محاولة وحيدة
                    ->get($url);

    /* ─────────────────────────
       1) استجابة HTTP 200 «نجاح»
       ───────────────────────── */
    if ($response->successful()) {
        return $response->json();              // يتضمن qrCode + status=done
    }

    /* ─────────────────────────
       2) استجابات منطقية نتوقّعها
       409  => already authorized
       404  => profile not found
       422  => profile needs refresh / QR expired
       ───────────────────────── */
    $json = $response->json();                 // قد يُعيد null إذا ليس JSON
    $detail = $json['detail'] ?? '';

    // «جهاز مرتبط مسبقًا»
    if ($response->status() === 409 || $detail === 'You are already authorized') {
        return [
            'status' => 'authorized',
            'detail' => 'You are already authorized',
        ];
    }

    // «الملف غير موجود»
    if ($response->status() === 404 || $detail === 'Profile not found') {
        return [
            'status' => 'not_found',
            'detail' => 'Profile not found',
        ];
    }

    // «كود منتهٍ / بحاجة تحديث»
    if ($response->status() === 422 || str_contains($detail, 'expired')) {
        return [
            'status' => 'expired',
            'detail' => $detail ?: 'QR expired',
        ];
    }

    /* ─────────────────────────
       3) باقي أخطاء الـ 4xx: أعِدها ليعالجها الأعلى إن رغبت
       ───────────────────────── */
    if ($response->status() >= 400 && $response->status() < 500) {
        return [
            'status'     => 'client_error',
            'http_code'  => $response->status(),
            'detail'     => $detail ?: $response->body(),
        ];
    }

    /* ─────────────────────────
       4) أخطاء حرجة (5xx) أو شبكة: أرمِ استثناء
       ───────────────────────── */
    throw new \Exception(
        sprintf(
            'QR API failure (%s): %s',
            $response->status(),
            $response->body()
        )
    );
}



    public function sendMessage(string $profileId, string $recipient, string $body)
    {
        $url = $this->baseUrl.'/sync/message/send?profile_id='.urlencode($profileId);

        $response = Http::withHeaders($this->headers)->post($url, [
            'body' => $body,
            'recipient' => $recipient,
        ]);

        if ($response->successful()) {
            return $response->json();
        }

        throw new \Exception('Failed to send message via API. Response: '.$response->body());
    }

    public function sendImage(string $profileId, string $recipient, string $caption, string $b64_file)
    {
        $url = $this->baseUrl.'/sync/message/img/send?profile_id='.urlencode($profileId);

        $response = Http::withHeaders($this->headers)->post($url, [
            'recipient' => $recipient,
            'caption' => $caption,
            'b64_file' => $b64_file,
        ]);

        if ($response->successful()) {
            return $response->json();
        }

        throw new \Exception('Failed to send image via API. Response: '.$response->body());
    }

    public function sendDocument(string $profileId, string $recipient, string $caption, string $file_name, string $b64_file)
    {
        $url = $this->baseUrl.'/sync/message/document/send?profile_id='.urlencode($profileId);

        $response = Http::withHeaders($this->headers)->post($url, [
            'recipient' => $recipient,
            'caption' => $caption,
            'file_name' => $file_name,
            'b64_file' => $b64_file,
        ]);

        if ($response->successful()) {
            return $response->json();
        }

        throw new \Exception('Failed to send document via API. Response: '.$response->body());
    }

    public function setWebhookUrl(string $profileId, string $url, string $auth)
    {
        $apiUrl = 'https://wappi.pro/api/webhook/url/set';
        $response = Http::withHeaders($this->headers)->post("{$apiUrl}?profile_id={$profileId}&url={$url}&auth={$auth}");

        return $response->json();
    }
}
