<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FacturaSerie extends Model
{
    protected $table = 'factura_series';

    protected $fillable = [
        'id',
        'serie',
        'ultimo_numero',
        'activa',
    ];
}
