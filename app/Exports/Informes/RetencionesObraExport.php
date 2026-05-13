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

class RetencionesObraExport implements FromView, WithTitle, ShouldAutoSize
{
    private const VENTA_ESTADOS_FISCALES = ['emitida', 'enviada', 'pagada'];
    private const RECIBIDA_ESTADOS_EXCLUIDOS = ['devuelta'];

    public function __construct(
        public int $obraId,
        public ?string $fechaInicio = null,
        public ?string $fechaFin = null,
    ) {}

    public function view(): View
    {
        $empresa = Empresa::first();
        $obra = Obra::findOrFail($this->obraId);

        // Retenciones de facturas EMITIDAS (el cliente nos retiene)
        $emitidas = FacturaVenta::query()
            ->with('cliente:id,nombre')
            ->where('obra_id', $this->obraId)
            ->whereIn('estado', self::VENTA_ESTADOS_FISCALES)
            ->where('retencion_importe', '>', 0)
            ->when($this->fechaInicio, fn ($q) => $q->whereDate('fecha_emision', '>=', $this->fechaInicio))
            ->when($this->fechaFin, fn ($q) => $q->whereDate('fecha_emision', '<=', $this->fechaFin))
            ->orderBy('fecha_emision')
            ->get();

        // Retenciones de facturas RECIBIDAS (nosotros retenemos al proveedor)
        $recibidas = FacturaRecibida::query()
            ->with('proveedor:id,nombre')
            ->where('obra_id', $this->obraId)
            ->whereNotIn('estado', self::RECIBIDA_ESTADOS_EXCLUIDOS)
            ->where('retencion_importe', '>', 0)
            ->when($this->fechaInicio, fn ($q) => $q->whereDate('fecha_factura', '>=', $this->fechaInicio))
            ->when($this->fechaFin, fn ($q) => $q->whereDate('fecha_factura', '<=', $this->fechaFin))
            ->orderBy('fecha_factura')
            ->get();

        // Agrupaciones por tipo de retención (porcentaje)
        $emitidasPorTipo = $emitidas
            ->groupBy(fn ($f) => (string) (float) $f->retencion_porcentaje)
            ->map(fn ($grupo) => [
                'porcentaje' => (float) $grupo->first()->retencion_porcentaje,
                'base'       => (float) $grupo->sum('base_imponible'),
                'retencion'  => (float) $grupo->sum('retencion_importe'),
                'count'      => $grupo->count(),
            ])
            ->sortBy('porcentaje')
            ->values();

        $recibidasPorTipo = $recibidas
            ->groupBy(fn ($f) => (string) (float) $f->retencion_porcentaje)
            ->map(fn ($grupo) => [
                'porcentaje' => (float) $grupo->first()->retencion_porcentaje,
                'base'       => (float) $grupo->sum('base_imponible'),
                'retencion'  => (float) $grupo->sum('retencion_importe'),
                'count'      => $grupo->count(),
            ])
            ->sortBy('porcentaje')
            ->values();

        $totalRetenidoCliente   = (float) $emitidas->sum('retencion_importe');
        $totalRetenidoProveedor = (float) $recibidas->sum('retencion_importe');
        $neto                   = $totalRetenidoProveedor - $totalRetenidoCliente;

        return view('exports.informes.retenciones-obra', [
            'empresa'                => $empresa,
            'obra'                   => $obra,
            'emitidas'               => $emitidas,
            'recibidas'              => $recibidas,
            'emitidasPorTipo'        => $emitidasPorTipo,
            'recibidasPorTipo'       => $recibidasPorTipo,
            'totalRetenidoCliente'   => $totalRetenidoCliente,
            'totalRetenidoProveedor' => $totalRetenidoProveedor,
            'neto'                   => $neto,
            'fechaInicio'            => $this->fechaInicio,
            'fechaFin'               => $this->fechaFin,
            'generadoEn'             => now()->format('d/m/Y H:i'),
        ]);
    }

    public function title(): string
    {
        return 'Retenciones obra';
    }
}
