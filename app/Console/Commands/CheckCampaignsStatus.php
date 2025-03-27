<?php

namespace App\Console\Commands;

use App\Models\Campaign;
use App\Models\Device;
use App\Services\QuickSendService;
use Illuminate\Console\Command;

class CheckCampaignsStatus extends Command
{
    protected $signature = 'campaigns:check-status';

    protected $description = 'Deactivate campaigns if device subscription is expired';

    public function handle()
    {
        $this->info('🔍 Starting campaign status check...');

        // جلب جميع الحملات مع الجهاز المرتبط بها
        $campaigns = Campaign::with('device.subscriptions.plan')->get();

        foreach ($campaigns as $campaign) {
            $device = $campaign->device;

            if (! $device || ! $device->profile_id) {
                $this->warn("⚠️ Campaign {$campaign->id} has no valid device.");

                continue;
            }

            // تحقق من صلاحية اشتراك الجهاز
            if (! $this->isDeviceActive($device)) {
                if ($campaign->status !== 'paused') {
                    QuickSendService::pauseCampaign($campaign);
                    $this->info("⛔ Campaign {$campaign->id} paused (expired device subscription).");
                }
            } else {
                $this->info("✅ Campaign {$campaign->id} is still active.");
            }
        }

        $this->info('✅ Campaign check completed.');
    }

    protected function isDeviceActive(Device $device): bool
    {
        $subscription = $device->subscriptions()
            ->where('start_date', '<=', now())
            ->latest('start_date')
            ->first();

        if (! $subscription) {
            return false;
        }

        $expiration = $subscription->getExpirationDate();

        return $expiration && now()->lessThan($expiration);
    }
}
