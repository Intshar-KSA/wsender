<?php

namespace App\Console\Commands;

use App\Models\Status;
use Illuminate\Console\Command;

class RunScheduledStatuses extends Command
{
    protected $signature = 'statuses:run';

    protected $description = 'Execute scheduled statuses based on time and date';

    public function handle()
    {
        $now = now();
        $statuses = Status::where('start_date', '<=', $now)
            ->where('end_date', '>=', $now)
            ->whereTime('time', '<=', $now)
            ->whereNull('last_run_at')
            ->get();

        foreach ($statuses as $status) {
            // تنفيذ الحالة هنا (يمكنك إرسال إشعارات أو أي تنفيذ آخر)
            $status->update(['last_run_at' => now()]);
            $this->info("Executed Status: {$status->caption}");
        }
    }
}
