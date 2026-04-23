<?php

namespace App\Exports\Informes;

use App\Models\Empresa;
use App\Models\FacturaRecibida;
use App\Models\FacturaVenta;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;

class LiquidacionIvaExport implements FromView, WithTitle, ShouldAutoSize
{
    // Estados de FacturaVenta que cuentan fiscalmente
    private const VENTA_ESTADOS_FISCALES = ['emitida', 'enviada', 'pagada'];

    // Estados de FacturaRecibida excluidos (devuelta = no se deduce)
    private const RECIBIDA_ESTADOS_EXCLUIDOS = ['devuelta'];

    public function __construct(
        public ?string $fechaInicio = null,
        public ?string $fechaFin = null,
    ) {}

    public function view(): View
    {
        $empresa = Empresa::first();

        // ===== FACTURAS EMITIDAS (IVA repercutido) =====
        $emitidas = FacturaVenta::query()
            ->with('cliente:id,nombre')
            ->whereIn('estado', self::VENTA_ESTADOS_FISCALES)
            ->when($this->fechaInicio, fn($q) => $q->whereDate('fecha_emision', '>=', $this->fechaInicio))
            ->when($this->fechaFin, fn($q) => $q->whereDate('fecha_emision', '<=', $this->fechaFin))
            ->orderBy('fecha_emision')
            ->get();

        // ===== FACTURAS RECIBIDAS (IVA soportado) =====
        $recibidas = FacturaRecibida::query()
            ->with('proveedor:id,nombre')
            ->whereNotIn('estado', self::RECIBIDA_ESTADOS_EXCLUIDOS)
            ->when($this->fechaInicio, fn($q) => $q->whereDate('fecha_factura', '>=', $this->fechaInicio))
            ->when($this->fechaFin, fn($q) => $q->whereDate('fecha_factura', '<=', $this->fechaFin))
            ->orderBy('fecha_factura')
            ->get();

        // ===== AGRUPACIONES POR TIPO DE IVA =====
        $emitidasPorTipo = $emitidas
            ->groupBy(fn($f) => (string) (float) $f->iva_porcentaje)
            ->map(fn($grupo) => [
                'porcentaje' => (float) $grupo->first()->iva_porcentaje,
                'base'       => (float) $grupo->sum('base_imponible'),
                'cuota'      => (float) $grupo->sum('iva_importe'),
                'count'      => $grupo->count(),
            ])
            ->sortBy('porcentaje')
            ->values();

        $recibidasPorTipo = $recibidas
            ->groupBy(fn($f) => (string) (float) $f->iva_porcentaje)
            ->map(fn($grupo) => [
                'porcentaje' => (float) $grupo->first()->iva_porcentaje,
                'base'       => (float) $grupo->sum('base_imponible'),
                'cuota'      => (float) $grupo->sum('iva_importe'),
                'count'      => $grupo->count(),
            ])
            ->sortBy('porcentaje')
            ->values();

        // ===== TOTALES =====
        $totalRepercutido = (float) $emitidas->sum('iva_importe');
        $totalSoportado   = (float) $recibidas->sum('iva_importe');
        $liquidacion      = $totalRepercutido - $totalSoportado;

        return view('exports.informes.liquidacion-iva', [
            'empresa'          => $empresa,
            'emitidas'         => $emitidas,
            'recibidas'        => $recibidas,
            'emitidasPorTipo'  => $emitidasPorTipo,
            'recibidasPorTipo' => $recibidasPorTipo,
            'totalRepercutido' => $totalRepercutido,
            'totalSoportado'   => $totalSoportado,
            'liquidacion'      => $liquidacion,
            'fechaInicio'      => $this->fechaInicio,
            'fechaFin'         => $this->fechaFin,
            'generadoEn'       => now()->format('d/m/Y H:i'),
        ]);
    }

    public function title(): string
    {
        return 'Liquidación IVA';
    }
}
