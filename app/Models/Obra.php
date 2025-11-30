<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Obra extends Model
{
    use HasFactory;

    protected $table = 'obras';

    protected $fillable = [
        'nombre',
        'descripcion',
        'estado',
        'fecha_inicio',
        'fecha_fin',
        'importe_presupuestado',
        'importe_ejecutado',
        'latitud',
        'longitud',
        'radio',
    ];

    public function documentos()
    {
        return $this->hasMany(Documento::class);
    }

    public function materiales()
    {
        return $this->hasMany(Material::class);
    }

    public function alquileres()
    {
        return $this->hasMany(Alquiler::class);
    }

    public function subcontratas()
    {
        return $this->hasMany(Subcontrata::class);
    }

    public function gastosVarios()
    {
        return $this->hasMany(GastosVarios::class);
    }

    public function ventas()
    {
        return $this->hasMany(Venta::class);
    }

    public function categoriasGasto()
    {
        return $this->hasMany(ObraGastoCategoria::class);
    }

    public function gastosIniciales()
    {
        return $this->belongsToMany(ObraGastoCategoria::class, 'obra_gastos_iniciales')
            ->withPivot('importe')
            ->withTimestamps();
    }

    public function facturasRecibidas()
    {
        return $this->hasMany(FacturaRecibida::class);
    }
}