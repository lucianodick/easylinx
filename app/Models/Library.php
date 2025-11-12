<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Library extends Model
{
    protected $fillable = [
        'system',
        'name',
        'description',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    /**
     * Relacionamento com versões
     */
    public function versions(): HasMany
    {
        return $this->hasMany(LibraryVersion::class);
    }

    /**
     * Scope para bibliotecas ativas
     */
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    /**
     * Retorna a versão padrão desta biblioteca
     */
    public function getDefaultVersion(): ?LibraryVersion
    {
        return $this->versions()
            ->active()
            ->whereNull('client_cnpj')
            ->first();
    }

    /**
     * Retorna os dados completos da versão para um cliente específico ou a versão padrão
     * Prioridade: Máquina > CNPJ > Padrão
     */
    public function getVersionDataForClient(?string $cnpj = null, ?string $machineName = null): ?array
    {
        $versionRecord = null;
        
        if ($cnpj) {
            // 1. Tenta buscar versão específica da máquina (case-insensitive)
            if ($machineName) {
                $machineNameLower = strtolower($machineName);
                $versionRecord = $this->versions()
                    ->active()
                    ->where('client_cnpj', $cnpj)
                    ->whereRaw('LOWER(machine_name) = ?', [$machineNameLower])
                    ->first();
            }

            // 2. Tenta buscar versão do CNPJ (sem máquina específica)
            if (!$versionRecord) {
                $versionRecord = $this->versions()
                    ->active()
                    ->where('client_cnpj', $cnpj)
                    ->whereNull('machine_name')
                    ->first();
            }
        }

        // 3. Busca versão padrão
        if (!$versionRecord) {
            $versionRecord = $this->getDefaultVersion();
        }
        
        if (!$versionRecord) {
            return null;
        }
        
        return [
            'version' => $versionRecord->version,
            'download_url_primary' => $versionRecord->download_url_primary,
            'download_url_secondary' => $versionRecord->download_url_secondary,
        ];
    }

    /**
     * Retorna apenas o número da versão (mantido para compatibilidade)
     */
    public function getVersionForClient(?string $cnpj = null, ?string $machineName = null): ?string
    {
        $data = $this->getVersionDataForClient($cnpj, $machineName);
        return $data['version'] ?? null;
    }
}
