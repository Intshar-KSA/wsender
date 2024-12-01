<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('chat_bots', function (Blueprint $table) {
            // تغيير العمود إلى Boolean
            $table->boolean('status')->default(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('chat_bots', function (Blueprint $table) {
            // إعادة العمود إلى النصوص (اختياري حسب الحالة السابقة)
            $table->string('status')->default('off')->change();
        });
    }
};
