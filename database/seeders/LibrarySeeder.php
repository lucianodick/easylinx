<?php

namespace Database\Seeders;

use App\Models\Library;
use Illuminate\Database\Seeder;

class LibrarySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $libraries = [
            [
                'system' => 'SETA',
                'name' => 'Fiscal Flow',
                'description' => 'Sistema de emissão de notas fiscais eletrônicas',
                'active' => true,
            ],
            [
                'system' => 'SETA',
                'name' => 'Tecnospeed',
                'description' => 'Componentes para automação fiscal e comercial',
                'active' => true,
            ],
            [
                'system' => 'Easylinx',
                'name' => 'Fiscal Flow',
                'description' => 'Sistema de emissão de notas fiscais eletrônicas',
                'active' => true,
            ],
            [
                'system' => 'Easylinx',
                'name' => 'Uninfe',
                'description' => 'Biblioteca unificada de nota fiscal eletrônica',
                'active' => true,
            ],
            [
                'system' => 'Easylinx',
                'name' => 'NFePHP',
                'description' => 'Biblioteca PHP para NF-e e demais documentos fiscais',
                'active' => true,
            ],
            [
                'system' => 'SETA',
                'name' => 'SAT Fiscal',
                'description' => 'Integração com SAT-CF-e e MFe',
                'active' => true,
            ],
        ];

        foreach ($libraries as $library) {
            Library::create($library);
        }
    }
}
