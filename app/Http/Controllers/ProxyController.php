<?php

namespace App\Http\Controllers;

use App\Models\Device;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ProxyController extends Controller
{
    public function handle(Request $request, $any)
    {
        $profileId = $request->query('profile_id') ?? $request->input('profile_id');

        $excludedProfiles = ['fe2dbcb1-c531', '2fdc9526-cccd'];
        \Log::info('Profile ID:-> ' . $profileId);
        // إذا لم يكن البروفايل هو المستثنى، تحقق من الاشتراك
        if ($profileId && ! in_array($profileId, $excludedProfiles)) {
           $device = Device::whereRaw('BINARY profile_id = ?', [$profileId])->first();


            if (! $device) {
                return response()->json(['error' => 'Device not found.'], 404);
            }

            $activeSubscription = $device->subscriptions()
                ->where('start_date', '<=', now())
                ->latest('start_date')
                ->first();

            $expirationDate = optional($activeSubscription)->getExpirationDate();

            if (! $expirationDate || now()->greaterThanOrEqualTo($expirationDate)) {
                return response()->json([
                    'error' => 'Subscription expired. Device not authorized to send requests.',
                ], 403);
            }
        }

        // تنفيذ الطلب لأي بروفايل (المستثنى أو الذي اجتاز التحقق)
        $baseDomain = 'https://wappi.pro/api';
        $originalUrl = $baseDomain.'/'.$any;

        $method = $request->method();

        $headers = $request->headers->all();
        $headers['Authorization'] = '40703bb7812b727ec01c24f2da518c407342559c';

        $http = Http::withHeaders($headers);

        if ($method === 'GET') {
            $response = $http->get($originalUrl, $request->query());
        } elseif (in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE'])) {
            $response = $http->send($method, $originalUrl, [
                'query' => $request->query(),
                'json' => $request->all(),
            ]);
        } else {
            return response()->json(['error' => 'Unsupported HTTP method'], 405);
        }

        return response($response->body(), $response->status())
            ->withHeaders($response->headers());
    }
}
