<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Proveedor extends Model
{
    use HasFactory;

    protected $table = 'proveedores';

    protected $fillable = [
        'nombre',
        'cif',
        'telefono',
        'email',
        'direccion',
        'tipo',
        'activo',
    ];

    public function facturas()
    {
        return $this->hasMany(FacturaRecibida::class);
    }
}
