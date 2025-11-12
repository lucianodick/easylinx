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
        Schema::create('libraries', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique()->comment('Nome da biblioteca');
            $table->text('description')->nullable()->comment('Descrição da biblioteca');
            $table->boolean('active')->default(true)->comment('Se a biblioteca está ativa');
            $table->timestamps();
            
            $table->index('name');
            $table->index('active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('libraries');
    }
};
