<?php

namespace App\Console\Commands;

use App\Models\Subscription;
use Illuminate\Console\Command;
use App\Services\ExternalApiService;

use App\Models\Device;

use App\Enums\ProfileDeleteStatus;


class CheckSubscriptions extends Command
{
    protected $signature = 'subscriptions:check';
    protected $description = 'Check expired subscriptions and delete their profiles if needed.';

    public function handle(): void
    {
        $apiService = app(ExternalApiService::class);

        // تحميل بدون global scope
        $subscriptions = Subscription::with(['device' => fn ($query) => $query->withoutGlobalScopes()])
            ->get();

        foreach ($subscriptions as $subscription) {
            $this->line("🔍 Checking Subscription #{$subscription->id}");
            \Log::info("🔍 Checking Subscription #{$subscription->id}");

            if (! $subscription->isExpired()) {
                $this->line("⏳ Still Active — start_date: {$subscription->start_date}");
                \Log::info("⏳ Still Active — start_date: {$subscription->start_date}");
                continue;
            }

            $device = $subscription->device;

            if (! $device) {
                $this->warn("⚠️ Subscription ID {$subscription->id} has no associated device (device_id: {$subscription->device_id})");
                \Log::warning("⚠️ Subscription ID {$subscription->id} has no associated device (device_id: {$subscription->device_id})");
                continue;
            }

            $profileId = $device->profile_id;

            if (empty($profileId)) {
                $this->warn("⚠️ Device ID {$device->id} has no profile_id.");
                \Log::warning("⚠️ Device ID {$device->id} has no profile_id.");
                continue;
            }

            if (in_array($subscription->profile_delete_status, [ProfileDeleteStatus::DONE, ProfileDeleteStatus::NOT_FOUND])) {
                $this->info("✅ Profile ID {$profileId} already handled previously ({$subscription->profile_delete_status})");
                \Log::info("✅ Profile ID {$profileId} already handled previously ({$subscription->profile_delete_status})");
                continue;
            }

            try {
                $this->line("🚀 Attempting deletion for Profile ID {$profileId}...");
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

                $this->info("✅ Deletion completed for Profile ID {$profileId} (Status: {$deleteStatus})");
                \Log::info("✅ Deletion completed for Profile ID {$profileId} (Status: {$deleteStatus})");
            } catch (\Throwable $e) {
                \Log::error("🔥 Exception for Profile {$profileId}: ".$e->getMessage());
                $subscription->update(['profile_delete_status' => ProfileDeleteStatus::FAILED]);
                $this->error("❌ Failed to delete Profile ID {$profileId}");
            }
        }

        $this->info('🎯 Subscription check completed.');
        \Log::info('🎯 Subscription check completed.');
    }
}
