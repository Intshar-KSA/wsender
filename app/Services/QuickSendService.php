<?php

namespace App\Services;

use App\Models\Device;
use App\Models\Content;
use App\Models\Campaign;
use App\Models\QuickSend;
use App\Models\ContactCat;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Filament\Notifications\Notification;
use Illuminate\Validation\ValidationException;

class QuickSendService
{
    public static function createCampaign(array &$data): void
    {
        try {
            // تحويل الملف إلى Base64 إذا كان موجودًا
            $base64 = isset($data['image']) && $data['image']
                ? base64_encode(Storage::disk('public')->get($data['image']))
                : '';

            // إعداد الهيدر
            $headers = [
                'accept' => 'application/json',
                'Authorization' => '40703bb7812b727ec01c24f2da518c407342559c'
            ];

            // dd($data['phone_numbers']);
            // إرسال الطلب إلى API
            $response = Http::asMultipart()->withHeaders($headers)->post('https://wappi.pro/mailings/init?profile_id=' . $data['profile_id'], [
                'mass_text' => $data['message_text'],
                'mass_phones' => $data['phone_numbers'],
                'mass_image' => '',
                'mass_timeout_from' => $data['timeout_from'],
                'mass_timeout_to' => $data['timeout_to'],
                'base64' => $base64,
                'file_name' => $data['file_name'] ?? ''
            ]);

            // الحصول على الاستجابة وتحليلها
            $responseBody = $response->json();

            // التحقق من نجاح الطلب وإضافة mass_posting_id إلى $data
            if ($response->successful() && isset($responseBody['posting']['mass_posting_id'])) {
                $data['mass_posting_id'] = $responseBody['posting']['mass_posting_id'];
            } else {
                // في حالة حدوث خطأ في الاستجابة
                throw new \Exception($responseBody['detail'] ?? 'Failed to create campaign.');
            }
        } catch (\Exception $e) {
            \Log::error('Error creating campaign: ' . $e->getMessage());
            Notification::make()
            ->title('please enter nickname')
            ->danger()
            ->send();

        throw ValidationException::withMessages([
                'nickname' => 'please enter nickname.',
        ]);
            throw $e; // إعادة رمي الخطأ ليتم التعامل معه في `mutateFormDataBeforeCreate`
        }
    }





    public static function startCampaign($record)
{
    $headers = [
        'accept' => 'application/json',
        'Authorization' => '40703bb7812b727ec01c24f2da518c407342559c',
        // 'Content-Type' => 'application/json',
    ];
    $response = Http::withHeaders($headers)->get('https://wappi.pro/mailings/start', [
        'profile_id' => $record->profile_id,
        'mass_posting_id' => $record->mass_posting_id, // Assuming `id` is used as `mass_posting_id`
    ]);
    \Log::info('Campaign started: ' . $response->json());

    $record->update(['status' => 'started']);
    return $response->json();
}


    public static function pauseCampaign($record)
    {
        $headers = [
            'accept' => 'application/json',
            'Authorization' => '40703bb7812b727ec01c24f2da518c407342559c',
            // 'Content-Type' => 'application/json',
        ];
        // استدعاء API لإيقاف الحملة
        $response = Http::withHeaders($headers)->get('https://wappi.pro/mailings/pause', [
            'profile_id' => $record->profile_id,
            'mass_posting_id' => $record->mass_posting_id,
        ]);
        if ($response->successful()) {
            \Log::info('Campaign started:', $response->json());
        } else {
            \Log::error('Failed to pause campaign:', [
                'status' => $response->status(),
                'error' => $response->body(),
            ]);
        }

        $record->update(['status' => 'paused']);
        return $response->json();
    }

    public static function resumeCampaign($record)
    {
        $headers = [
            'accept' => 'application/json',
            'Authorization' => '40703bb7812b727ec01c24f2da518c407342559c',
            // 'Content-Type' => 'application/json',
        ];
        // استدعاء API لاستئناف الحملة
        $response = Http::withHeaders($headers)->get('https://wappi.pro/mailings/start', [
            'profile_id' => $record->profile_id,
            'mass_posting_id' => $record->mass_posting_id, // Assuming `id` is used as `mass_posting_id`
        ]);
        if ($response->successful()) {
            \Log::info('Campaign started:', $response->json());
        } else {
            \Log::error('Failed to pause campaign:', [
                'status' => $response->status(),
                'error' => $response->body(),
            ]);
        }
        $record->update(['status' => 'resumed']);
        return $response->json();
    }





}
