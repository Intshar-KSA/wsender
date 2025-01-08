<?php

namespace App\Filament\Resources\DeviceResource\Pages;

use Filament\Actions;
use App\Services\ExternalApiService;
use Filament\Notifications\Notification;
use App\Filament\Resources\DeviceResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Validation\ValidationException;
use App\Events\DeviceCreated;
use Illuminate\Support\Facades\Log;

class CreateDevice extends CreateRecord
{
    protected static string $resource = DeviceResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if($data['nickname'] == null || $data['nickname'] == ''){

            Notification::make()
            ->title('please enter nickname')
            ->danger()
            ->send();

        throw ValidationException::withMessages([
                'nickname' => 'please enter nickname.',
        ]);


        }

            $apiService = app(ExternalApiService::class);

            // إعداد المعطيات لطلب API
            $profileId ="";
            $name = $data['nickname'];
            $webhookUrl = $data['webhook_url'] ?? '';

            // استدعاء الـ API
            $apiResponse = $apiService->addProfile($profileId, $name, $webhookUrl);

            // يمكنك معالجة الاستجابة هنا إذا كنت بحاجة لذلك
            if (isset($apiResponse['status']) && $apiResponse['status']=="done") {
                $data['profile_id'] = $apiResponse['profile_id'];
                // القيام بشيء ما في حالة النجاح
            } else {
                // معالجة الخطأ
                logger()->error('API Response:', $apiResponse);

                // طرح استثناء مع رسالة مفصلة
                throw new \Exception('Failed to add profile via API. Response: ' . json_encode($apiResponse));
            }






        $data['user_id'] = auth()->id();
        // $data['start_date'] =
        return $data;
    }
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function afterCreate(): void
    {

        Log::info('Device created: ', ['device_id' => $this->record->id]); // تسجيل الجهاز

        // حذف الكاش لتحديث البيانات
        cache()->forget('profiles_cache');
        // استدعاء الحدث بعد إنشاء الجهاز
        DeviceCreated::dispatch($this->record);
    }

}
