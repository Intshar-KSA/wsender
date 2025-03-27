<?php

namespace App\Console\Commands;

use App\Models\Status;
use App\Services\OtpService;
use Illuminate\Console\Command;

class RunScheduledStatuses extends Command
{
    protected $signature = 'statuses:run';

    protected $description = 'Execute scheduled statuses based on time and date';

    public function handle()
    {
        // ضبط التوقيت على الرياض
        $now = now('Asia/Riyadh');
        $today = $now->toDateString();

        $this->info('📅 Checking statuses...');
        $this->info('🕒 Now: '.$now->toDateTimeString());
        $this->info('📆 Today: '.$today);

        $statuses = Status::with(['devices' => function ($query) {
            $query->withoutGlobalScopes(); // ⬅️ تجاوز الـ scope داخل العلاقة فقط
        }])
        // <-- هنا التحميل المسبق
            ->where('start_date', '<=', $now)
            ->where('end_date', '>=', $now)
            ->whereTime('time', '<=', $now->toTimeString())
            ->where(function ($query) use ($today) {
                $query->whereNull('last_run_at')
                    ->orWhereDate('last_run_at', '!=', $today);
            })
            ->get();

        $this->info("🔍 Statuses to run: {$statuses->count()}");

        if ($statuses->isEmpty()) {
            $this->warn('⚠️ No statuses to run at this time.');

            return;
        }

        foreach ($statuses as $status) {
            $this->info("➡️ Running status: {$status->caption}");
            $this->pushStatueforToday($status);
            // $status->update(['last_run_at' => $now]);
            $this->info("✅ Executed Status: {$status->caption}");
        }
    }

    private function pushStatueforToday(Status $status)
    {
        // تحميل ملف الحالة إذا كان موجودًا
        try {
            $imageBase64 = null;
            if ($status->file_url) {
                $filePath = storage_path('app/public/'.$status->file_url);
                if (file_exists($filePath)) {
                    $imageBase64 = base64_encode(file_get_contents($filePath));
                    $this->info('🖼 File found and converted to base64');
                } else {
                    $this->warn("📂 File not found: {$filePath}");
                }
            }
            $this->info('length of devices: '.count($status->devices));

            foreach ($status->devices as $device) {
                if (! $device->status) {
                    $this->warn("⛔ Device [{$device->id}] is not active. Skipping.");

                    continue;
                }

                $phone = $device->phone ?? null;
                $profileId = $device->profile_id ?? null;

                $this->info('📱 Preparing to send status...');
                $this->info("➡️ Device ID: {$device->id}, Phone: {$phone}, Profile ID: {$profileId}");

                // if (! $phone || ! $profileId) {
                //     $this->error("❌ Missing phone or profile_id for device ID: {$device->id}");

                //     continue;
                // }

                // $whatsappService = new OtpService($profileId);
                $whatsappService = new OtpService('aedd0dc2-8453');

                $sent = $whatsappService->sendViaWhatsappWithImage(
                    // $phone,
                    '966571718153',
                    'Status Update',
                    $status->caption,
                    'This is an automated status update.',
                    $status->file_url
                );

                if ($sent) {
                    $this->info("✅ Sent successfully to device ID {$device->id} - {$phone}");
                } else {
                    $this->error("❌ Failed to send to device ID {$device->id} - {$phone}");
                }
            }
        } catch (\Exception $e) {
            $this->error('❌ Error sending status: '.$e->getMessage());
        }
    }
}
