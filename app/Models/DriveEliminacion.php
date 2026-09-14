<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Registro de borrados del Drive (carpetas completas y archivos).
 */
class DriveEliminacion extends Model
{
    protected $table = 'drive_eliminaciones';

    protected $fillable = [
        'user_id',
        'tipo',
        'nombre',
        'ubicacion',
        'carpetas',
        'archivos',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
