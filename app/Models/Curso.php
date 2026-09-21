<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Curso o formación de un empleado (PRL, carnets, oficio…). */
class Curso extends Model
{
    public const CATEGORIAS = [
        'prl' => 'Prevención de riesgos (PRL)',
        'oficio' => 'Oficio / especialidad',
        'carnet' => 'Carnet o habilitación',
        'otro' => 'Otro',
    ];

    /** Días de antelación con que se avisa de la caducidad. */
    public const DIAS_AVISO = 30;

    protected $table = 'rrhh_cursos';

    protected $fillable = [
        'empleado_id', 'nombre', 'categoria', 'entidad', 'horas',
        'fecha', 'fecha_caducidad', 'file_id', 'observaciones', 'usuario_id',
    ];

    protected $casts = [
        'horas' => 'float',
        'fecha' => 'date:Y-m-d',
        'fecha_caducidad' => 'date:Y-m-d',
    ];

    public function empleado(): BelongsTo
    {
        return $this->belongsTo(Empleado::class);
    }

    public function archivo(): BelongsTo
    {
        return $this->belongsTo(File::class, 'file_id');
    }
}
