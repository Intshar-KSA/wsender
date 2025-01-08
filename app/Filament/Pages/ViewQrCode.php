<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Services\ExternalApiService;
use Exception;
use App\Models\Device;
use Filament\Notifications\Notification;

class ViewQrCode extends Page
{
    protected static string $view = 'filament.pages.view-qr-code';
    protected static ?string $pollingInterval = '7s'; // يتم تحديث QR Code كل 7 ثوانٍ تلقائيًا

    public string $qrCodeUrl;
    public string $qrCodeUrl2;
    public string $errorMessage;
    public int $counter = 0;

    protected $listeners = ['refreshQrCode'];

    public function mount(string $qrCodeText)
    {
        $this->qrCodeUrl2 = $qrCodeText;
        $this->loadQrCode($qrCodeText);
    }

    public function refreshQrCode()
    {
        $this->counter++;
        $this->loadQrCode($this->qrCodeUrl2);
    }

    private function loadQrCode(string $qrCodeText)
    {
        $apiService = app(ExternalApiService::class);

        try {
            $response = $apiService->getQrCode($qrCodeText);

            if (isset($response['status']) && $response['status'] === 'done' && isset($response['qrCode'])) {
                $this->qrCodeUrl = $response['qrCode'];
            } else {
                if (isset($response['detail']) && $response['detail'] === 'Profile not found') {
                    // إذا كان البروفايل غير موجود، أنشئ بروفايل جديد
                    $this->handleProfileNotFound();
                } else if (isset($response['detail']) && $response['detail'] === 'You are already authorized') {
                    $this->updateDeviceStatus('active');
                } else {
                    $this->errorMessage = "عذرًا، لا يمكن إنشاء رمز QR في الوقت الحالي.";
                }
            }
        } catch (Exception $e) {
            if (strpos($e->getMessage(), 'Profile not found') !== false) {
                // إذا كان البروفايل غير موجود، أنشئ بروفايل جديد
                $this->handleProfileNotFound();
            } else if (strpos($e->getMessage(), 'You are already authorized') !== false) {
                $this->updateDeviceStatus('active');
            } else {
                $this->errorMessage = "حدث خطأ أثناء محاولة إنشاء رمز QR: " . $e->getMessage();
            }
        }
    }


    public function closeQrCode()
    {
        return redirect('/app/devices');
    }

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }
    private function updateDeviceStatus($status)
{
    // dd($this->qrCodeUrl2);

    $device = Device::where('profile_id', $this->qrCodeUrl2)->first();

    if ($device) {
        // $device->update(['status' => $status]);
        $device->update(['status' => true]); // تحديث الحالة إلى true
        session()->flash('success', 'تم الربط بنجاح.');
        Notification::make()
        ->title('تم ربط الجهاز بنجاح')
        ->success()
        ->send();
        $this->closeQrCode();
        // $this->dispatchBrowserEvent('notify', ['message' => 'تم الربط بنجاح']);
    } else {
        Notification::make()
        ->title('هذا الجهاز غير موجود')
        ->danger()
        ->send();
        // $this->dispatchBrowserEvent('notify', ['message' => 'الجهاز غير موجود']);
    }
    // اكتب هنا الكود الذي يقوم بتحديث حالة الجهاز إلى active
    // يمكنك استخدام موديل الجهاز وتحديد الجهاز المناسب لتحديث حالته
}
private function handleProfileNotFound()
{
    try {
        $device = Device::where('profile_id', $this->qrCodeUrl2)->first();

        if ($device) {
            $apiService = app(ExternalApiService::class);

            // إنشاء بروفايل جديد
            $name = $device->nickname;
            $webhookUrl = $device->webhook_url ?? '';
            $newProfileResponse = $apiService->addProfile('', $name, $webhookUrl);

            if (isset($newProfileResponse['status']) && $newProfileResponse['status'] === 'done') {
                $newProfileId = $newProfileResponse['profile_id'];

                // تحديث السجل بالبروفايل الجديد
                $device->update(['profile_id' => $newProfileId]);

                Notification::make()
                    ->title('تم إنشاء بروفايل جديد وتحديث الجهاز بنجاح.')
                    ->success()
                    ->send();
            } else {
                throw new \Exception('Failed to create a new profile. Response: ' . json_encode($newProfileResponse));
            }
        } else {
            throw new \Exception('Device not found in the database.');
        }
    } catch (Exception $e) {
        \Log::error('Error handling profile not found: ' . $e->getMessage());
        Notification::make()
            ->title('حدث خطأ أثناء محاولة إنشاء بروفايل جديد.')
            ->danger()
            ->send();
        $this->errorMessage = "حدث خطأ أثناء محاولة إنشاء بروفايل جديد: " . $e->getMessage();
    }
}


}
