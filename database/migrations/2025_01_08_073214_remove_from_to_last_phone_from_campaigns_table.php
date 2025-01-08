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
        Schema::table('campaigns', function (Blueprint $table) {
            // إزالة قيود المفاتيح الخارجية
            $table->dropForeign(['from_contact_id']);
            $table->dropForeign(['to_contact_id']);

            // حذف الأعمدة
            $table->dropColumn(['from_contact_id', 'to_contact_id', 'last_phone']);
        });
    }

    public function down()
    {
        Schema::table('campaigns', function (Blueprint $table) {
            $table->foreignId('from_contact_id')->nullable()->constrained('contacts')->onDelete('cascade');
            $table->foreignId('to_contact_id')->nullable()->constrained('contacts')->onDelete('cascade');
            $table->string('last_phone')->nullable();
        });
    }


};
