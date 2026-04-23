<?php

namespace App\Livewire\Empresa\FacturasVentas;

use App\Models\FacturaVenta;
use App\Models\FacturaVentaDetalle;
use App\Models\FacturaVentaPago;
use App\Services\FacturaVentaService;
use Livewire\Component;
use RuntimeException;

class Detalle extends Component
{
    public FacturaVenta $factura;
    public bool $editable = false;

    // Modal línea
    public bool $showLineaModal = false;
    public bool $modoEdicionLinea = false;
    public ?int $detalleId = null;
    public string $concepto = '';
    public ?string $unidad = null;
    public float $cantidad = 0;
    public float $precio_unitario = 0;

    // Modal eliminar línea
    public ?int $detalleAEliminarId = null;

    // Modal emitir
    public bool $showEmitirModal = false;

    // Modal pago (crear o editar)
    public bool $showPagoModal = false;
    public ?int $pagoId = null;
    public bool $modoEdicionPago = false;
    public ?string $pago_fecha = null;
    public $pago_importe = 0;
    public ?string $pago_metodo = null;
    public ?string $pago_observaciones = null;
    public string $pago_tipo = 'normal';

    // Modal eliminar pago
    public ?int $pagoAEliminarId = null;

    // Modal anular
    public bool $showAnularModal = false;
    public string $motivoAnulacion = '';

    protected function rulesLinea(): array
    {
        return [
            'concepto'        => 'required|string|max:255',
            'unidad'          => 'nullable|string|max:50',
            'cantidad'        => 'required|numeric|min:0.01',
            'precio_unitario' => 'required|numeric|min:0',
        ];
    }

    protected function rulesPago(): array
    {
        return [
            'pago_fecha'         => 'required|date',
            'pago_importe'       => 'required|numeric|not_in:0',
            'pago_tipo'          => 'required|in:normal,correccion',
            'pago_metodo'        => 'required|string|max:50',
            'pago_observaciones' => 'nullable|string|max:255',
        ];
    }

    public function mount(FacturaVenta $factura): void
    {
        $this->factura = $factura->load(['detalles', 'pagos', 'cliente', 'obra']);
        $this->editable = $factura->esEditable();
    }

    // -------------------------
    // LÍNEAS
    // -------------------------

    public function abrirLineaNueva(): void
    {
        $this->resetLinea();
        $this->modoEdicionLinea = false;
        $this->showLineaModal = true;
    }

    public function abrirLineaEditar(int $detalleId): void
    {
        $detalle = FacturaVentaDetalle::where('factura_venta_id', $this->factura->id)
            ->findOrFail($detalleId);

        $this->detalleId = $detalle->id;
        $this->concepto = $detalle->concepto;
        $this->unidad = $detalle->unidad;
        $this->cantidad = (float) $detalle->cantidad;
        $this->precio_unitario = (float) $detalle->precio_unitario;

        $this->modoEdicionLinea = true;
        $this->showLineaModal = true;
    }

    public function cerrarLineaModal(): void
    {
        $this->resetLinea();
        $this->showLineaModal = false;
    }

    public function guardarLinea(FacturaVentaService $service): void
    {
        $data = $this->validate($this->rulesLinea());

        try {
            if ($this->modoEdicionLinea && $this->detalleId) {
                $detalle = FacturaVentaDetalle::where('factura_venta_id', $this->factura->id)
                    ->findOrFail($this->detalleId);
                $service->actualizarLinea($this->factura, $detalle, $data);
                $mensaje = 'Línea actualizada.';
            } else {
                $service->agregarLinea($this->factura, $data);
                $mensaje = 'Línea añadida.';
            }
        } catch (RuntimeException $e) {
            $this->dispatch('notify', type: 'error', message: $e->getMessage());

            return;
        }

        $this->factura->refresh()->load(['detalles', 'pagos']);
        $this->cerrarLineaModal();
        $this->dispatch('notify', type: 'success', message: $mensaje);
    }

    public function confirmarEliminarLinea(int $detalleId): void
    {
        $this->detalleAEliminarId = $detalleId;
    }

    public function cancelarEliminarLinea(): void
    {
        $this->detalleAEliminarId = null;
    }

    public function eliminarLinea(FacturaVentaService $service): void
    {
        if (! $this->detalleAEliminarId) {
            return;
        }

        $detalle = FacturaVentaDetalle::where('factura_venta_id', $this->factura->id)
            ->findOrFail($this->detalleAEliminarId);

        try {
            $service->eliminarLinea($this->factura, $detalle);
        } catch (RuntimeException $e) {
            $this->dispatch('notify', type: 'error', message: $e->getMessage());

            return;
        }

        $this->factura->refresh()->load(['detalles', 'pagos']);
        $this->detalleAEliminarId = null;
        $this->dispatch('notify', type: 'success', message: 'Línea eliminada.');
    }

    private function resetLinea(): void
    {
        $this->reset(['detalleId', 'concepto', 'unidad', 'cantidad', 'precio_unitario']);
        $this->cantidad = 0;
        $this->precio_unitario = 0;
    }

    // -------------------------
    // EMISIÓN
    // -------------------------

    public function confirmarEmitir(): void
    {
        if (! $this->factura->puedeEmitirse()) {
            $this->dispatch('notify', type: 'error', message: 'La factura no se puede emitir.');

            return;
        }

        $this->showEmitirModal = true;
    }

    public function cancelarEmitir(): void
    {
        $this->showEmitirModal = false;
    }

    public function emitirFactura(FacturaVentaService $service): void
    {
        try {
            $service->emitir($this->factura);
        } catch (RuntimeException $e) {
            $this->dispatch('notify', type: 'error', message: $e->getMessage());

            return;
        } catch (\Throwable $e) {
            report($e);
            $this->dispatch('notify', type: 'error', message: 'Error al emitir la factura.');

            return;
        }

        $this->factura->refresh()->load(['detalles', 'pagos']);
        $this->editable = false;
        $this->showEmitirModal = false;
        $this->dispatch('notify', type: 'success', message: 'Factura emitida correctamente.');
    }

    // -------------------------
    // PAGOS
    // -------------------------

    public function abrirPagoNuevo(): void
    {
        $this->resetPago();
        $this->pago_fecha = now()->format('Y-m-d');
        $this->modoEdicionPago = false;
        $this->showPagoModal = true;
    }

    public function abrirPagoEditar(int $pagoId): void
    {
        $pago = FacturaVentaPago::where('factura_venta_id', $this->factura->id)
            ->findOrFail($pagoId);

        $this->pagoId = $pago->id;
        $this->pago_fecha = $pago->fecha_pago?->format('Y-m-d');
        $this->pago_importe = (float) $pago->importe;
        $this->pago_metodo = $pago->metodo;
        $this->pago_tipo = $pago->tipo;
        $this->pago_observaciones = $pago->observaciones;

        $this->modoEdicionPago = true;
        $this->showPagoModal = true;
    }

    public function cerrarPagoModal(): void
    {
        $this->resetPago();
        $this->showPagoModal = false;
    }

    public function guardarPago(FacturaVentaService $service): void
    {
        $data = $this->validate($this->rulesPago());
        $importe = (float) $data['pago_importe'];

        if ($data['pago_tipo'] === 'normal' && $importe < 0) {
            $this->addError('pago_importe', 'Un pago normal no puede ser negativo.');

            return;
        }

        if ($data['pago_tipo'] === 'correccion' && $importe > 0) {
            $this->addError('pago_importe', 'Una corrección debe tener importe negativo.');

            return;
        }

        $payload = [
            'fecha_pago'    => $data['pago_fecha'],
            'importe'       => $importe,
            'metodo'        => $data['pago_metodo'],
            'tipo'          => $data['pago_tipo'],
            'observaciones' => $data['pago_observaciones'] ?? null,
        ];

        try {
            if ($this->modoEdicionPago && $this->pagoId) {
                $pago = FacturaVentaPago::where('factura_venta_id', $this->factura->id)
                    ->findOrFail($this->pagoId);
                $service->actualizarPago($pago, $payload);
                $mensaje = 'Pago actualizado.';
            } else {
                $service->registrarPago($this->factura, $payload);
                $mensaje = 'Pago registrado.';
            }
        } catch (RuntimeException $e) {
            $this->dispatch('notify', type: 'error', message: $e->getMessage());

            return;
        }

        $this->factura->refresh()->load(['detalles', 'pagos']);
        $this->cerrarPagoModal();
        $this->dispatch('notify', type: 'success', message: $mensaje);
    }

    public function confirmarEliminarPago(int $pagoId): void
    {
        $this->pagoAEliminarId = $pagoId;
    }

    public function cancelarEliminarPago(): void
    {
        $this->pagoAEliminarId = null;
    }

    public function eliminarPago(FacturaVentaService $service): void
    {
        if (! $this->pagoAEliminarId) {
            return;
        }

        $pago = FacturaVentaPago::where('factura_venta_id', $this->factura->id)
            ->findOrFail($this->pagoAEliminarId);

        try {
            $service->eliminarPago($pago);
        } catch (RuntimeException $e) {
            $this->dispatch('notify', type: 'error', message: $e->getMessage());

            return;
        }

        $this->factura->refresh()->load(['detalles', 'pagos']);
        $this->pagoAEliminarId = null;
        $this->dispatch('notify', type: 'success', message: 'Pago eliminado.');
    }

    private function resetPago(): void
    {
        $this->reset(['pagoId', 'pago_fecha', 'pago_importe', 'pago_metodo', 'pago_observaciones']);
        $this->pago_tipo = 'normal';
        $this->pago_importe = 0;
    }

    // -------------------------
    // ANULAR
    // -------------------------

    public function confirmarAnular(): void
    {
        if (! $this->factura->puedeAnular()) {
            $this->dispatch('notify', type: 'error', message: 'La factura no se puede anular.');

            return;
        }

        $this->motivoAnulacion = '';
        $this->showAnularModal = true;
    }

    public function cerrarAnularModal(): void
    {
        $this->showAnularModal = false;
    }

    public function anularFactura(FacturaVentaService $service): void
    {
        $this->validate([
            'motivoAnulacion' => 'required|string|min:5|max:500',
        ]);

        try {
            $service->anular($this->factura, $this->motivoAnulacion);
        } catch (RuntimeException $e) {
            $this->dispatch('notify', type: 'error', message: $e->getMessage());

            return;
        }

        $this->factura->refresh()->load(['detalles', 'pagos']);
        $this->showAnularModal = false;
        $this->dispatch('notify', type: 'success', message: 'Factura anulada.');
    }

    public function render()
    {
        return view('livewire.empresa.facturas-ventas.detalle');
    }
}
