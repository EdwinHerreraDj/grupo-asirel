<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ObraGastoCategoria extends Model
{
    protected $table = 'obra_gasto_categorias';

    protected $fillable = [
        'obra_id',
        'nombre',
        'descripcion',
    ];

    public function obra()
    {
        return $this->belongsTo(Obra::class);
    }
    public function facturas()
    {
        return $this->hasMany(FacturaRecibida::class, 'oficio_id');
    }
}
    