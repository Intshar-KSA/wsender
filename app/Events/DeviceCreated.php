<?php

namespace App\Events;

use App\Models\Device;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DeviceCreated
{
    use Dispatchable, SerializesModels;

    public Device $device;

    public function __construct(Device $device)
    {
        $this->device = $device;
    }
}
