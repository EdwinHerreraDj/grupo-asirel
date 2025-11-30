<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Material extends Model
{
    use HasFactory;

    protected $table = 'materiales';

    protected $fillable = [
        'obra_id',
        'nombre',
        'descripcion',
        'precio_unitario',
        'cantidad',
        'importe',
        'fecha',
        'archivo_factura',
        'numero_factura',
    ];

    protected $casts = [
        'fecha' => 'date',
    ];

    public function obra()
    {
        return $this->belongsTo(Obra::class);
    }
}
