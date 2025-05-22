<?php

namespace App\Http\Controllers;

use App\Models\ChatBot;
use App\Models\Device;
use App\Services\ExternalApiService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    protected $externalApiService;

    public function __construct(ExternalApiService $externalApiService)
    {
        $this->externalApiService = $externalApiService;
    }

    public function handle(Request $request)
    {
        $payload = json_decode($request->getContent(), true);
        Log::info('Webhook event:', $payload);

        $msgData = $payload['messages'][0] ?? null;
        if (! $msgData) {
            Log::warning('No incoming message');

            return;
        }

        $body = $msgData['body'] ?? '';
        $chatId = $msgData['chatId'] ?? '';
        $profileId = $msgData['profile_id'] ?? '';

        // أمر خاص: لو المستخدم أرسل "chat_id" نرجّع له معرف الدردشة
        if (trim($body) === 'chat_id') {
            try {
                $this->externalApiService
                    ->sendMessage($profileId, $chatId, $chatId);
                Log::info("Replied with chat_id: $chatId");
            } catch (Exception $e) {
                Log::error('Failed to send chat_id: '.$e->getMessage());
            }

            return;
        }

        // إذا المرسل نصي
        if ($body === '') {
            Log::warning('Empty message body');

            return;
        }

        // إيجاد الجهاز والمستخدم
        $device = Device::withoutGlobalScopes()
            ->where('profile_id', $profileId)
            ->first();
        if (! $device || ! $device->user) {
            Log::error("Device or user not found for profile_id: $profileId");

            return;
        }

        // بحث في جدول ChatBot حسب الرسالة والجهاز
        $chatBot = ChatBot::with('content')
            ->where('msg', $body)
            ->where('device_id', $device->id)
            ->first();

        // جهّز الرد
        if ($chatBot && $chatBot->content && ! empty($chatBot->content->des)) {
            $reply = $chatBot->content->des;
        } else {
            return; // لا يوجد محتوى للرد
            $reply = 'عذراً، لم أفهمك.';
        }

        // أرسل الرد
        try {
            $this->externalApiService
                ->sendMessage($profileId, $chatId, $reply);
            Log::info("Replied: $reply");
        } catch (Exception $e) {
            Log::error('Failed to send reply: '.$e->getMessage());
        }
    }
}
