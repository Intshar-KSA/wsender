<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('campaigns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('device_id')->constrained()->onDelete('cascade');
            $table->unsignedBigInteger('mass_prsting_id')->nullable();
            $table->foreignId('content_id')->constrained()->onDelete('cascade');
            $table->longText('receivers_phones');
            $table->integer('message_every');
            $table->string('last_phone')->nullable();
            $table->time('starting_time');
            $table->time('allowed_period_from');
            $table->time('allowed_period_to');
            $table->enum('status', ['on', 'off']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('campaigns');
    }
};
