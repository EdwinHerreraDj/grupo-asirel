<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Documento extends Model
{
    use HasFactory;

    protected $fillable = ['obra_id', 'tipo', 'archivo'];

    public function obra()
    {
        return $this->belongsTo(Obra::class);
    }
}
