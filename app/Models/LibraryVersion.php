<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LibraryVersion extends Model
{
    protected $fillable = [
        'library_id',
        'client_cnpj',
        'machine_name',
        'version',
        'download_url_primary',
        'download_url_secondary',
        'active',
        'notes',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    /**
     * Relacionamento com biblioteca
     */
    public function library(): BelongsTo
    {
        return $this->belongsTo(Library::class);
    }

    /**
     * Scope para buscar apenas versões ativas
     */
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    /**
     * Scope para buscar a versão padrão
     */
    public function scopeDefault($query)
    {
        return $query->whereNull('client_cnpj');
    }

    /**
     * Scope para buscar versão de um cliente específico
     */
    public function scopeForClient($query, string $cnpj)
    {
        return $query->where('client_cnpj', $cnpj);
    }

    /**
     * Scope para uma biblioteca específica
     */
    public function scopeForLibrary($query, int $libraryId)
    {
        return $query->where('library_id', $libraryId);
    }

    /**
     * Scope para uma máquina específica
     */
    public function scopeForMachine($query, string $machineName)
    {
        return $query->where('machine_name', $machineName);
    }

    /**
     * Verifica se é a versão padrão
     */
    public function isDefault(): bool
    {
        return is_null($this->client_cnpj);
    }
}
