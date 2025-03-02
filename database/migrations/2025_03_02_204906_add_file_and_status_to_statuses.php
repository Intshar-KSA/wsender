<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('statuses', function (Blueprint $table) {
            $table->string('file_url')->nullable()->after('caption'); // رابط الملف (صورة/فيديو)
            $table->boolean('is_active')->default(true)->after('file_url'); // الحالة نشط/غير نشط
        });
    }

    public function down(): void
    {
        Schema::table('statuses', function (Blueprint $table) {
            $table->dropColumn(['file_url', 'is_active']);
        });
    }
};
