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
        \Log::info('Webhook event: ', $event);

        if (isset($event)) {
            $chat_id = $event['messages'][0]['chatId'];
            $message = $event['messages'][0]['body'];
            $profile_id = $event['messages'][0]['profile_id'];
            $is_me = $event['messages'][0]['is_me'];
            $from_user = $event['messages'][0]['from'];
            $to_user = $event['messages'][0]['to'];

            if ($message == 'chat_id') {
                $result = $this->externalApiService->sendMessage($profile_id, $chat_id, $chat_id);
            }

            $userInfo = $this->getUserByUserProfile($profile_id);
            if ($userInfo !== null) {
                $user_name = $userInfo['name'];
                $token = $userInfo['token'];
                $sheet_url = $userInfo['sheet_url'];
                $sheet_id = $this->getSheetIdFromUrl($sheet_url);

                if (! $is_me) {
                    $this->get_sheet_msgs($sheet_id, $user_name, $profile_id, $token, $sheet_url, $message, $chat_id);
                }

                if ($is_me && $from_user == $to_user) {
                    $this->get_sheet_msgs($sheet_id, $user_name, $profile_id, $token, $sheet_url, $message, $chat_id);
                }

                // استدعاء الرابط الموجود في المتغير webhook_url
                if (! empty($userInfo['webhook_url'])) {
                    $this->callWebhookUrl($userInfo['webhook_url'], $event);
                }
            } else {
                echo 'User not found.';
            }
        }
    }

    private function getUserByUserProfile($profile_id)
    {
        $device = Device::where('profile_id', $profile_id)->with('user')->first();
        if ($device) {
            $user = $device->user;

            return [
                'name' => $user->name,
                'token' => $device->token,
                'sheet_url' => $device->sheet_url,
                'webhook_url' => $device->webhook_url,
                // Add other necessary fields from the device or user
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
