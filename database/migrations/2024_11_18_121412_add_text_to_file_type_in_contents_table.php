<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contents', function (Blueprint $table) {
            // تعديل enum لإضافة 'text'
            $table->enum('file_type', ['video', 'image', 'doc', 'text'])->change();
        });
    }

    public function down(): void
    {
        Schema::table('contents', function (Blueprint $table) {
            // إعادة العمود للحالة السابقة
            $table->enum('file_type', ['video', 'image', 'doc'])->change();
        });
    }
};
