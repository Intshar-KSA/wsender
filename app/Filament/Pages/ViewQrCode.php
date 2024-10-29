<?php
namespace App\Filament\Pages;

use Filament\Pages\Page;

class ViewQrCode extends Page
{
    // protected static ?string $navigationIcon = 'heroicon-o-qrcode';
    protected static string $view = 'filament.pages.view-qr-code';

    public string $qrCodeUrl;

    public function mount($qrCodeUrl)
    {
        $this->qrCodeUrl = $qrCodeUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=' . urlencode($qrCodeUrl);
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
