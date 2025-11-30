<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Folder;
use App\Models\User;

class File extends Model
{
    protected $fillable = [
        'folder_id',
        'usuario_id',
        'nombre',
        'ruta',
        'tipo',
        'tamaño',
        'tiene_caducidad',
        'fecha_caducidad',
    ];


    public function folder(): BelongsTo
    {
        return $this->belongsTo(Folder::class);
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function getUrlAttribute(): string
    {
        return asset('storage/' . $this->ruta);
    }
}
