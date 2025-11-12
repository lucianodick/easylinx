<?php

namespace App\Http\Controllers;

use App\Models\Library;
use App\Models\LibraryVersion;
use App\Models\ApiRequestLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;

class LibraryVersionController extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Library $library)
    {
        $validated = $request->validate([
            'client_cnpj' => [
                'nullable',
                'string',
                'max:18',
                function ($attribute, $value, $fail) use ($library, $request) {
                    $machineName = $request->input('machine_name');
                    $machineNameLower = $machineName ? strtolower($machineName) : null;
                    
                    $exists = LibraryVersion::where('library_id', $library->id)
                        ->where('client_cnpj', $value)
                        ->whereRaw('LOWER(machine_name) = ?', [$machineNameLower])
                        ->exists();
                    if ($exists) {
                        $fail('Já existe uma versão cadastrada para este CNPJ/Máquina nesta biblioteca.');
                    }
                },
            ],
            'machine_name' => 'nullable|string|max:100',
            'version' => 'required|string|max:20',
            'download_url_primary' => 'nullable|url|max:500',
            'download_url_secondary' => 'nullable|url|max:500',
            'active' => 'boolean',
            'notes' => 'nullable|string',
        ]);

        // Converte machine_name para minúsculas
        if (!empty($validated['machine_name'])) {
            $validated['machine_name'] = strtolower($validated['machine_name']);
        }

        $validated['library_id'] = $library->id;
        LibraryVersion::create($validated);

        // Invalida o cache relacionado
        $this->clearVersionCache($validated['client_cnpj'], $validated['machine_name']);

        return back()->with('success', 'Versão criada com sucesso!');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Library $library, LibraryVersion $version)
    {
        // Normaliza machine_name para comparação case-insensitive
        $machineName = $request->input('machine_name');
        $machineNameLower = $machineName ? strtolower($machineName) : null;

        $validated = $request->validate([
            'client_cnpj' => [
                'nullable',
                'string',
                'max:18',
                function ($attribute, $value, $fail) use ($library, $version, $machineNameLower) {
                    $exists = LibraryVersion::where('library_id', $library->id)
                        ->where('client_cnpj', $value)
                        ->whereRaw('LOWER(machine_name) = ?', [$machineNameLower])
                        ->where('id', '!=', $version->id)
                        ->exists();
                    if ($exists) {
                        $fail('Já existe uma versão cadastrada para este CNPJ/Máquina nesta biblioteca.');
                    }
                },
            ],
            'machine_name' => 'nullable|string|max:100',
            'version' => 'required|string|max:20',
            'download_url_primary' => 'nullable|url|max:500',
            'download_url_secondary' => 'nullable|url|max:500',
            'active' => 'boolean',
            'notes' => 'nullable|string',
        ]);

        // Normaliza machine_name para lowercase antes de salvar
        if (!empty($validated['machine_name'])) {
            $validated['machine_name'] = strtolower($validated['machine_name']);
        }

        // Invalida cache antigo (antes de atualizar)
        $this->clearVersionCache($version->client_cnpj, $version->machine_name);

        $version->update($validated);

        // Invalida cache novo (caso CNPJ/máquina tenham mudado)
        $this->clearVersionCache($validated['client_cnpj'], $validated['machine_name']);

        return back()->with('success', 'Versão atualizada com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Library $library, LibraryVersion $version)
    {
        // Invalida o cache antes de deletar
        $this->clearVersionCache($version->client_cnpj, $version->machine_name);

        $version->delete();

        return back()->with('success', 'Versão excluída com sucesso!');
    }

    /**
     * Retorna todas as versões de todas as bibliotecas para um cliente (API)
     */
    public function getVersions(Request $request)
    {
        // Valida parâmetros obrigatórios
        $validated = $request->validate([
            'system' => 'required|string',
            'cnpj' => 'required|string',
            'machine_name' => 'required|string',
        ]);

        // Remove formatação do CNPJ (mantém apenas números)
        $cnpj = preg_replace('/[^0-9]/', '', $validated['cnpj']);
        $system = $validated['system'];
        $machineName = strtolower($validated['machine_name']); // Normaliza para lowercase
        
        // Cache por 15 minutos para reduzir carga no banco
        $cacheKey = $this->getCacheKey($cnpj, $machineName, $system);
        
        $libraries = Cache::remember($cacheKey, now()->addMinutes(15), function () use ($cnpj, $machineName, $system) {
            return Library::active()
                ->where('system', $system)
                ->get()
                ->map(function ($library) use ($cnpj, $machineName) {
                    $versionData = $library->getVersionDataForClient($cnpj, $machineName);
                    
                    return [
                        'library' => $library->name,
                        'version' => $versionData['version'] ?? null,
                        'download_url_primary' => $versionData['download_url_primary'] ?? null,
                        'download_url_secondary' => $versionData['download_url_secondary'] ?? null,
                    ];
                });
        });

        return response()->json([
            'cnpj' => $cnpj,
            'machine_name' => $machineName,
            'system' => $system,
            'libraries' => $libraries,
        ]);
    }

    /**
     * Gera a chave de cache
     */
    private function getCacheKey(?string $cnpj, ?string $machineName, ?string $system = null): string
    {
        return 'library_versions_' 
            . ($system ?? 'all_systems') . '_'
            . ($cnpj ?? 'default') . '_' 
            . ($machineName ?? 'no_machine');
    }

    /**
     * Limpa TODO o cache de versões
     */
    private function clearVersionCache(?string $cnpj = null, ?string $machineName = null): void
    {
        // Limpa TODOS os caches que começam com 'library_versions_'
        Cache::flush();
        
        // Alternativa mais específica (se não quiser limpar todo cache do sistema):
        // Cache::forget('library_versions_*'); // Requer driver que suporte padrões
    }
}
