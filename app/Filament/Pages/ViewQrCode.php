<?php


namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Services\ExternalApiService;
use Exception;
use Livewire\Component;

class ViewQrCode extends Page
{
    protected static string $view = 'filament.pages.view-qr-code';
    public string $qrCodeUrl;
    public string $errorMessage;

    protected $listeners = ['refreshQrCode'];

    public function mount(string $qrCodeText)
    {
        $this->loadQrCode($qrCodeText);
    }

    public function refreshQrCode()
    {
        $this->loadQrCode($this->qrCodeText);
    }

    private function loadQrCode(string $qrCodeText)
    {
        $apiService = app(ExternalApiService::class);

        try {
            $response = $apiService->getQrCode($qrCodeText);

            if (isset($response['status']) && $response['status'] === 'done' && isset($response['qrCode'])) {
                $this->qrCodeUrl = $response['qrCode'];
            } else {
                $this->errorMessage = "عذرًا، لا يمكن إنشاء رمز QR في الوقت الحالي.";
            }
        } catch (Exception $e) {
            $this->errorMessage = "حدث خطأ أثناء محاولة إنشاء رمز QR: " . $e->getMessage();
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
}