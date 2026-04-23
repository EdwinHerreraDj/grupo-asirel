<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CertificacionEvento extends Model
{
    use HasFactory;

    protected $table = 'certificacion_eventos';

    protected $fillable = [
        'certificacion_id',
        'user_id',
        'tipo',
        'estado_previo',
        'estado_nuevo',
        'motivo',
    ];

    public function certificacion(): BelongsTo
    {
        return $this->belongsTo(Certificacion::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class);
    }
}
