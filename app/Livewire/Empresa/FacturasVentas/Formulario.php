<?php

namespace App\Livewire\Empresa\FacturasVentas;

use App\Models\Cliente;
use App\Models\FacturaSerie;
use App\Models\FacturaVenta;
use App\Models\Obra;
use App\Services\FacturaVentaService;
use Livewire\Component;
use Livewire\WithFileUploads;
use RuntimeException;

class Formulario extends Component
{
    use WithFileUploads;

    public ?FacturaVenta $factura = null;
    public ?int $facturaId = null;
    public bool $editable = true;

    public string $serie = '';
    public string $fecha_emision = '';
    public string $fecha_contable = '';
    public ?string $vencimiento = null;

    public ?int $cliente_id = null;
    public ?int $obra_id = null;

    public float $iva_porcentaje = 21;
    public float $retencion_porcentaje = 0;

    public ?string $observaciones = null;

    public $adjunto = null;
    public ?string $adjuntoActual = null;

    public $clientes = [];
    public $obras = [];
    public $series = [];

    public function mount(?int $facturaId = null): void
    {
        $this->facturaId = $facturaId;

        $this->clientes = Cliente::orderBy('nombre')->get();
        $this->obras    = Obra::orderBy('nombre')->get();
        $this->series   = FacturaSerie::where('activa', 1)->orderBy('serie')->get();

        $this->fecha_emision  = now()->toDateString();
        $this->fecha_contable = now()->toDateString();

        if ($facturaId) {
            $this->cargarFactura($facturaId);
        }
    }

    private function cargarFactura(int $id): void
    {
        $this->factura = FacturaVenta::findOrFail($id);

        $this->serie          = $this->factura->serie;
        $this->fecha_emision  = $this->factura->fecha_emision?->format('Y-m-d') ?? now()->toDateString();
        $this->fecha_contable = $this->factura->fecha_contable?->format('Y-m-d') ?? now()->toDateString();
        $this->vencimiento    = $this->factura->vencimiento?->format('Y-m-d');

        $this->cliente_id           = $this->factura->cliente_id;
        $this->obra_id              = $this->factura->obra_id;
        $this->iva_porcentaje       = $this->factura->iva_porcentaje;
        $this->retencion_porcentaje = $this->factura->retencion_porcentaje;
        $this->observaciones        = $this->factura->observaciones;
        $this->adjuntoActual        = $this->factura->adjunto;

        $this->editable = $this->factura->esEditable();
    }

    protected function rules(): array
    {
        return [
            'serie'                => 'required|exists:factura_series,serie',
            'cliente_id'           => 'required|exists:clientes,id',
            'obra_id'              => 'nullable|exists:obras,id',
            'fecha_emision'        => 'required|date',
            'fecha_contable'       => 'required|date',
            'vencimiento'          => 'nullable|date',
            'iva_porcentaje'       => 'required|numeric|min:0',
            'retencion_porcentaje' => 'required|numeric|min:0',
            'observaciones'        => 'nullable|string|max:1000',
            'adjunto'              => 'nullable|file|mimes:pdf|max:5120',
        ];
    }

    public function guardar(FacturaVentaService $service): void
    {
        if (! $this->editable) {
            abort(403);
        }

        $data = $this->validate();

        $payload = [
            'serie'                 => $data['serie'],
            'fecha_emision'         => $data['fecha_emision'],
            'fecha_contable'        => $data['fecha_contable'],
            'vencimiento'           => $data['vencimiento'] ?? null,
            'cliente_id'            => $data['cliente_id'],
            'obra_id'               => $data['obra_id'] ?? null,
            'iva_porcentaje'        => (float) $data['iva_porcentaje'],
            'retencion_porcentaje'  => (float) $data['retencion_porcentaje'],
            'observaciones'         => $data['observaciones'] ?? null,
        ];

        try {
            if ($this->facturaId) {
                $factura = FacturaVenta::findOrFail($this->facturaId);
                $service->actualizarBorrador($factura, $payload, $this->adjunto ?: null);
                $mensaje = 'Borrador actualizado correctamente.';
            } else {
                // Importes a 0; se calculan al añadir líneas.
                $payload = array_merge($payload, [
                    'base_imponible'    => 0,
                    'iva_importe'       => 0,
                    'retencion_importe' => 0,
                    'total'             => 0,
                ]);
                $service->crearBorrador($payload, $this->adjunto ?: null);
                $mensaje = 'Factura creada en borrador.';
            }
        } catch (RuntimeException $e) {
            $this->dispatch('notify', type: 'error', message: $e->getMessage());

            return;
        } catch (\Throwable $e) {
            report($e);
            $this->dispatch('notify', type: 'error', message: 'Error al guardar la factura.');

            return;
        }

        $this->dispatch('factura-guardada');
        $this->dispatch('cerrarModalForm');
        $this->dispatch('notify', type: 'success', message: $mensaje);
    }

    public function render()
    {
        return view('livewire.empresa.facturas-ventas.formulario');
    }
}
