<?php

namespace App\Observers;

use App\Models\GastoInicialPartida;
use App\Models\PresupuestoVentaPartida;

/**
 * Sincroniza los campos descriptivos compartidos (codigo, descripcion, unidad)
 * desde una partida de coste hacia las partidas de venta vinculadas,
 * siempre que estas sigan siendo editables (no certificadas).
 *
 * Campos econ\u00f3micos (medicion, precio_unitario, importe) NO se sincronizan:
 * la venta se pacta con el cliente y es independiente del coste.
 */
class GastoInicialPartidaObserver
{
    private const CAMPOS_SINCRONIZABLES = ['codigo', 'descripcion', 'unidad'];

    public function updated(GastoInicialPartida $partida): void
    {
        $cambios = $this->recolectarCambios($partida);

        if (empty($cambios)) {
            return;
        }

        PresupuestoVentaPartida::where('coste_partida_id', $partida->id)
            ->whereDoesntHave('certificacionDetalles')
            ->update($cambios);
    }

    /**
     * Devuelve solo los campos descriptivos que realmente cambiaron.
     */
    private function recolectarCambios(GastoInicialPartida $partida): array
    {
        $cambios = [];

        foreach (self::CAMPOS_SINCRONIZABLES as $campo) {
            if ($partida->wasChanged($campo)) {
                $cambios[$campo] = $partida->{$campo};
            }
        }

        return $cambios;
    }
}
