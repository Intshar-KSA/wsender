<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
   // database/migrations/xxxx_xx_xx_create_bot_conversations_table.php
public function up()
{
    Schema::create('bot_conversations', function (Blueprint $table) {
        $table->id();
        $table->string('chat_id')->unique();
        $table->timestamp('last_greeted_at')->nullable();
        $table->timestamps();
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bot_conversations');
    }
};
