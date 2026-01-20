<?php

namespace App\Livewire\Empresa\Certificaciones;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use App\Models\Certificacion;
use App\Models\FacturaVenta;
use App\Models\FacturaVentaDetalle;
use App\Models\FacturaSerie;
use Illuminate\Validation\ValidationException;
use App\Services\Facturas\FacturaPdfService;

class Facturar extends Component
{
    public int $obraId;

    public bool $showConfirmModal = false;

    public ?string $numeroCertificacionSeleccionada = null;
    public ?string $serieSeleccionada = null;

    public array $resumenFactura = [];
    public $series;
    public ?string $modalError = null;

    /* =========================
     * MOUNT
     * ========================= */

    public function mount(int $obraId): void
    {
        $this->obraId = $obraId;

        $this->series = FacturaSerie::where('activa', true)
            ->orderBy('serie')
            ->get();
    }

    /* =========================
     * CONFIRMAR FACTURACIÓN
     * ========================= */

    public function confirmarFacturacion(string $numero): void
    {
        // TOTAL capítulos del número
        $totalCapitulos = Certificacion::where('obra_id', $this->obraId)
            ->where('numero_certificacion', $numero)
            ->count();

        // Capítulos aceptados y pendientes de facturar
        $certs = Certificacion::where('obra_id', $this->obraId)
            ->where('numero_certificacion', $numero)
            ->where('estado_certificacion', 'aceptada')
            ->where('estado_factura', 'pendiente')
            ->with('cliente')
            ->get();

        if ($certs->isEmpty()) {
            $this->dispatch('toast', type: 'error', text: 'No hay capítulos aceptados para facturar.');
            return;
        }

        if ($certs->count() !== $totalCapitulos) {
            $this->dispatch(
                'toast',
                type: 'error',
                text: 'No se puede facturar: hay capítulos de esta certificación que aún no están aceptados.'
            );
            return;
        }

        // Cliente único
        $clienteId = $certs->first()->cliente_id;
        if ($certs->contains(fn($c) => $c->cliente_id !== $clienteId)) {
            $this->dispatch('toast', type: 'error', text: 'Las certificaciones no pertenecen al mismo cliente.');
            return;
        }

        // Resumen
        $this->resumenFactura = [
            'cliente'   => $certs->first()->cliente->nombre ?? '—',
            'capitulos' => $certs->count(),
            'base'      => $certs->sum('base_imponible'),
            'iva'       => $certs->sum('iva_importe'),
            'ret'       => $certs->sum('retencion_importe'),
            'total'     => $certs->sum('total'),
        ];

        $this->serieSeleccionada = null;
        $this->modalError = null;
        $this->numeroCertificacionSeleccionada = $numero;
        $this->showConfirmModal = true;
    }

    public function cancelarFacturacion(): void
    {
        $this->reset([
            'showConfirmModal',
            'numeroCertificacionSeleccionada',
            'serieSeleccionada',
            'resumenFactura',
        ]);

        $this->modalError = null;
    }

    /* =========================
     * EMITIR FACTURA
     * ========================= */

    public function emitirFactura()
    {
        if (!$this->numeroCertificacionSeleccionada || !$this->serieSeleccionada) {
            $this->modalError = 'Debes seleccionar una serie de facturación.';
            return;
        }

        try {
            $facturaId = DB::transaction(function () {

                // 🔒 TODOS los capítulos del número
                $totalCapitulos = Certificacion::where('obra_id', $this->obraId)
                    ->where('numero_certificacion', $this->numeroCertificacionSeleccionada)
                    ->lockForUpdate()
                    ->count();

                // 🔒 Solo aceptados
                $certs = Certificacion::where('obra_id', $this->obraId)
                    ->where('numero_certificacion', $this->numeroCertificacionSeleccionada)
                    ->where('estado_certificacion', 'aceptada')
                    ->where('estado_factura', 'pendiente')
                    ->with(['oficio', 'cliente'])
                    ->lockForUpdate()
                    ->get();

                if ($certs->isEmpty() || $certs->count() !== $totalCapitulos) {
                    throw ValidationException::withMessages([
                        'certificacion' => 'Existen capítulos no aceptados. No se puede facturar.',
                    ]);
                }

                // Cliente único
                $clienteId = $certs->first()->cliente_id;
                if ($certs->contains(fn($c) => $c->cliente_id !== $clienteId)) {
                    throw ValidationException::withMessages([
                        'cliente' => 'Las certificaciones no pertenecen al mismo cliente.',
                    ]);
                }

                // 🔒 Serie
                $serie = FacturaSerie::where('serie', $this->serieSeleccionada)
                    ->lockForUpdate()
                    ->firstOrFail();

                $numeroFactura = $serie->ultimo_numero + 1;
                $serie->update(['ultimo_numero' => $numeroFactura]);

                // Totales
                $baseTotal = $certs->sum('base_imponible');
                $ivaTotal  = $certs->sum('iva_importe');
                $retTotal  = $certs->sum('retencion_importe');
                $total     = $certs->sum('total');
                $ivaPorcentaje = $certs->first()->iva_porcentaje ?? 0;
                $retPorcentaje = $certs->first()->retencion_porcentaje ?? 0;

                // Validación fiscal
                if ($certs->pluck('iva_porcentaje')->unique()->count() > 1) {
                    $this->modalError = 'Las certificaciones no tienen el mismo IVA.';
                    return;
                }

                if ($certs->pluck('retencion_porcentaje')->unique()->count() > 1) {
                    $this->modalError = 'Las certificaciones no tienen la misma retención.';
                    return;
                }


                // Crear factura
                $factura = FacturaVenta::create([
                    'serie'          => $serie->serie,
                    'numero_factura' => $numeroFactura,
                    'estado'         => 'emitida',

                    'fecha_emision'  => now(),
                    'fecha_contable' => now(),

                    'origen'               => 'certificacion',
                    'codigo_certificacion' => $this->numeroCertificacionSeleccionada,
                    'cliente_id'           => $clienteId,
                    'obra_id'              => $this->obraId,

                    'base_imponible'       => $baseTotal,

                    'iva_porcentaje'       => $ivaPorcentaje,
                    'iva_importe'          => $ivaTotal,

                    'retencion_porcentaje' => $retPorcentaje,
                    'retencion_importe'    => $retTotal,

                    'total'                => $total,
                ]);


                // Líneas
                foreach ($certs as $cert) {
                    FacturaVentaDetalle::create([
                        'factura_venta_id' => $factura->id,
                        'certificacion_id' => $cert->id,

                        'concepto' => 'Certificación ' . $cert->numero_certificacion
                            . ' – ' . ($cert->oficio->nombre ?? 'Capítulo'),

                        'cantidad'        => 1,
                        'unidad'          => '1',
                        'precio_unitario' => $cert->base_imponible,
                        'importe_linea'   => $cert->base_imponible,
                    ]);
                }

                // 🔗 Marcar SOLO los aceptados
                Certificacion::where('obra_id', $this->obraId)
                    ->where('numero_certificacion', $this->numeroCertificacionSeleccionada)
                    ->where('estado_certificacion', 'aceptada')
                    ->where('estado_factura', 'pendiente')
                    ->update(['estado_factura' => 'facturada']);

                return $factura->id;
            });

            $factura = FacturaVenta::findOrFail($facturaId);

            // Generar PDF igual que en manual
            app(FacturaPdfService::class)->generar($factura);

            return redirect()->route('empresa.facturas-ventas.detalle', $facturaId);
        } catch (\Throwable $e) {

            report($e);

            $this->dispatch(
                'toast',
                type: 'error',
                text: 'Error al emitir la factura. Revisa los datos.'
            );

            $this->showConfirmModal = false;
        }
    }

    /* =========================
     * RENDER
     * ========================= */

    public function render()
    {
        $certificaciones = Certificacion::select(
            'numero_certificacion',
            'cliente_id',
            DB::raw('SUM(base_imponible) as base'),
            DB::raw('SUM(total) as total')
        )
            ->where('obra_id', $this->obraId)
            ->where('estado_certificacion', 'aceptada')
            ->where('estado_factura', 'pendiente')
            ->groupBy('numero_certificacion', 'cliente_id')
            ->with('cliente')
            ->orderBy('numero_certificacion', 'desc')
            ->get();

        return view('livewire.empresa.certificaciones.facturar', compact('certificaciones'));
    }
}
