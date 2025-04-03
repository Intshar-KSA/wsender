<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('campaigns', function (Blueprint $table) {
            $table->time('starting_time')->nullable()->change();
            $table->time('allowed_period_from')->nullable()->change();
            $table->time('allowed_period_to')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('campaigns', function (Blueprint $table) {
            $table->time('starting_time')->nullable(false)->change();
            $table->time('allowed_period_from')->nullable(false)->change();
            $table->time('allowed_period_to')->nullable(false)->change();
        });
    }
};
