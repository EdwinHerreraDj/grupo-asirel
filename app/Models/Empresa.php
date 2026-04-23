<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Empresa extends Model
{
    protected $table = 'empresa';

    protected $fillable = [
        'nombre',
        'cif',
        'direccion',
        'codigo_postal',
        'ciudad',
        'provincia',
        'pais',
        'telefono',
        'email',
        'sitio_web',
        'logo',
        'descripcion',
        'color_primario',
        'color_secundario',
        'mostrar_logo_pdf',
        'pie_pdf',
    ];

    protected $casts = [
        'mostrar_logo_pdf' => 'boolean',
    ];

    protected $attributes = [
        'color_primario'   => '#111827',
        'color_secundario' => '#d1d5db',
        'mostrar_logo_pdf' => true,
    ];
}
