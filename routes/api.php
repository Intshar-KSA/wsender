<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;
use App\Http\Controllers\WebhookController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// ...existing code...

// Route::post('/webhook', [WebhookController::class, 'handle']);
Route::post('/webhook', [WebhookController::class, 'handle'])->name('webhook.handle');


// ...existing code...

Route::post('/run-migrations', function (Request $request) {
    // حماية الوصول بكلمة مرور أو توكن
    $password = $request->input('password');
    if ($password !== env('MIGRATION_PASSWORD', 'default_password')) {
        return response()->json(['message' => 'Unauthorized'], 403);
    }

    // تنفيذ المايجريشن
    try {
        Artisan::call('migrate', ['--force' => true]);
        return response()->json(['message' => 'Migrations executed successfully']);
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
});

Route::post('/optimize-project', function (Request $request) {
    // حماية الوصول بكلمة مرور أو توكن
    $password = $request->input('password');
    if ($password !== env('MIGRATION_PASSWORD', 'default_password')) {
        return response()->json(['message' => 'Unauthorized'], 403);
    }

    // تحسين وتنظيف المشروع
    try {
        Artisan::call('optimize:clear');
        return response()->json(['message' => 'Project optimized and cleared successfully']);
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
});

// مسار جديد ليكون وسيط بين الطلبات
// Route::post('/sync/message/send', function (Request $request) {
//     $profile_id = $request->query('profile_id');
//     $body = $request->input('body');
//     $recipient = $request->input('recipient');
//     $headers = [
//         'accept' => 'application/json',
//         'Authorization' => '40703bb7812b727ec01c24f2da518c407342559c',
//         'Content-Type' => 'application/json',
//     ];

//     $response = Http::withHeaders($headers)->post("https://wappi.pro/api/sync/message/send?profile_id={$profile_id}", [
//         'body' => $body,
//         'recipient' => $recipient,
//     ]);

//     return response()->json($response->json(), $response->status());
// });



use App\Http\Controllers\ProxyController;

Route::any('/{any}', [ProxyController::class, 'handle'])
    ->where('any', '.*');
