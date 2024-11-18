<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->boolean('is_free')->default(false); // لتحديد إذا كانت الخطة مجانية
            $table->integer('hours')->nullable(); // عدد الساعات للخطة المجانية أو الأخرى
        });
    }

    public function down(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->dropColumn(['is_free', 'hours']);
        });
    }
};
