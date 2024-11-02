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
            if (is_string($response) && str_contains($response, '"status":"error"')) {
                $this->errorMessage = "عذرًا، لا يمكن إنشاء رمز QR لأن الملف الشخصي لم يتم دفعه.";
            } else {
                // تعيين رابط الصورة إذا لم يكن هناك خطأ
                $this->qrCodeUrl = $response;
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