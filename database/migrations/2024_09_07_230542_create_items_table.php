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
        Schema::create('items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('defaulter_id')
                    ->constrained(table:'defaulters')
                    ->onUpdate('cascade')
                    ->onDelete('cascade');
                    
            $table->string('name', length: 50);
            $table->int('unit_price');
            $table->int('quantity');
            $table->date('retirement_date');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};
