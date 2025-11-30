<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Alquiler extends Model
{
    use HasFactory;

    protected $table = 'alquileres';

    protected $fillable = [
        'obra_id',
        'nombre',
        'descripcion',
        'precio_unitario',
        'cantidad',
        'importe',
        'numero_factura',
        'archivo_factura',
        'fecha',
    ];

    protected $casts = [
        'fecha' => 'date',
    ];

    // Relación con la tabla 'obras'
    public function obra()
    {
        return $this->belongsTo(Obra::class);
    }
}
