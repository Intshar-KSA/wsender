<?php

namespace App\Http\Controllers;

use App\Models\BotConversation;
use App\Models\ChatBot;
use App\Models\Device;
use App\Services\ExternalApiService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    protected ExternalApiService $api;

    public function __construct(ExternalApiService $api)
    {
        $this->api = $api;
    }

    public function handle(Request $request)
    {
        $payload = $request->all();
        Log::info('[Webhook] Received payload', $payload);

        $msg = $payload['messages'][0] ?? null;
        if (! $msg) {
            return response()->json(['status' => 'no_message'], 200);
        }

        $chatId = $msg['chatId'] ?? $msg['from'] ?? null;
        $profileId = $msg['profile_id'] ?? null;
        $text = trim(strtolower($msg['body'] ?? ''));

        if (! $chatId || ! $profileId) {
            Log::error('[Webhook] Missing chatId or profile_id');

            return response()->json(['status' => 'bad_request'], 400);
        }

        // 👇 تجاوز الـ global scope هنا
        $device = Device::withoutGlobalScope('current_user_devices')
            ->where('profile_id', $profileId)
            ->first();

        Log::info('[Webhook] Found device', [
            'profile_id' => $profileId,
            'device' => $device ? $device->toArray() : null,
        ]);

        if (! $device) {
            return response()->json(['status' => 'device_not_found'], 400);
        }

        $deviceId = $device->id;

        // 1) أمر chat_id
        if ($text === 'chat_id') {
            $this->api->sendMessage($profileId, $chatId, "📌 chat_id: {$chatId}");

            return response()->json(['status' => 'sent_chat_id'], 200);
        }

        // 2) التحية الأولى أو بعد TTL
        $ttl = config('bot.greeting_ttl', 1440);
        $conv = BotConversation::firstOrCreate(['chat_id' => $chatId], ['last_greeted_at' => now()]);
        $minutes = Carbon::now('Asia/Riyadh')->diffInMinutes($conv->last_greeted_at);

        if ($conv->wasRecentlyCreated || $minutes >= $ttl) {
            $greetings = ChatBot::where('device_id', $deviceId)
                ->where('is_greeting', true)
                ->where('status', true)
                ->with('content')
                ->orderBy('created_at')
                ->get();

            foreach ($greetings as $g) {
                $this->sendContent($g->content, $profileId, $chatId);
            }
            $conv->update(['last_greeted_at' => now()]);

            return response()->json(['status' => 'sent_greetings'], 200);
        }

        // 3) البحث العادي في chat_bots
        $trigger = ChatBot::where('device_id', $deviceId)
            ->where('is_greeting', false)
            ->where('status', true)
            ->where(function ($q) use ($text) {
                $q->where(fn ($q1) => $q1->where('type', 'exact')->whereRaw('LOWER(msg)=?', [$text]))
                    ->orWhere(fn ($q2) => $q2->where('type', 'contains')->whereRaw('LOWER(msg) LIKE ?', ["%{$text}%"]));
            })
            ->with('content')
            ->first();

        if (! $trigger) {
            // $this->api->sendMessage($profileId, $chatId, 'عذرًا، لم أجد محتوىً يطابق هذا الطلب.');

            return response()->json(['status' => 'no_content'], 200);
        }

        $this->sendContent($trigger->content, $profileId, $chatId);

        return response()->json(['status' => 'ok'], 200);
    }

    protected function sendContent($content, string $profileId, string $chatId): void
    {
        switch ($content->file_type) {
            case 'text':
                $this->api->sendMessage($profileId, $chatId, $content->des);
                break;
            case 'image':
                $this->sendImageFile($content, $profileId, $chatId);
                break;
            case 'doc':
                $this->sendDocumentFile($content, $profileId, $chatId);
                break;
            default:
                // $this->api->sendMessage($profileId, $chatId, 'نوع المحتوى غير مدعوم.');
        }
    }

    protected function sendImageFile($content, string $profileId, string $chatId): void
    {
        $path = storage_path('app/public/'.$content->file);
        Log::info('[Webhook] sendImageFile checking path', compact('path'));

        if (file_exists($path)) {
            $b64 = base64_encode(file_get_contents($path));
            $this->api->sendImage($profileId, $chatId, $content->title, $b64);
            Log::info('[Webhook] Image sent');
        } else {
            Log::error('[Webhook] Image file not found', compact('path'));
            $this->api->sendMessage($profileId, $chatId, 'عذراً، لم أتمكن من العثور على الصورة المطلوبة.');
        }
    }

    protected function sendDocumentFile($content, string $profileId, string $chatId): void
    {
        $path = storage_path('app/public/'.$content->file);
        Log::info('[Webhook] sendDocumentFile checking path', compact('path'));

        if (file_exists($path)) {
            $b64 = base64_encode(file_get_contents($path));
            $this->api->sendDocument(
                $profileId,
                $chatId,
                $content->title,
                basename($path),
                $b64
            );
            Log::info('[Webhook] Document sent');
        } else {
            Log::error('[Webhook] Document file not found', compact('path'));
            $this->api->sendMessage($profileId, $chatId, 'عذراً، لم أتمكن من العثور على الملف المطلوب.');
        }
    }
}
