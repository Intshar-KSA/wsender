<?php

// app/Http/Controllers/PublicQrController.php
namespace App\Http\Controllers;

use App\Models\Device;
use App\Services\ExternalApiService;
use Illuminate\Http\JsonResponse;

class PublicQrController extends Controller
{
    public function show(string $profile)
    {
        $device   = Device::where('profile_id', $profile)->first();
        $api      = app(ExternalApiService::class);
        $response = $api->getQrCode($profile);

        // ➊ حدد نوع الصفحة المطلوب
      $status = 'qr';          // default
$qrCodeUrl = null;

if (isset($response['qrCode']) && $response['status'] === 'done') {
    $qrCodeUrl = $response['qrCode'];
} elseif (($response['detail'] ?? '') === 'You are already authorized') {
    $status = 'authorized';
} else {
    $status = 'error';
}

        return view('public.qr', [
            'device'    => $device,
            'qrCodeUrl' => $qrCodeUrl,
            'refreshMs' => 7_000,
            'profile'   => $profile,
            'status'    => $status,
        ]);
    }

    // AJAX لتحديث الكود
    public function refresh(string $profile): JsonResponse
    {
        $api      = app(ExternalApiService::class);
        $response = $api->getQrCode($profile);

        if (isset($response['qrCode']) && $response['status'] === 'done') {
            return response()->json(['qrCode' => $response['qrCode']]);
        }

        if (($response['detail'] ?? '') === 'You are already authorized') {
            return response()->json(['authorized' => true]);
        }

        return response()->json(['error' => true], 503);
    }
}
