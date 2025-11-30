<?php

namespace App\Livewire;

use App\Models\Obra;
use App\Models\ResumenFichajeMensual;
use Livewire\Component;
use App\Models\FacturaRecibida;
use App\Models\Certificacion;

class FiltroObras extends Component
{
    public $estado = '';

    /*  public function render()
    {
        $query = Obra::with(['materiales', 'alquileres', 'subcontratas', 'gastosVarios', 'ventas', 'documentos']);

        if ($this->estado) {
            $query->where('estado', $this->estado);
        }

        $obras = $query->get()->map(function ($obra) {
            // Totales base
            $total_materiales = $obra->materiales->sum('importe');
            $total_alquileres = $obra->alquileres->sum('importe');
            $total_subcontratas = $obra->subcontratas->sum('importe');
            $total_gastos_varios = $obra->gastosVarios->sum('importe');
            $total_ventas = $obra->ventas->sum('importe');
            $total_documentos = $obra->documentos->count();
            $total_ganado = ResumenFichajeMensual::where('obra_id', $obra->id)->sum('total_ganado');

            // Progreso de documentos (mismo que tu controlador)
            $progreso_documentos = min(100, ($total_documentos / 9) * 100);

            // Balance igual que antes
            $total_gastos = $total_materiales + $total_alquileres + $total_subcontratas + $total_gastos_varios + $total_ganado;

            $balance = $total_gastos > 0
                ? min(100, ($total_ventas / $total_gastos) * 100)
                : ($total_ventas > 0 ? 100 : 0);

            // Atributos calculados
            $obra->setAttribute('total_materiales', $total_materiales);
            $obra->setAttribute('total_alquileres', $total_alquileres);
            $obra->setAttribute('total_subcontratas', $total_subcontratas);
            $obra->setAttribute('total_gastos_varios', $total_gastos_varios);
            $obra->setAttribute('total_ventas', $total_ventas);
            $obra->setAttribute('total_gastos', $total_gastos);
            $obra->setAttribute('balance', round($balance, 2));
            $obra->setAttribute('total_documentos', $total_documentos);
            $obra->setAttribute('progreso_documentos', round($progreso_documentos, 2));
            $obra->setAttribute('total_ganado_empleados', round($total_ganado, 2));

            return $obra;
        });

        return view('livewire.filtro-obras', [
            'obras' => $obras,
        ]);
    } */

    public function render()
    {
        $query = Obra::with(['documentos']);

        if ($this->estado) {
            $query->where('estado', $this->estado);
        }

        $obras = $query->get()->map(function ($obra) {

            // ===========================
            // GASTOS: Facturas pagadas
            // ===========================
            $total_gastos = FacturaRecibida::where('obra_id', $obra->id)
                ->where('estado', 'pagada')
                ->sum('importe');

            // ===========================
            // INGRESOS: Certificaciones
            // ===========================
            $total_ingresos = Certificacion::where('obra_id', $obra->id)
                ->where('tipo_documento', 'certificacion')
                ->sum('total');

            // ===========================
            // DOCUMENTOS
            // ===========================
            $total_documentos = $obra->documentos->count();
            $progreso_documentos = min(100, ($total_documentos / 9) * 100);

            // ===========================
            // BALANCE
            // ===========================
            $balance = $total_gastos > 0
                ? min(100, ($total_ingresos / $total_gastos) * 100)
                : ($total_ingresos > 0 ? 100 : 0);

            // ===========================
            // Atributos calculados
            // ===========================
            $obra->setAttribute('total_gastos', round($total_gastos, 2));
            $obra->setAttribute('total_ventas', round($total_ingresos, 2));
            $obra->setAttribute('balance', round($balance, 2));
            $obra->setAttribute('total_documentos', $total_documentos);
            $obra->setAttribute('progreso_documentos', round($progreso_documentos, 2));

            return $obra;
        });

        return view('livewire.filtro-obras', [
            'obras' => $obras,
        ]);
    } 
}
