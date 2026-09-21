<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Tipo de documento que se pide a cada empleado (DNI, contrato,
 * reconocimiento médico...). Cada tipo activo es un apartado (subcarpeta)
 * dentro de la carpeta del empleado en el Drive.
 */
class RrhhTipoDocumento extends Model
{
    protected $table = 'rrhh_tipos_documento';

    protected $fillable = [
        'nombre',
        'obligatorio',
        'requiere_caducidad',
        'dias_aviso',
        'orden',
        'activo',
    ];

    protected $casts = [
        'obligatorio' => 'boolean',
        'requiere_caducidad' => 'boolean',
        'dias_aviso' => 'integer',
        'orden' => 'integer',
        'activo' => 'boolean',
    ];
}
