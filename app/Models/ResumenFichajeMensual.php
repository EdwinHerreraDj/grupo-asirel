<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ResumenFichajeMensual extends Model
{
    protected $table = 'resumen_fichajes_mensuales';

    protected $fillable = [
        'obra_id',
        'empleado_id',
        'mes',
        'horas_trabajadas',
        'tarifa_hora',
        'total_ganado',
        'metros_realizados',
    ];

    public function obra()
    {
        return $this->belongsTo(Obra::class);
    }
}
