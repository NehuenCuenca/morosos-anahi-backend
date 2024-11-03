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
        Schema::create('defaulter_thing', function (Blueprint $table) {
            $table->id();

            $table->foreignId('defaulter_id')
                    ->constrained(table:'defaulters')
                    ->onUpdate('cascade')
                    ->onDelete('cascade');

            $table->foreignId('thing_id')
                    ->constrained(table:'things')
                    ->onUpdate('cascade')
                    ->onDelete('cascade');
                    
            $table->integer('unit_price');
            $table->integer('quantity');
            $table->date('retired_at');
            $table->date('filed_at')->nullable();
            $table->boolean('was_paid');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('defaulter_thing');
    }
};
