<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CategoriaGastoEmpresa extends Model
{
    use HasFactory;

    protected $table = 'categorias_gastos_empresa';

    protected $fillable = [
        'codigo',
        'nombre',
        'descripcion',
        'parent_id',
        'nivel',
    ];

    protected $casts = [
        'nivel' => 'integer',
    ];

    /**
     * Categoría padre (si existe)
     */
    public function parent()
    {
        return $this->belongsTo(CategoriaGastoEmpresa::class, 'parent_id');
    }

    /**
     * Subcategorías (hijos)
     */
    public function children()
    {
        return $this->hasMany(CategoriaGastoEmpresa::class, 'parent_id');
    }

    /**
     * Relación con gastos reales
     */
    public function gastos()
    {
        return $this->hasMany(GastoGeneralEmpresa::class, 'categoria_id');
    }

    /**
     * Accesor para saber si es categoría padre
     */
    public function getEsPadreAttribute()
    {
        return $this->nivel === 1;
    }

    /**
     * Accesor para saber si es una subcategoría
     */
    public function getEsHijaAttribute()
    {
        return $this->nivel === 2;
    }

    /**
     * Orden predeterminado por código contable
     */
    public function scopeOrdenado($query)
    {
        return $query->orderBy('codigo', 'asc');
    }

    /**
     * Obtener lista de categorías padre (nivel 1)
     */
    public static function padres()
    {
        return self::whereNull('parent_id')->ordenado()->get();
    }

    /**
     * Obtener subcategorías de un padre
     */
    public static function subcategoriasDe($parentId)
    {
        return self::where('parent_id', $parentId)->ordenado()->get();
    }
}
