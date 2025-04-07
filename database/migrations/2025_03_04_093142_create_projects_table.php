<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->string('employee');
            $table->string('owner_name');
            $table->string('owner_number');
            $table->string('owner_country');
            $table->string('project_name');
            $table->string('project_type');
            $table->string('price_offer')->nullable();
            $table->decimal('cost', 10, 2);
            $table->decimal('initial_payment', 10, 2);
            $table->decimal('profit_margin', 10, 2);
            $table->string('hosting')->nullable();
            $table->string('technical_support')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
