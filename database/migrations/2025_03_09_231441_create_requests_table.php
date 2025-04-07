<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sales_id')->constrained('users')->onDelete('cascade'); // المبيعات الذي أرسل الطلب
            $table->foreignId('team_lead_id')->constrained('users')->onDelete('cascade'); // التيم ليدر الذي يستلم الطلب
            $table->text('message'); // محتوى الطلب
            $table->enum('status', ['pending', 'accepted', 'rejected'])->default('pending'); // حالة الطلب
            $table->timestamps();
        });
    }

    public function down()
    {
        // تعطيل قيود المفتاح الأجنبي قبل حذف الجدول
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('requests');
        Schema::enableForeignKeyConstraints();
    }
};
