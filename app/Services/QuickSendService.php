<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class QuickSendService
{
    public static function createCampaign($data)
    {
        // تحويل الملف إلى Base64 إذا كان موجودًا
        $base64 = $data['image']
            ? base64_encode(Storage::get($data['image']))
            : '';

        $response = Http::asMultipart()->post('https://wappi.pro/mailings/init?profile_id=' . $data['profile_id'], [
            'mass_text' => $data['message_text'],
            'mass_phones' => $data['phone_numbers'],
            'mass_image' => '',
            'mass_timeout_from' => $data['timeout_from'],
            'mass_timeout_to' => $data['timeout_to'],
            'base64' => $base64,
            'file_name' => $data['file_name'] ?? 'картинка',
        ]);

        return $response->json();
    }

    public static function pauseCampaign($record)
    {
        // استدعاء API لإيقاف الحملة
        $response = Http::post('https://wappi.pro/mailings/pause', [
            'campaign_id' => $record->id,
        ]);

        $record->update(['status' => 'paused']);
        return $response->json();
    }

    public static function resumeCampaign($record)
    {
        // استدعاء API لاستئناف الحملة
        $response = Http::post('https://wappi.pro/mailings/resume', [
            'campaign_id' => $record->id,
        ]);

        $record->update(['status' => 'resumed']);
        return $response->json();
    }
}
