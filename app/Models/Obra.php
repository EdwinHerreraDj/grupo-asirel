<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Obra extends Model
{
    use HasFactory;

    protected $table = 'obras';

    public const ESTADO_PLANIFICACION = 'planificacion';
    public const ESTADO_EJECUCION     = 'ejecucion';
    public const ESTADO_EN_PAUSA      = 'en_pausa';
    public const ESTADO_FINALIZADA    = 'finalizada';

    public const ESTADOS = [
        self::ESTADO_PLANIFICACION => 'Planificación',
        self::ESTADO_EJECUCION     => 'En ejecución',
        self::ESTADO_EN_PAUSA      => 'En pausa',
        self::ESTADO_FINALIZADA    => 'Finalizada',
    ];

    public const TIPO_SUBCONTRATISTA = 'subcontratista';
    public const TIPO_CONTRATISTA    = 'contratista';

    public const TIPOS = [
        self::TIPO_SUBCONTRATISTA => 'Subcontratista',
        self::TIPO_CONTRATISTA    => 'Contratista',
    ];

    protected $fillable = [
        'nombre',
        'descripcion',
        'estado',
        'tipo',
        'fecha_inicio',
        'fecha_fin',
        'importe_presupuestado',
        'importe_ejecutado',
        'latitud',
        'longitud',
        'radio',
    ];

    protected $casts = [
        'fecha_inicio'          => 'date',
        'fecha_fin'             => 'date',
        'importe_presupuestado' => 'float',
        'importe_ejecutado'     => 'float',
        'latitud'               => 'float',
        'longitud'              => 'float',
        'radio'                 => 'float',
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

    public function facturasRecibidas()
    {
        return $this->hasMany(FacturaRecibida::class);
    }
}