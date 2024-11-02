<?php

use App\Filament\Pages\ViewQrCode;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Route::get('/filament/view-qr-code/{qrCodeUrl}', ViewQrCode::class)->name('filament.pages.view-qr-code');
Route::get('/filament/view-qr-code/{qrCodeText}', ViewQrCode::class)->name('filament.pages.view-qr-code');