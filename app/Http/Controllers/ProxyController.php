<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ProxyController extends Controller
{
    public function handle(Request $request, $any)
    {
        // استبدال الدومين
        $baseDomain = 'https://wappi.pro/api';
        $originalUrl = $baseDomain . '/' . $any;

        // نوع الطلب (GET, POST, PUT, DELETE...)
        $method = $request->method();

        // إعداد الهيدر مع إضافة Authorization
        $headers = $request->headers->all();
        $headers['Authorization'] = '40703bb7812b727ec01c24f2da518c407342559c';

        // إنشاء HTTP Client مع الهيدر
        $http = Http::withHeaders($headers);

        // إرسال الطلب
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

        // إعادة الرد النهائي من الرابط الأصلي
        return response($response->body(), $response->status())
            ->withHeaders($response->headers());
    }
}
