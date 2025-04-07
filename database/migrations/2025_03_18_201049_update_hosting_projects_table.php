<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('hosting_projects', function (Blueprint $table) {
            $table->dropColumn(['name_en', 'name_ar']); // حذف الأعمدة القديمة
            $table->string('name'); // إضافة العمود الجديد
        });
    }

    public function down()
    {
        Schema::table('hosting_projects', function (Blueprint $table) {
            $table->string('name_en'); // استرجاع الأعمدة في حالة التراجع
            $table->string('name_ar');
            $table->dropColumn('name'); // حذف العمود الجديد
        });
    }
};;
