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
        Schema::create('defaulters', function (Blueprint $table) {
            $table->id();

            $table->string('name', length: 50);
            $table->integer('debt_balance');
            $table->integer('discount_balance');
            $table->integer('total_balance');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('defaulters');
    }
};
