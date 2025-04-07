<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // غيّر نوع العمود من TEXT إلى VARCHAR(255)
        DB::statement('ALTER TABLE devices MODIFY profile_id VARCHAR(255) NOT NULL');

        // أضف فهرس للبحث السريع
        Schema::table('devices', function (Blueprint $table) {
            $table->index('profile_id', 'idx_profile_id');
        });
    }

    public function down()
    {
        // أزل الفهرس
        Schema::table('devices', function (Blueprint $table) {
            $table->dropIndex('idx_profile_id');
        });

        // أرجع العمود إلى TEXT
        DB::statement('ALTER TABLE devices MODIFY profile_id TEXT NOT NULL');
    }
};
