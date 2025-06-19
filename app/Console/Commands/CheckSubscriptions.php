<?php

namespace App\Console\Commands;

use App\Models\Subscription;
use Illuminate\Console\Command;
use App\Services\ExternalApiService;

use App\Enums\ProfileDeleteStatus;


class CheckSubscriptions extends Command
{
    protected $signature = 'subscriptions:check';
    protected $description = 'Check for expired subscriptions and take appropriate actions.';

    public function handle(): void
    {
        $apiService = app(ExternalApiService::class);

        $subscriptions = Subscription::all();

        foreach ($subscriptions as $subscription) {
            if (! $subscription->isExpired()) {
                $this->info("Subscription ID {$subscription->id} is still active.");
                continue;
            }

            $this->info("Subscription ID {$subscription->id} has expired.");

            // لا تحاول الحذف إن كانت الحالة محفوظة مسبقًا بنجاح أو غير موجود
            if (in_array($subscription->profile_delete_status, [ProfileDeleteStatus::DONE, ProfileDeleteStatus::NOT_FOUND])) {
                $this->info("Profile ID {$subscription->profile_id} already deleted or not found.");
                continue;
            }

            try {
                $result = $apiService->deleteProfile($subscription->profile_id);
                $status = $result['status'] ?? null;

                // تحديد الحالة المحفوظة
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

                $this->info("Profile ID {$subscription->profile_id} deletion result: {$deleteStatus}");

            } catch (\Throwable $e) {
                \Log::error("Profile deletion error [{$subscription->profile_id}]: {$e->getMessage()}");

                // تسجيل فشل
                $subscription->update([
                    'profile_delete_status' => ProfileDeleteStatus::FAILED,
                ]);
                $this->error("Failed to delete profile ID {$subscription->profile_id}");
            }
        }

        $this->info('Subscription check completed.');
    }
}
