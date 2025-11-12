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
        Schema::create('library_versions', function (Blueprint $table) {
            $table->id();
            $table->string('client_cnpj')->nullable()->unique()->comment('CNPJ do cliente (null = versão padrão)');
            $table->string('version', 20)->comment('Versão da biblioteca');
            $table->boolean('active')->default(true)->comment('Se esta versão está ativa');
            $table->text('notes')->nullable()->comment('Observações sobre a versão');
            $table->timestamps();
            
            $table->index('client_cnpj');
            $table->index('active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('library_versions');
    }
};
