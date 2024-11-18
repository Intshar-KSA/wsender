<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateCampaignsTable extends Migration
{
    public function up()
    {
        Schema::table('campaigns', function (Blueprint $table) {
            $table->dropColumn('receivers_phones');
            $table->unsignedBigInteger('from_contact_id')->nullable();
            $table->unsignedBigInteger('to_contact_id')->nullable();

            // إعداد العلاقات الجديدة
            $table->foreign('from_contact_id')->references('id')->on('contacts')->onDelete('set null');
            $table->foreign('to_contact_id')->references('id')->on('contacts')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('campaigns', function (Blueprint $table) {
            $table->string('receivers_phones');
            $table->dropForeign(['from_contact_id']);
            $table->dropForeign(['to_contact_id']);
            $table->dropColumn('from_contact_id');
            $table->dropColumn('to_contact_id');
        });
    }
}
