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
            // Remove a constraint única antiga
            $table->dropUnique(['library_id', 'client_cnpj']);
            
            // Adiciona o campo machine_name
            $table->string('machine_name', 100)->nullable()->after('client_cnpj')->comment('Nome da máquina (opcional)');
            
            // Recria a constraint única incluindo machine_name
            $table->unique(['library_id', 'client_cnpj', 'machine_name']);
            
            $table->index('machine_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('library_versions', function (Blueprint $table) {
            $table->dropUnique(['library_id', 'client_cnpj', 'machine_name']);
            $table->dropColumn('machine_name');
            $table->unique(['library_id', 'client_cnpj']);
        });
    }
};
