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
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->string('payment_status')->default('pending'); // حالة الدفع (pending, approved, rejected)
            $table->string('payment_method')->default('None'); // طريقة الدفع (receipt, online)
            $table->string('receipt_url')->nullable(); // رابط الإيصال
            $table->string('transaction_id')->nullable(); // رقم معاملة الدفع عبر الإنترنت
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropColumn(['payment_status', 'payment_method', 'receipt_url', 'transaction_id']);
        });
    }
};
