<?php

namespace App\Services;

use App\Models\Obra;
use Illuminate\Support\Facades\DB;

class ObraService
{
    public function crear(array $data): Obra
    {
        return DB::transaction(function () use ($data) {
            return Obra::create([
                'nombre' => $data['nombre'],
                'estado' => $data['estado'],
                'fecha_inicio' => $data['fecha_inicio'] ?? null,
                'fecha_fin' => $data['fecha_fin'] ?? null,
                'importe_presupuestado' => $data['importe_presupuestado'],
                'descripcion' => $data['descripcion'] ?? null,
            ]);
        });
    }

    public function actualizar(Obra $obra, array $data): Obra
    {
        return DB::transaction(function () use ($obra, $data) {
            $obra->update([
                'nombre' => $data['nombre'],
                'estado' => $data['estado'],
                'fecha_inicio' => $data['fecha_inicio'] ?? null,
                'fecha_fin' => $data['fecha_fin'] ?? null,
                'importe_presupuestado' => $data['importe_presupuestado'],
                'descripcion' => $data['descripcion'] ?? null,
            ]);

            return $obra->fresh();
        });
    }

    public function eliminar(Obra $obra): void
    {
        DB::transaction(function () use ($obra) {
            $obra->delete();
        });
    }
}
