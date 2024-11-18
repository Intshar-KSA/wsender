<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('chat_bots', function (Blueprint $table) {
            $table->dropColumn('msg_type');
        });
    }

    public function down(): void
    {
        Schema::table('chat_bots', function (Blueprint $table) {
            $table->string('msg_type')->nullable();
        });
    }
};
