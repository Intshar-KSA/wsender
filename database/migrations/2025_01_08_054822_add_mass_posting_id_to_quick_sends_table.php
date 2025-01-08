<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMassPostingIdToQuickSendsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('quick_sends', function (Blueprint $table) {
            $table->string('mass_posting_id')->nullable()->after('status'); // إضافة العمود بعد 'status'
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('quick_sends', function (Blueprint $table) {
            $table->dropColumn('mass_posting_id'); // حذف العمود في حالة التراجع
        });
    }
}
