<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Venta extends Model
{
    use HasFactory;

    protected $table = 'ventas';

    protected $fillable = [
        'obra_id',
        'nombre',
        'descripcion',
        'importe',
        'fecha',
        'archivo_certificado',
    ];

    public function obra()
    {
        return $this->belongsTo(Obra::class);
    }
}
