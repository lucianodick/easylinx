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
        Schema::table('library_versions', function (Blueprint $table) {
            // Primeiro remove a constraint unique do client_cnpj
            $table->dropUnique(['client_cnpj']);
            
            // Adiciona a coluna library_id
            $table->foreignId('library_id')
                ->after('id')
                ->constrained('libraries')
                ->onDelete('cascade');
            
            // Recria a constraint unique combinando library_id e client_cnpj
            $table->unique(['library_id', 'client_cnpj']);
            
            $table->index('library_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('library_versions', function (Blueprint $table) {
            $table->dropUnique(['library_id', 'client_cnpj']);
            $table->dropForeign(['library_id']);
            $table->dropColumn('library_id');
            $table->unique('client_cnpj');
        });
    }
};
