<?php

namespace App\Livewire\Empresa\FacturasVentas;

use Livewire\Component;
use App\Models\FacturaVenta;
use Illuminate\Support\Facades\DB;
use App\Models\FacturaVentaDetalle;
use App\Models\FacturaSerie;
use App\Models\FacturaVentaPago;


class Detalle extends Component
{
    public FacturaVenta $factura;
    public bool $editable = false;
    public bool $showModal = false;
    public bool $modoEdicion = false;

    public ?int $detalleId = null;

    public string $concepto = '';
    public ?string $unidad = null;
    public float $cantidad = 0;
    public float $precio_unitario = 0;

    public bool $showDeleteModal = false;
    public ?int $detalleAEliminarId = null;

    public bool $showEmitirModal = false;

    /* Gestion de cobros */
    public bool $showPagoModal = false;

    public $pago_fecha;
    public $pago_importe;
    public $pago_metodo;
    public $pago_observaciones;
    public $pago_tipo = 'normal';

    /* Anulacion de la factura */
    public bool $showAnularModal = false;
    public string $motivoAnulacion = '';




    protected function rules()
    {
        return [
            'concepto'        => ['required', 'string', 'max:255'],
            'unidad'          => ['nullable', 'string', 'max:50'],
            'cantidad'        => ['required', 'numeric', 'min:0.01'],
            'precio_unitario' => ['required', 'numeric', 'min:0'],
        ];
    }



    public function mount(FacturaVenta $factura)
    {
        $this->factura = $factura->load('detalles');
        $this->editable = $factura->estado === 'borrador';
    }

    public function abrirModalCrear()
    {
        $this->resetCamposLinea();
        $this->modoEdicion = false;
        $this->showModal = true;
    }

    public function abrirModalEditar(int $detalleId)
    {
        $detalle = FacturaVentaDetalle::where('factura_venta_id', $this->factura->id)
            ->findOrFail($detalleId);

        $this->detalleId = $detalle->id;
        $this->concepto = $detalle->concepto;
        $this->unidad = $detalle->unidad;
        $this->cantidad = $detalle->cantidad;
        $this->precio_unitario = $detalle->precio_unitario;

        $this->modoEdicion = true;
        $this->showModal = true;
    }

    public function cerrarModal()
    {
        $this->resetCamposLinea();
        $this->showModal = false;
    }

    public function confirmarEliminar(int $detalleId): void
    {
        if (!$this->editable) {
            abort(403);
        }

        $this->detalleAEliminarId = $detalleId;
        $this->showDeleteModal = true;
    }

    public function eliminarDetalle(): void
    {
        if (!$this->editable) {
            abort(403);
        }

        $detalle = FacturaVentaDetalle::where('factura_venta_id', $this->factura->id)
            ->findOrFail($this->detalleAEliminarId);

        $detalle->delete();

        $this->recalcularTotales();

        $this->showDeleteModal = false;
        $this->detalleAEliminarId = null;

        /* Mensaje de éxito o notificación  */
        $this->dispatch('toast', type: 'success', text: 'Línea eliminada correctamente');
    }




    private function resetCamposLinea(): void
    {
        $this->reset([
            'detalleId',
            'concepto',
            'unidad',
            'cantidad',
            'precio_unitario',
        ]);

        $this->cantidad = 0;
        $this->precio_unitario = 0;
    }

    public function guardarDetalle()
    {
        if (!$this->editable) {
            abort(403);
        }

        $this->validate();

        $importe = round($this->cantidad * $this->precio_unitario, 2);

        if ($this->modoEdicion) {

            $detalle = FacturaVentaDetalle::where('factura_venta_id', $this->factura->id)
                ->findOrFail($this->detalleId);

            $detalle->update([
                'concepto'        => $this->concepto,
                'unidad'          => $this->unidad,
                'cantidad'        => $this->cantidad,
                'precio_unitario' => $this->precio_unitario,
                'importe_linea'   => $importe,
            ]);
        } else {

            FacturaVentaDetalle::create([
                'factura_venta_id' => $this->factura->id,
                'concepto'         => $this->concepto,
                'unidad'           => $this->unidad,
                'cantidad'         => $this->cantidad,
                'precio_unitario'  => $this->precio_unitario,
                'importe_linea'    => $importe,
            ]);
        }

        // Recalcular totales factura
        $this->factura->load('detalles');
        $this->recalcularTotales();

        $this->cerrarModal();
        /* Mensaje de éxito o notificación  */
        $this->dispatch('toast', type: 'success', text: 'Línea guardada correctamente');
    }

    public function recalcularTotales(): void
    {
        DB::transaction(function () {

            // Base = suma de importes de línea (tu modelo guarda importe_linea ya calculado)
            $base = $this->factura->detalles()->sum('importe_linea');

            $ivaPorcentaje = (float) ($this->factura->iva_porcentaje ?? 0);
            $retencionPorcentaje = (float) ($this->factura->retencion_porcentaje ?? 0);

            $ivaImporte = round($base * ($ivaPorcentaje / 100), 2);
            $retencionImporte = round($base * ($retencionPorcentaje / 100), 2);

            // Total típico construcción: base + IVA - retención
            $total = round($base + $ivaImporte - $retencionImporte, 2);

            $this->factura->update([
                'base_imponible'     => $base,
                'iva_importe'        => $ivaImporte,
                'retencion_importe'  => $retencionImporte,
                'total'              => $total,
            ]);
        });

        // refrescar modelo + relación para que la vista se actualice bien
        $this->factura->refresh();
        $this->factura->load('detalles');
    }

    public function confirmarEmitir(): void
    {
        if (!$this->editable) {
            abort(403);
        }

        if ($this->factura->detalles()->count() === 0) {
            $this->dispatch('toast', type: 'error', text: 'La factura no tiene líneas');
            return;
        }

        $this->showEmitirModal = true;
    }

    public function emitirFactura(): void
    {
        if ($this->factura->estado !== 'borrador') {
            abort(403);
        }

        DB::transaction(function () {

            // 1️⃣ Recalcular totales finales (por seguridad)
            $this->recalcularTotales();

            // 2️⃣ Obtener serie
            $serie = FacturaSerie::where('serie', $this->factura->serie)
                ->lockForUpdate()
                ->firstOrFail();

            // 3️⃣ Consumir numeración
            $numero = $serie->ultimo_numero + 1;

            $serie->update([
                'ultimo_numero' => $numero,
            ]);

            // 4️⃣ Emitir factura
            $this->factura->update([
                'numero_factura' => $numero,
                'estado'         => 'emitida',
                'fecha_emision'  => now()->toDateString(),
            ]);
        });

        // Refrescar estado
        $this->factura->refresh();
        $this->editable = false;

        $this->showEmitirModal = false;

        $this->dispatch('toast', type: 'success', text: 'Factura emitida correctamente');
    }

    /* Gestion de cobros de la factura */
    public function abrirModalPago()
    {
        $this->reset([
            'pago_fecha',
            'pago_importe',
            'pago_metodo',
            'pago_observaciones',
            'pago_tipo',
        ]);


        $this->pago_fecha = now()->format('Y-m-d');
        $this->pago_tipo = 'normal';
        $this->showPagoModal = true;
    }

    public function cerrarModalPago()
    {
        $this->showPagoModal = false;
    }

    protected function rulesPago()
    {
        return [
            'pago_fecha'         => 'required|date',
            'pago_importe'       => 'required|numeric|not_in:0',
            'pago_tipo'          => 'required|in:normal,correccion',
            'pago_metodo'        => 'required|string|max:50',
            'pago_observaciones' => 'nullable|string|max:255',
        ];
    }


    public function guardarPago()
    {
        if (!in_array($this->factura->estado, ['emitida', 'enviada'])) {
            return;
        }

        $this->validate($this->rulesPago());

        if ($this->pago_tipo === 'normal') {

            if ($this->pago_importe < 0) {
                $this->addError('pago_importe', 'Un pago normal no puede ser negativo.');
                return;
            }

            if ($this->pago_importe > $this->factura->pendientePago()) {
                $this->addError('pago_importe', 'El importe supera el pendiente de la factura.');
                return;
            }
        } else { // corrección

            if ($this->pago_importe > 0) {
                $this->addError('pago_importe', 'Una corrección debe tener importe negativo.');
                return;
            }
        }

        FacturaVentaPago::create([
            'factura_venta_id' => $this->factura->id,
            'fecha_pago'       => $this->pago_fecha,
            'importe'          => $this->pago_importe,
            'metodo'           => $this->pago_metodo,
            'tipo'             => $this->pago_tipo,
            'observaciones'    => $this->pago_observaciones,
        ]);

        $this->factura->refresh();
        $this->factura->recalcularEstadoPorPagos();

        $this->showPagoModal = false;

        $this->dispatch('toast', type: 'success', text: 'Pago registrado correctamente');
    }

    /* Metodos para anular la factura */
    public function confirmarAnular()
    {
        if (! $this->factura->puedeAnular()) {
            return;
        }

        $this->motivoAnulacion = '';
        $this->showAnularModal = true;
    }

    public function cerrarAnularModal()
    {
        $this->showAnularModal = false;
    }

    public function anularFactura()
    {
        if (! $this->factura->puedeAnular()) {
            return;
        }

        $this->validate([
            'motivoAnulacion' => 'required|string|min:5|max:500',
        ]);

        $this->factura->anular($this->motivoAnulacion);

        $this->showAnularModal = false;

        $this->dispatch('toast', type: 'success', text: 'Factura anulada correctamente');
    }




    public function render()
    {
        return view('livewire.empresa.facturas-ventas.detalle');
    }
}
