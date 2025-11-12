<?php

namespace Database\Seeders;

use App\Models\Library;
use App\Models\LibraryVersion;
use Illuminate\Database\Seeder;

class LibraryVersionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Busca bibliotecas
        $fiscalFlow = Library::where('name', 'Fiscal Flow')->first();
        $tecnospeed = Library::where('name', 'Tecnospeed')->first();
        $uninfe = Library::where('name', 'Uninfe')->first();

        if ($fiscalFlow) {
            // Versão padrão Fiscal Flow
            LibraryVersion::create([
                'library_id' => $fiscalFlow->id,
                'client_cnpj' => null,
                'version' => '9.8.0.9',
                'active' => true,
                'notes' => 'Versão padrão para todos os clientes',
            ]);

            // Exceção para cliente específico
            LibraryVersion::create([
                'library_id' => $fiscalFlow->id,
                'client_cnpj' => '06.210.435/0001-47',
                'version' => '9.18.0.1',
                'active' => true,
                'notes' => 'Versão customizada para este cliente',
            ]);
        }

        if ($tecnospeed) {
            // Versão padrão Tecnospeed
            LibraryVersion::create([
                'library_id' => $tecnospeed->id,
                'client_cnpj' => null,
                'version' => '3.0.200',
                'active' => true,
                'notes' => 'Versão padrão Tecnospeed',
            ]);
        }

        if ($uninfe) {
            // Versão padrão Uninfe
            LibraryVersion::create([
                'library_id' => $uninfe->id,
                'client_cnpj' => null,
                'version' => '4.0.65',
                'active' => true,
                'notes' => 'Versão padrão Uninfe',
            ]);
        }
    }
}
