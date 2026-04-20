<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Documento extends Model
{
    use HasFactory;

    protected $fillable = [
        'obra_id',
        'documento_tipo_id',
        'tipo',
        'archivo',
        'nombre_original',
        'mime_type',
        'size',
        'fecha_vencimiento',
    ];

    protected $casts = [
        'size' => 'integer',
        'fecha_vencimiento' => 'date',
    ];

    protected static function booted(): void
    {
        static::deleting(function (Documento $documento) {
            if ($documento->archivo && Storage::disk('public')->exists($documento->archivo)) {
                Storage::disk('public')->delete($documento->archivo);
            }
        });
    }

    public function obra(): BelongsTo
    {
        return $this->belongsTo(Obra::class);
    }

    public function documentoTipo(): BelongsTo
    {
        return $this->belongsTo(DocumentoTipo::class);
    }
}
