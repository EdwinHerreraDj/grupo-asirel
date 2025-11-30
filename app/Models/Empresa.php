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
    ];
}
