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
        Schema::create('api_request_logs', function (Blueprint $table) {
            $table->id();
            $table->string('ip_address', 45);
            $table->string('system')->nullable();
            $table->string('cnpj', 14)->nullable();
            $table->string('machine_name', 100)->nullable();
            $table->boolean('cache_hit')->default(false);
            $table->integer('response_time_ms')->nullable(); // Tempo de resposta em milissegundos
            $table->integer('libraries_count')->default(0); // Quantidade de bibliotecas retornadas
            $table->string('user_agent', 255)->nullable();
            $table->timestamps();
            
            // Índices para consultas rápidas
            $table->index('ip_address');
            $table->index('system');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('api_request_logs');
    }
};
