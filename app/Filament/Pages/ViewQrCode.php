<?php




namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Services\ExternalApiService;
use Exception;

class ViewQrCode extends Page
{
    protected static string $view = 'filament.pages.view-qr-code';

    public string $qrCodeUrl;
    public string $errorMessage;

    public function mount(string $qrCodeText)
    {
        $apiService = app(ExternalApiService::class);
            // $this->qrCodeUrl = $qrCodeUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=' . urlencode($qrCodeUrl);

        try {
            // محاولة استدعاء API وإنشاء QR
            $response = $apiService->getQrCode($qrCodeText);

            // تحقق إذا كانت الاستجابة تحتوي على صورة QR
           // تحقق من حالة الاستجابة وإذا كان يحتوي على qrCode
           if (isset($response['status']) && $response['status'] === 'done' && isset($response['qrCode'])) {
            // تعيين qrCodeUrl إلى القيمة base64 للصورة
            // dd($response['qrCode']);
            $this->qrCodeUrl = $response['qrCode'];
        } else {
            $this->errorMessage = "عذرًا، لا يمكن إنشاء رمز QR في الوقت الحالي.";
        }
        } catch (Exception $e) {
            // في حالة حدوث استثناء، قم بتعيين رسالة الخطأ
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