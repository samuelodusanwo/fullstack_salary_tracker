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
        Schema::create('user_salaries', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Name provided by the user submitting salary
            $table->string('email')->unique(); // Unique email for salary records
            $table->double('salary_local_currency')->nullable();
            $table->double('salary_euros')->nullable();
            $table->double('commission')->nullable(); // Default to 500 later in model/controller

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_salaries');
    }
};
