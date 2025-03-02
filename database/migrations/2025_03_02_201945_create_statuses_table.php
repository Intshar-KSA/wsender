<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('statuses', function (Blueprint $table) {
            $table->id();
            $table->string('title'); // العنوان
            $table->string('caption'); // الوصف
            $table->date('start_date'); // تاريخ البداية
            $table->date('end_date'); // تاريخ النهاية
            $table->time('time'); // وقت التنفيذ
            $table->timestamp('last_run_at')->nullable(); // آخر مرة تم تشغيل الحالة فيها
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('statuses');
    }
};
