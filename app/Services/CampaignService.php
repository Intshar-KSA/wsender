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

class CampaignService
{





    public static function pauseCampaign(Campaign $campaign)
    {
        // dd($campaign->mass_prsting_id);
        $headers = [
            'accept' => 'application/json',
            'Authorization' => '40703bb7812b727ec01c24f2da518c407342559c',
        ];

        // الحصول على الجهاز المرتبط بالحملة
        $device = $campaign->device;

        // استدعاء API لإيقاف الحملة
        $response = Http::withHeaders($headers)->get('https://wappi.pro/mailings/pause', [
            'profile_id' => $device->profile_id,
            'mass_posting_id' => $campaign->mass_prsting_id,
        ]);

        if ($response->successful()) {
            \Log::info('Campaign paused successfully:', $response->json());
        } else {
            \Log::error('Failed to pause campaign:', [
                'status' => $response->status(),
                'error' => $response->body(),
            ]);
        }

        // تحديث حالة الحملة إلى "paused"
        $campaign->update(['status' => 'paused']);

        return $response->json();
    }





    public static function resumeCampaign(Campaign $campaign)
    {

        $headers = [
            'accept' => 'application/json',
            'Authorization' => '40703bb7812b727ec01c24f2da518c407342559c',
        ];

        // الحصول على الجهاز المرتبط بالحملة
        $device = $campaign->device;

        // استدعاء API لاستئناف الحملة
        $response = Http::withHeaders($headers)->get('https://wappi.pro/mailings/start', [
            'profile_id' => $device->profile_id,
            'mass_posting_id' => $campaign->mass_prsting_id,
        ]);

        if ($response->successful()) {
            \Log::info('Campaign resumed successfully:', $response->json());
        } else {
            \Log::error('Failed to resume campaign:', [
                'status' => $response->status(),
                'error' => $response->body(),
            ]);
        }

        // تحديث حالة الحملة إلى "resumed"
        $campaign->update(['status' => 'resumed']);

        return $response->json();
    }




    public static function createCampaignFromCampaignsTable(Campaign $campaign): void
    {
        try {
            // الحصول على المحتوى المرتبط بالحملة
            $content = $campaign->content;

            // الحصول على الجهاز المرتبط بالحملة
            $device = $campaign->device;

            // الحصول على جهات الاتصال من التصنيفات
            $contactCats = ContactCat::whereIn('id', $campaign->contact_cat_ids)->with('contacts')->get();
            $contacts = $contactCats->flatMap(function ($category) {
                return $category->contacts;
            })->pluck('phone')->toArray();

            if (empty($contacts)) {
                throw new \Exception('No contacts found for the selected categories.');
            }

            // تحويل الملف إلى Base64 إذا كان موجودًا
            $base64 = isset($content->file) && $content->file
                ? base64_encode(Storage::disk('public')->get($content->file))
                : '';

            // إعداد الهيدر
            $headers = [
                'accept' => 'application/json',
                'Authorization' => '40703bb7812b727ec01c24f2da518c407342559c',
            ];
// dd(implode(PHP_EOL, $contacts));
            // إرسال الطلب إلى API
            $response = Http::asMultipart()->withHeaders($headers)->post('https://wappi.pro/mailings/init?profile_id=' . $device->profile_id, [
                'mass_text' => $content->des, // الرسالة النصية
                'mass_phones' =>implode(PHP_EOL, $contacts), // أرقام الهواتف
                'mass_image' => '',
                'mass_timeout_from' => $campaign->message_every - 5,
                'mass_timeout_to' => $campaign->message_every + 5,
                'base64' => $base64,
                'file_name' => $content->title ?? '',
            ]);

            // الحصول على الاستجابة وتحليلها
            $responseBody = $response->json();

            // التحقق من نجاح الطلب
            if ($response->successful() && isset($responseBody['posting']['mass_posting_id'])) {
                // حفظ `mass_posting_id` في الحملة
                $campaign->update([
                    'mass_prsting_id' => $responseBody['posting']['mass_posting_id'],
                    'status' => 'started', // أو أي حالة جديدة تريد تعيينها
                ]);
            } else {
                throw new \Exception($responseBody['detail'] ?? 'Failed to create campaign.');
            }
        } catch (\Exception $e) {
            \Log::error('Error creating campaign: ' . $e->getMessage());
            Notification::make()
                ->title('Error creating campaign')
                ->danger()
                ->send();

            throw ValidationException::withMessages([
                'error' => $e->getMessage(),
            ]);
        }
    }


}
