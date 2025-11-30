<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Certificacion extends Model
{
    protected $table = 'certificaciones';

    protected $fillable = [
        'obra_id',
        'fecha_ingreso',
        'fecha_contable',
        'fecha_vencimiento',
        'numero_certificacion',
        'obra_gasto_categoria_id',
        'especificacion',
        'tipo_documento',
        'total',
        'adjunto_url',
    ];

    public function obra()
    {
        return $this->belongsTo(Obra::class);
    }

    public function oficio()
    {
        return $this->belongsTo(ObraGastoCategoria::class, 'obra_gasto_categoria_id');
    }
}
