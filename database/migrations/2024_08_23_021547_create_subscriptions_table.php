<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        // Schema::create('subscriptions', function (Blueprint $table) {
        //     $table->id();
        //     $table->foreignId('device_id')->constrained()->onDelete('cascade');
        //     $table->integer('number_of_days');
        //     $table->decimal('price', 8, 2);
        //     $table->timestamp('created_at')->useCurrent();
        // });
    }

    public function down()
    {
        // Schema::dropIfExists('subscriptions');
    }
};
