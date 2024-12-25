<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WebhookController;

// ...existing code...

Route::post('/webhook', [WebhookController::class, 'handle']);

// ...existing code...
