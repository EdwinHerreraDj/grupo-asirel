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
        return $this->belongsTo(self::class, 'parent_id')->where('id', '>', 0);
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
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
