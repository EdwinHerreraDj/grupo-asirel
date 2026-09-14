<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Folder extends Model
{
    protected $fillable = [
        'parent_id',
        'usuario_id',
        'nombre',
        'tipo',
    ];

    public function files(): HasMany
    {
        return $this->hasMany(File::class);
    }


    // Helpers
    public function isRoot(): bool
    {
        return (int) $this->parent_id === 0;
    }

    // Relaciones (ojo: en raíz parent_id=0, no hay fila con id=0)
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }


    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    /**
     * Ruta legible desde la raíz: "Inicio / Empresa / Contratos".
     */
    public function rutaCompleta(): string
    {
        $partes = [];
        $visitadas = [];
        $actual = $this;

        while ($actual && ! isset($visitadas[$actual->id])) {
            $visitadas[$actual->id] = true;
            array_unshift($partes, $actual->nombre);
            $actual = $actual->parent_id > 0 ? self::find($actual->parent_id) : null;
        }

        return 'Inicio'.($partes ? ' / '.implode(' / ', $partes) : '');
    }

    /* Scopes útiles */
    public function scopeRoot($query)
    {
        return $query->where('parent_id', 0);
    }

    public function scopeInParent($query, int $parentId)
    {
        return $query->where('parent_id', $parentId);
    }
}
