<?php

namespace App\Console\Commands;

use App\Models\Subscription;
use Illuminate\Console\Command;

class CheckSubscriptions extends Command
{
    protected $signature = 'subscriptions:check';
    protected $description = 'Check for expired subscriptions and take appropriate actions.';

    public function handle(): void
    {
        $subscriptions = Subscription::all();

        foreach ($subscriptions as $subscription) {
            if ($subscription->isExpired()) {
                // تنفيذ الإجراء المناسب عند انتهاء الاشتراك
                $subscription->update(['status' => 'expired']); // مثال إذا كان لديك عمود status
                $this->info("Subscription ID {$subscription->id} has expired.");
            } else {
                $this->info("Subscription ID {$subscription->id} is still active.");
            }
        }

        $this->info('Subscription check completed.');
    }
}
