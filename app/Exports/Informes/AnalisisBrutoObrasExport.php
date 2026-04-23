<?php

namespace App\Exports\Informes;

use App\Models\Empresa;
use App\Models\FacturaRecibida;
use App\Models\FacturaVenta;
use App\Models\Obra;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;

class AnalisisBrutoObrasExport implements FromView, WithTitle, ShouldAutoSize
{
    private const VENTA_ESTADOS_FISCALES = ['emitida', 'enviada', 'pagada'];
    private const RECIBIDA_ESTADOS_EXCLUIDOS = ['devuelta'];

    public function __construct(
        public ?int $obraId = null,
        public ?string $estado = null,
        public ?string $fechaInicio = null,
        public ?string $fechaFin = null,
    ) {}

    public function view(): View
    {
        $empresa = Empresa::first();

        $obras = Obra::query()
            ->when($this->obraId, fn($q) => $q->where('id', $this->obraId))
            ->when(
                $this->estado && $this->estado !== 'todas',
                fn($q) => $q->where('estado', $this->estado),
            )
            ->orderBy('nombre')
            ->get();

        $filas = $obras->map(function (Obra $obra) {
            // INGRESOS: facturación de venta sobre la obra (estados fiscales)
            $ingresosQuery = FacturaVenta::query()
                ->where('obra_id', $obra->id)
                ->whereIn('estado', self::VENTA_ESTADOS_FISCALES);

            if ($this->fechaInicio) {
                $ingresosQuery->whereDate('fecha_emision', '>=', $this->fechaInicio);
            }
            if ($this->fechaFin) {
                $ingresosQuery->whereDate('fecha_emision', '<=', $this->fechaFin);
            }

            $ingresosBase  = (float) $ingresosQuery->sum('base_imponible');
            $ingresosTotal = (float) $ingresosQuery->sum('total');

            // COSTES: facturas recibidas de la obra (excepto devueltas)
            $costesQuery = FacturaRecibida::query()
                ->where('obra_id', $obra->id)
                ->whereNotIn('estado', self::RECIBIDA_ESTADOS_EXCLUIDOS);

            if ($this->fechaInicio) {
                $costesQuery->whereDate('fecha_factura', '>=', $this->fechaInicio);
            }
            if ($this->fechaFin) {
                $costesQuery->whereDate('fecha_factura', '<=', $this->fechaFin);
            }

            $costesBase  = (float) $costesQuery->sum('base_imponible');
            $costesTotal = (float) $costesQuery->sum('total');

            $beneficioBruto = $ingresosBase - $costesBase;
            $margenPct = $ingresosBase > 0
                ? ($beneficioBruto / $ingresosBase) * 100
                : null;

            return [
                'id'              => $obra->id,
                'nombre'          => $obra->nombre,
                'estado'          => $obra->estado,
                'fecha_inicio'    => $obra->fecha_inicio,
                'fecha_fin'       => $obra->fecha_fin,
                'presupuestado'   => (float) ($obra->importe_presupuestado ?? 0),
                'ingresos_base'   => $ingresosBase,
                'ingresos_total'  => $ingresosTotal,
                'costes_base'     => $costesBase,
                'costes_total'    => $costesTotal,
                'beneficio_bruto' => $beneficioBruto,
                'margen_pct'      => $margenPct,
            ];
        });

        $totales = [
            'presupuestado'   => $filas->sum('presupuestado'),
            'ingresos_base'   => $filas->sum('ingresos_base'),
            'ingresos_total'  => $filas->sum('ingresos_total'),
            'costes_base'     => $filas->sum('costes_base'),
            'costes_total'    => $filas->sum('costes_total'),
            'beneficio_bruto' => $filas->sum('beneficio_bruto'),
        ];
        $totales['margen_pct'] = $totales['ingresos_base'] > 0
            ? ($totales['beneficio_bruto'] / $totales['ingresos_base']) * 100
            : null;

        return view('exports.informes.analisis-bruto-obras', [
            'empresa'     => $empresa,
            'filas'       => $filas,
            'totales'     => $totales,
            'fechaInicio' => $this->fechaInicio,
            'fechaFin'    => $this->fechaFin,
            'estado'      => $this->estado,
            'generadoEn'  => now()->format('d/m/Y H:i'),
        ]);
    }

    public function title(): string
    {
        return 'Análisis bruto obras';
    }
}
