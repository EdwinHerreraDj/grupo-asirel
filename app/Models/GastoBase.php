<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


class GastoBase extends Model
{
    protected $table = 'gastos_base';

    protected $fillable = [
        'nombre',
        'descripcion',
        'activo',
    ];


    public function obras()
    {
        return $this->belongsToMany(Obra::class, 'obra_gastos_iniciales')
            ->withPivot('importe')
            ->withTimestamps();
    }
}
