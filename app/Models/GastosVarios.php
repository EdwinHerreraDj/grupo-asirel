<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GastosVarios extends Model
{
    use HasFactory;

    protected $table = 'gastos_varios';

    protected $fillable = [
        'obra_id',
        'tipo',
        'descripcion',
        'importe',
        'numero_factura',
        'archivo_factura',
    ];

    protected $casts = [
        'fecha' => 'date',
    ];

    public function obra()
    {
        return $this->belongsTo(Obra::class);
    }
}
