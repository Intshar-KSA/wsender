<?php

namespace App\Console\Commands;

use App\Models\Subscription;
use Illuminate\Console\Command;
use App\Services\ExternalApiService;

use App\Enums\ProfileDeleteStatus;


class CheckSubscriptions extends Command
{
    protected $signature = 'subscriptions:check';
    protected $description = 'Check for expired subscriptions and delete their profiles if needed.';

    public function handle(): void
    {
        $apiService = app(ExternalApiService::class);

        $subscriptions = Subscription::with('device')->get(); // ✅ تحميل الجهاز لتفادي N+1

        foreach ($subscriptions as $subscription) {
            if (! $subscription->isExpired()) {
                $this->info("Subscription ID {$subscription->id} is still active.");
                continue;
            }

            // تحقق من وجود جهاز
            if (!$subscription->device) {
                $this->warn("Subscription ID {$subscription->id} has no associated device.");
                continue;
            }

            // تحقق من وجود profile_id
            $profileId = $subscription->device->profile_id;
            if (empty($profileId)) {
                $this->warn("Device ID {$subscription->device->id} has no profile_id.");
                continue;
            }

            // تحقق من عدم تكرار الحذف
            if (in_array($subscription->profile_delete_status, [ProfileDeleteStatus::DONE, ProfileDeleteStatus::NOT_FOUND])) {
                $this->info("Profile ID {$profileId} already handled previously.");
                continue;
            }

            // تنفيذ الحذف
            try {
                $result = $apiService->deleteProfile($profileId);
                $status = $result['status'] ?? null;

                if ($status === 'done') {
                    $deleteStatus = ProfileDeleteStatus::DONE;
                } elseif ($result['detail'] === 'Profile not found') {
                    $deleteStatus = ProfileDeleteStatus::NOT_FOUND;
                } else {
                    $deleteStatus = ProfileDeleteStatus::FAILED;
                }

                $subscription->update([
                    'status' => 'expired',
                    'profile_delete_status' => $deleteStatus,
                ]);

                $this->info("Profile ID {$profileId} deletion result: {$deleteStatus}");
            } catch (\Throwable $e) {
                \Log::error("Profile deletion error [{$profileId}]: ".$e->getMessage());
                $subscription->update(['profile_delete_status' => ProfileDeleteStatus::FAILED]);
                $this->error("Failed to delete profile ID {$profileId}");
            }
        }

        $this->info('Subscription check completed.');
    }
}
