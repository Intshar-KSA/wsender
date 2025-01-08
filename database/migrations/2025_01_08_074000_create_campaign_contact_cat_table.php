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
        Schema::create('campaign_contact_cat', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('campaign_id');
            $table->unsignedBigInteger('contact_cat_id');
            $table->timestamps();

            $table->foreign('campaign_id')
                ->references('id')
                ->on('campaigns')
                ->onDelete('cascade');

            $table->foreign('contact_cat_id')
                ->references('id')
                ->on('contacts_cats') // تصحيح اسم الجدول هنا
                ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('campaign_contact_cat');
    }




};
