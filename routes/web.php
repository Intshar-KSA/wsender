<?php

use App\Filament\Pages\ViewQrCode;
use App\Http\Controllers\WebhookController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Route::get('/filament/view-qr-code/{qrCodeUrl}', ViewQrCode::class)->name('filament.pages.view-qr-code');
Route::get('/filament/view-qr-code/{qrCodeText}', ViewQrCode::class)->name('filament.pages.view-qr-code');

Route::post('/webhook', [WebhookController::class, 'handle'])->name('webhook.handle');

// // Route لإرسال رابط التحقق
// Route::get('/email/verify', function () {
//     return view('auth.verify-email');
// })->middleware('auth')->name('verification.notice');

// // Route لتنفيذ عملية التحقق
// Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
//     $request->fulfill();

//     return redirect('/'); // إعادة التوجيه بعد التحقق
// })->middleware(['auth', 'signed'])->name('verification.verify');

// // Route لإعادة إرسال رابط التحقق
// Route::post('/email/verification-notification', function () {
//     auth()->user()->sendEmailVerificationNotification();

//     return back()->with('message', 'Verification link sent!');
// })->middleware(['auth', 'throttle:6,1'])->name('verification.send');
