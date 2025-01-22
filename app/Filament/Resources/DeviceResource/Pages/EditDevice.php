<?php

namespace App\Filament\Resources\DeviceResource\Pages;

use Filament\Actions;
use App\Services\ExternalApiService;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use App\Filament\Resources\DeviceResource;

class EditDevice extends EditRecord
{
    protected static string $resource = DeviceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

 // when edit

//  protected function mutateFormDataBeforeSave(array $data): array
//  {
// // dd($this->record->profile_id);
//      // التأكد من أن profile_id و webhook_url موجودين
//      if (isset($this->record->profile_id) && isset($data['webhook_url'])) {
//          $apiService = app(ExternalApiService::class);

//          try {
//              // استدعاء API لتحديث رابط الـ Webhook
//              $response = $apiService->setWebhookUrl(
//                  $this->record->profile_id,
//                  $data['webhook_url'],
//                  'StrongPassword' // كلمة المرور كما هو مذكور في الرابط
//              );
//             //  dd($response);
//              // التحقق من نجاح الاستجابة
//              if (isset($response['status']) && $response['status'] === 'done') {
//                  Notification::make()
//                      ->title('تم تحديث رابط الـ Webhook بنجاح.')
//                      ->success()
//                      ->send();
//              } else {
//                  throw new \Exception('Failed to set webhook URL. Response: '.json_encode($response));
//              }
//          } catch (\Exception $e) {
//              \Log::error('Error setting webhook URL: '.$e->getMessage());
//              dd($e->getMessage());
//              Notification::make()
//                  ->title('حدث خطأ أثناء تحديث رابط الـ Webhook.')
//                  ->danger()
//                  ->send();
//          }
//      }

//      return $data;
//  }


    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
