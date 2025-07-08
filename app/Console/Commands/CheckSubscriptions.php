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

        // تحميل الاشتراكات مع الأجهزة المرتبطة بدون global scope
        $subscriptions = Subscription::with(['device' => fn ($query) => $query->withoutGlobalScopes()])->get();

        foreach ($subscriptions as $subscription) {
            $subscriptionId = $subscription->id;
            \Log::info("🔍 Checking Subscription #$subscriptionId");

            $device = $subscription->device;

            if (! $device) {
                \Log::warning("⚠️ Subscription ID {$subscriptionId} has no associated device (device_id: {$subscription->device_id})");
                continue;
            }

            $profileId = $device->profile_id;

            if (empty($profileId)) {
                \Log::warning("⚠️ Device ID {$device->id} has no profile_id.");
                continue;
            }

            // تجاهل الاشتراكات التي سبق التعامل معها
            if (in_array($subscription->profile_delete_status, [ProfileDeleteStatus::DONE, ProfileDeleteStatus::NOT_FOUND])) {
                \Log::info("✅ Profile ID {$profileId} already handled previously ({$subscription->profile_delete_status})");
                continue;
            }

            // ✅ التحقق من تاريخ الانتهاء بشكل دقيق (كما في واجهة Filament)
            $expirationDate = $subscription->getExpirationDate();
            $now = now();

            \Log::info("📅 Subscription #$subscriptionId Start: {$subscription->start_date}, Expires: {$expirationDate}, Now: {$now}");

            if (! $expirationDate || $now->greaterThanOrEqualTo($expirationDate)) {
                \Log::warning("🚨 Subscription #$subscriptionId is expired or has no expiration date. Proceeding to delete profile {$profileId}.");

                try {
                    $result = $apiService->deleteProfile($profileId);
                    $status = $result['status'] ?? null;

                    if ($status === 'done') {
                        $deleteStatus = ProfileDeleteStatus::DONE;
                    } elseif (($result['detail'] ?? '') === 'Profile not found') {
                        $deleteStatus = ProfileDeleteStatus::NOT_FOUND;
                    } else {
                        $deleteStatus = ProfileDeleteStatus::FAILED;
                    }

                    $subscription->update([
                        'status' => 'expired',
                        'profile_delete_status' => $deleteStatus,
                    ]);

                    \Log::info("✅ Deletion completed for Profile ID {$profileId} (Status: {$deleteStatus})");
                } catch (\Throwable $e) {
                    \Log::error("🔥 Exception while deleting Profile {$profileId}: " . $e->getMessage());
                    $subscription->update(['profile_delete_status' => ProfileDeleteStatus::FAILED]);
                }
            } else {
                \Log::info("⏳ Subscription #$subscriptionId is still active. Expires in: " . $now->diffForHumans($expirationDate, true));
                continue;
            }
        }

        \Log::info('🎯 Subscription check completed.');
    }
}
