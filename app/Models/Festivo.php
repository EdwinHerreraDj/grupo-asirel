<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Día festivo de la empresa (nacional, autonómico o local). */
class Festivo extends Model
{
    protected $table = 'rrhh_festivos';

    protected $fillable = ['fecha', 'nombre'];

    protected $casts = [
        'fecha' => 'date:Y-m-d',
    ];
}
