<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateQuickSendsTable extends Migration
{
    public function up()
    {
        Schema::create('quick_sends', function (Blueprint $table) {
            $table->id();
            $table->string('profile_id');               // معرف الملف الشخصي
            $table->text('message_text');               // نص الرسالة
            $table->text('phone_numbers');              // قائمة أرقام الهواتف (نص متعدد الأسطر)
            $table->string('image')->nullable();        // الصورة (مسار حفظ الملف)
            $table->integer('timeout_from')->default(5);// الوقت الأدنى للمهلة
            $table->integer('timeout_to')->default(8);  // الوقت الأقصى للمهلة
            $table->text('base64')->nullable();         // محتوى الملف بتنسيق Base64
            $table->string('file_name')->nullable();    // اسم الملف
            $table->string('status')->default('created'); // حالة الحملة (created, paused, resumed)
            $table->timestamps();                       // التاريخ والوقت
        });
    }

    public function down()
    {
        Schema::dropIfExists('quick_sends');
    }
}
