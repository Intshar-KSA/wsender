<?php

namespace App\Http\Controllers;

use App\Models\ChatBot;
use App\Models\Device;
use App\Services\ExternalApiService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class WebhookController extends Controller
{
    protected $externalApiService;

    public function __construct(ExternalApiService $externalApiService)
    {
        $this->externalApiService = $externalApiService;
    }

    public function handle(Request $request)
    {
        $data = $request->getContent();
        $event = json_decode($data, true);

        \Log::info('Webhook event: ', (array) $event);

        if (! isset($event['messages'])) {
            \Log::warning('Missing messages key in webhook event');

            return;
        }

        $messages = $event['messages'];

        // إذا كانت الرسالة عبارة عن مصفوفة (incoming_message)
        if (isset($messages[0])) {
            $messageData = $messages[0];

            $chat_id = $messageData['chatId'] ?? null;
            $message = $messageData['body'] ?? '';
            $profile_id = $messageData['profile_id'] ?? null;
            $is_me = $messageData['is_me'] ?? false;
            $from_user = $messageData['from'] ?? null;
            $to_user = $messageData['to'] ?? null;

            if ($message === 'chat_id') {
                $this->externalApiService->sendMessage($profile_id, $chat_id, $chat_id);
            }

            $userInfo = $this->getUserByUserProfile($profile_id);
            \Log::info('User Info: ', (array) $userInfo);

            if ($userInfo !== null) {
                $user_name = $userInfo['name'];
                $token = $userInfo['token'];
                $sheet_url = $userInfo['sheet_url'];

                try {
                    $sheet_id = $this->getSheetIdFromUrl($sheet_url);

                    if (! $is_me || ($is_me && $from_user == $to_user)) {
                        $this->get_sheet_msgs($sheet_id, $user_name, $profile_id, $token, $sheet_url, $message, $chat_id);
                    }
                } catch (Exception $e) {
                    \Log::error('Error in get_sheet_msgs: '.$e->getMessage());
                }

                // استدعاء الرابط الموجود في المتغير webhook_url
                if (! empty($userInfo['webhook_url'])) {
                    $this->callWebhookUrl($userInfo['webhook_url'], $event);
                }
            } else {
                echo 'User not found.';
            }

            // إذا كانت الرسالة عبارة عن كائن (مثل delivery_status)
        } elseif (is_array($messages) && isset($messages['wh_type'])) {
            $wh_type = $messages['wh_type'];

            if ($wh_type === 'delivery_status') {
                \Log::info('Delivery status received: ', $messages);
                // يمكنك هنا حفظ حالة الرسالة في قاعدة البيانات مثلاً
            } else {
                \Log::warning("Unhandled webhook type: $wh_type", $messages);
            }

        } else {
            \Log::warning('Unknown format in messages', ['messages' => $messages]);
        }
    }

    private function getUserByUserProfile($profile_id)
    {
        $device = Device::where('profile_id', $profile_id)->with('user')->first();

        if (! $device) {
            \Log::error('❌ Device not found for profile_id: '.$profile_id);
        } else {
            \Log::info('✅ Device found: ', $device->toArray());

            if (! $device->user) {
                \Log::error('❌ User not linked to device with profile_id: '.$profile_id);
            } else {
                \Log::info('✅ Linked User: ', $device->user->toArray());
            }
        }

        if ($device && $device->user) {
            $user = $device->user;

            return [
                'name' => $user->name,
                'token' => $device->token,
                'sheet_url' => $device->sheet_url,
                'webhook_url' => $device->webhook_url,
            ];
        }

        return null;
    }

    private function getSheetIdFromUrl($url)
    {
        $parts = parse_url($url);
        $path = explode('/', $parts['path']);
        $id = $path[3];

        return $id;
    }

    private function get_sheet_msgs($sheet_id, $user_name, $profile_id, $token, $sheet_url, $message, $chat_id)
    {
        try {
            $chatBot = ChatBot::where('msg', $message)->with('content')->first();

            if ($chatBot) {
                $responseMessage = $chatBot->content->des;

                if (strpos($chat_id, '@c.us') !== false) {
                    try {
                        $result = $this->externalApiService->sendMessage($profile_id, $chat_id, $responseMessage);
                    } catch (Exception $e) {
                        $result = $this->externalApiService->sendMessage($profile_id, $chat_id, $e->getMessage().'eception');
                    }
                }

                echo $responseMessage.'<br>';
            } else {
                echo 'No matching message found.';
            }
        } catch (Exception $e) {
            // Handle exceptions
            // echo 'Error: ' . $e->getMessage();
        }
    }

    private function callWebhookUrl($webhookUrl, $event)
    {
        try {
            $response = Http::post($webhookUrl, $event);
            if ($response->successful()) {
                echo 'Webhook called successfully.';
            } else {
                throw new Exception('Failed to call webhook. Response: '.$response->body());
            }
        } catch (Exception $e) {
            // Handle exceptions
            // echo 'Error: ' . $e->getMessage();
        }
    }
}
