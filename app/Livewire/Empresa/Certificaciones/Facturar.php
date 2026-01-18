<?php

namespace App\Livewire\Empresa\Certificaciones;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use App\Models\Certificacion;
use App\Models\FacturaVenta;
use App\Models\FacturaVentaDetalle;
use App\Models\FacturaSerie;
use Illuminate\Validation\ValidationException;

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
     * CONFIRMAR (ABRE MODAL)
     * ========================= */

    public function confirmarFacturacion(string $numero): void
    {
        $certs = Certificacion::where('obra_id', $this->obraId)
            ->where('numero_certificacion', $numero)
            ->where('estado_certificacion', 'aceptada')
            ->where('estado_factura', 'pendiente')
            ->with('cliente')
            ->get();

        if ($certs->isEmpty()) {
            $this->dispatch('toast', type: 'error', text: 'No hay certificaciones válidas para facturar.');
            return;
        }

        $clienteId = $certs->first()->cliente_id;
        if ($certs->contains(fn($c) => $c->cliente_id !== $clienteId)) {
            $this->dispatch('toast', type: 'error', text: 'Las certificaciones no pertenecen al mismo cliente.');
            return;
        }

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

                // 🔒 Bloquear certificaciones
                $certs = Certificacion::where('obra_id', $this->obraId)
                    ->where('numero_certificacion', $this->numeroCertificacionSeleccionada)
                    ->where('estado_certificacion', 'aceptada')
                    ->where('estado_factura', 'pendiente')
                    ->with(['oficio', 'cliente'])
                    ->lockForUpdate()
                    ->get();

                if ($certs->isEmpty()) {
                    throw ValidationException::withMessages([
                        'certificacion' => 'No hay certificaciones válidas para facturar.',
                    ]);
                }

                // 🔒 Cliente único
                $clienteId = $certs->first()->cliente_id;
                if ($certs->contains(fn($c) => $c->cliente_id !== $clienteId)) {
                    throw ValidationException::withMessages([
                        'cliente' => 'Las certificaciones no pertenecen al mismo cliente.',
                    ]);
                }

                // 🔒 Consumir contador de serie
                $serie = FacturaSerie::where('serie', $this->serieSeleccionada)
                    ->lockForUpdate()
                    ->first();

                if (!$serie) {
                    throw ValidationException::withMessages([
                        'serie' => 'La serie seleccionada no es válida.',
                    ]);
                }

                $numeroFactura = $serie->ultimo_numero + 1;
                $serie->update(['ultimo_numero' => $numeroFactura]);

                // 🔢 Totales
                $baseTotal = $certs->sum('base_imponible');
                $ivaTotal  = $certs->sum('iva_importe');
                $retTotal  = $certs->sum('retencion_importe');
                $total     = $certs->sum('total');

                // 🧾 Crear factura (YA NUMERADA)
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

                    'base_imponible'    => $baseTotal,
                    'iva_importe'       => $ivaTotal,
                    'retencion_importe' => $retTotal,
                    'total'             => $total,
                ]);

                // 📄 Líneas (1 × concepto cerrado)
                foreach ($certs as $cert) {
                    FacturaVentaDetalle::create([
                        'factura_venta_id' => $factura->id,
                        'certificacion_id' => $cert->id,

                        'concepto'        => 'Certificación ' . $cert->numero_certificacion
                            . ' – ' . ($cert->oficio->nombre ?? 'Capítulo'),

                        'cantidad'        => 1,
                        'unidad'          => '1',
                        'precio_unitario' => $cert->base_imponible,
                        'importe_linea'   => $cert->base_imponible,
                    ]);
                }

                // 🔗 Marcar certificaciones
                Certificacion::where('numero_certificacion', $this->numeroCertificacionSeleccionada)
                    ->update(['estado_factura' => 'facturada']);

                return $factura->id;
            });

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
