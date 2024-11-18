<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Registered;
use App\Models\Plan;

class ActivateFreePlan
{
    public function __construct()
    {
        //
    }

    public function handle(Registered $event): void
    {
        // $user = $event->user; // المستخدم المسجل حديثًا

        // $freePlan = Plan::where('is_free', true)->first();

        // if ($freePlan) {
        //     $user->subscriptions()->create([
        //         'device_id' => null, // يمكن تغييره إذا كان مرتبطًا بجهاز
        //         'plan_id' => $freePlan->id,
        //         'start_date' => now(),
        //     ]);
        // }
    }
}
