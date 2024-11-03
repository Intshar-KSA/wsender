<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class ExternalApiService
{
    protected $baseUrl;

    protected $headers;

    public function __construct()
    {
        $this->baseUrl = 'https://wappi.pro/api';
        $this->headers = [
            'accept' => 'application/json',
            'Authorization' => '40703bb7812b727ec01c24f2da518c407342559c',
            // 'Authorization' => '99abcba87402dc4d5ee91f4bc3bcdcf70be07bc6',
            'Content-Type' => 'application/json',
        ];
    }

    public function addProfile($profileId, $name, $webhookUrl)
    {

        $url = 'https://wappi.pro/api/profile/add';
        $url .= '?profile_id='.urlencode($profileId);
        $url .= '&name='.urlencode($name);
        $url .= '&webhook_url='.urlencode($webhookUrl);
        // dd($profileId, $name, $webhookUrl);
        $response = Http::withHeaders($this->headers)->post($url, [
            // 'profile_id' => $profileId,
            // 'name' => $name,
            // 'webhook_url' => $webhookUrl,
        ]);

        return $response->json();
    }

    public function deleteProfile(string $profileId)
    {
        $url = $this->baseUrl . '/profile/delete?profile_id=' . urlencode($profileId);

        $response = Http::withHeaders($this->headers)->post($url);

        if ($response->successful()) {
            return $response->json();
        }

        throw new \Exception('Failed to delete profile via API. Response: ' . $response->body());
    }
    public function getQrCode(string $profileId)
    {
        $url = $this->baseUrl . '/sync/qr/get?profile_id=' . urlencode($profileId);

        $response = Http::withHeaders($this->headers)->get($url);

        if ($response->successful()) {
            return $response->json();
        }

        throw new \Exception('Failed to delete profile via API. Response: ' . $response->body());
    }
}