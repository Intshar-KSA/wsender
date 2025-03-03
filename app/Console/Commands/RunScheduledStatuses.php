<?php

namespace App\Console\Commands;

use App\Models\Device;
use App\Models\Status;
use App\Services\OtpService;
use Illuminate\Console\Command;

class RunScheduledStatuses extends Command
{
    protected $signature = 'statuses:run';

    protected $description = 'Execute scheduled statuses based on time and date';

    public function handle()
    {
        $now = now();
        $today = $now->toDateString(); // الحصول على تاريخ اليوم بدون وقت

        $statuses = Status::where('start_date', '<=', $now)
            ->where('end_date', '>=', $now)
            ->whereTime('time', '<=', $now)
            ->where(function ($query) use ($today) {
                $query->whereNull('last_run_at') // تشغيل إذا لم يتم تشغيلها من قبل
                    ->orWhereDate('last_run_at', '!=', $today); // أو إذا لم تُنفذ اليوم
            })
            ->get();

        foreach ($statuses as $status) {
            // تنفيذ الحالة هنا
            $this->pushStatueforToday($status); // استدعاء دالة تنفيذ الحالة
            $status->update(['last_run_at' => now()]);

            $this->info("Executed Status: {$status->caption}");
        }
    }

    private function pushStatueforToday(Status $status)
    {
        // استرجاع الجهاز المرتبط بهذه الحالة
        $device = Device::where('id', $status->device_id)->first();

        if (! $device) {
            Log::error("Device not found for status ID: {$status->id}");

            return;
        }
        $whatsappService = new OtpService('aedd0dc2-8453');
        // $whatsappService = new WhatsAppService($device->profile_id);

        // تحميل ملف الحالة إذا كان موجودًا
        $imageBase64 = null;
        if ($status->media) {
            $imagePath = storage_path('app/'.$status->media);
            if (file_exists($imagePath)) {
                $imageBase64 = base64_encode(file_get_contents($imagePath));
            }
        }

        // استدعاء دالة إرسال الحالة عبر واتساب
        $sent = $whatsappService->sendViaWhatsappWithImage(
            '966571718153',
            'Status Update', // نوع الحالة
            $status->caption,
            'This is an automated status update.',
            $imageBase64
        );

        if ($sent) {
            Log::info("Status sent successfully: {$status->caption}");
        } else {
            Log::error("Failed to send status: {$status->caption}");
        }
    }
}
