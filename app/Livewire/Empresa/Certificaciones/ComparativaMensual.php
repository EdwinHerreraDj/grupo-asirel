<?php

namespace App\Livewire\Empresa\Certificaciones;

use Livewire\Component;
use App\Models\Obra;
use App\Models\ObraPresupuestoVenta;
use App\Models\CertificacionDetalle;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class ComparativaMensual extends Component
{
    public int $obraId;

    /** Periodo en formato YYYY-MM */
    public string $periodo;
    public string $tmpPeriodo;

    public function mount(int $obraId): void
    {
        $this->obraId     = $obraId;
        $this->periodo   = now()->format('Y-m');
        $this->tmpPeriodo = $this->periodo;
    }

    public function aplicarPeriodo(): void
    {
        $this->periodo = $this->tmpPeriodo;
    }

    protected function inicioMes(): Carbon
    {
        return Carbon::createFromFormat('Y-m', $this->periodo)->startOfMonth();
    }

    protected function finMes(): Carbon
    {
        return Carbon::createFromFormat('Y-m', $this->periodo)->endOfMonth();
    }

    protected function obtenerFilas(): array
    {
        $obra = Obra::findOrFail($this->obraId);

        $inicio = $this->inicioMes();
        $fin    = $this->finMes();
        $finMesAnterior = $inicio->copy()->subDay();

        $presupuestos = ObraPresupuestoVenta::where('obra_id', $this->obraId)
            ->get()
            ->keyBy('obra_gasto_categoria_id');

        $oficios = $obra->categoriasGasto()
            ->orderByRaw("CAST(SUBSTRING_INDEX(nombre, '-', 1) AS UNSIGNED)")
            ->orderBy('nombre')
            ->get();

        // Medición del mes
        $medicionMes = CertificacionDetalle::selectRaw(
            'certificaciones.obra_gasto_categoria_id, SUM(certificacion_detalles.cantidad) as total'
        )
            ->join('certificaciones', 'certificacion_detalles.certificacion_id', '=', 'certificaciones.id')
            ->where('certificaciones.obra_id', $this->obraId)
            ->whereBetween('certificaciones.fecha_ingreso', [$inicio, $fin])
            ->groupBy('certificaciones.obra_gasto_categoria_id')
            ->pluck('total', 'obra_gasto_categoria_id');

        // Origen hasta mes anterior
        $origenAnterior = CertificacionDetalle::selectRaw(
            'certificaciones.obra_gasto_categoria_id, SUM(certificacion_detalles.cantidad) as total'
        )
            ->join('certificaciones', 'certificacion_detalles.certificacion_id', '=', 'certificaciones.id')
            ->where('certificaciones.obra_id', $this->obraId)
            ->where('certificaciones.fecha_ingreso', '<=', $finMesAnterior)
            ->groupBy('certificaciones.obra_gasto_categoria_id')
            ->pluck('total', 'obra_gasto_categoria_id');

        $filas = [];

        foreach ($oficios as $oficio) {

            $presupuesto = $presupuestos[$oficio->id] ?? null;

            $contrato = (float) ($presupuesto?->cantidad ?? 0);
            $unidad   = $presupuesto?->unidad ?? '';
            $precio   = (float) ($presupuesto?->precio_unitario ?? 0);

            $mes        = (float) ($medicionMes[$oficio->id] ?? 0);
            $origenAnt  = (float) ($origenAnterior[$oficio->id] ?? 0);
            $aOrigen    = $origenAnt + $mes;

            $filas[] = [
                'oficio'          => $oficio->nombre,
                'unidad'          => $unidad,
                'contrato'        => $contrato,
                'origen_anterior' => $origenAnt,
                'mes'             => $mes,
                'a_origen'        => $aOrigen,
                'pendiente'       => $contrato - $aOrigen,
                'precio_unitario' => $precio,
                'importe_mes'     => $mes * $precio,
                'importe_origen'  => $aOrigen * $precio,
            ];
        }

        return $filas;
    }

    public function generarPdf()
    {
        $obra  = Obra::findOrFail($this->obraId);
        $filas = $this->obtenerFilas();

        return response()->streamDownload(
            fn() => print(
                Pdf::loadView('pdf.comparativa-mensual', [
                    'obra'    => $obra,
                    'filas'   => $filas,
                    'periodo' => $this->periodo,
                ])->output()
            ),
            'comparativa-' . $obra->id . '-' . $this->periodo . '.pdf'
        );
    }


    public function render()
    {
        return view('livewire.empresa.certificaciones.comparativa-mensual', [
            'filas' => $this->obtenerFilas(),
        ]);
    }
}
