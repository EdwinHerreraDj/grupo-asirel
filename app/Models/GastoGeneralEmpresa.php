<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GastoGeneralEmpresa extends Model
{
    protected $table = 'gastos_generales_empresa';

    protected $fillable = [
        'concepto',
        'importe',
        'fecha_factura',
        'fecha_contable',
        'numero_factura',
        'descripcion',
        'especificacion',
        'fecha_vencimiento',
        'factura_url',
        'categoria_id',
    ];

    public function categoria()
    {
        return $this->belongsTo(CategoriaGastoEmpresa::class, 'categoria_id');
    }

}
