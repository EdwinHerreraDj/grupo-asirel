<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subcontrata extends Model
{
    use HasFactory;

    protected $table = 'subcontratas';

    protected $fillable = [
        'obra_id',
        'nombre',
        'descripcion',
        'fecha',
        'importe',
        'archivo_factura',
        'archivo_contrato',
    ];

    public function obra()
    {
        return $this->belongsTo(Obra::class);
    }
}
