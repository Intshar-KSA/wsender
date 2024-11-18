<?php

namespace App\Listeners;

use App\Events\DeviceCreated;
use App\Models\Plan;
use Illuminate\Support\Facades\Log;

class ActivateFreePlanForDevice
{
    public function handle(DeviceCreated $event): void
    {
        $device = $event->device;
        $user = $device->user; // الجهاز مرتبط بمستخدم

        Log::info('Listener triggered for device: ', ['device_id' => $device->id, 'user_id' => $user?->id]);

        if (!$user) {
            Log::error('Device has no associated user.');
            return; // إذا لم يكن الجهاز مرتبطًا بمستخدم، لا يتم إنشاء اشتراك
        }

        $freePlan = Plan::where('is_free', true)->first();

        if (!$freePlan) {
            Log::error('No free plan found.');
            return;
        }

        $user->subscriptions()->create([
            'device_id' => $device->id,
            'plan_id' => $freePlan->id,
            'start_date' => now(),
        ]);

        Log::info('Free subscription created for user: ', ['user_id' => $user->id, 'device_id' => $device->id]);
    }
}
