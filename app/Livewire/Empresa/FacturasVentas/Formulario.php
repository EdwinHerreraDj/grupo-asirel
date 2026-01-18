<?php

namespace App\Livewire\Empresa\FacturasVentas;

use Livewire\Component;
use App\Models\FacturaVenta;
use App\Models\Cliente;
use App\Models\Obra;
use App\Models\FacturaSerie;


class Formulario extends Component
{
    /* =======================
        ESTADO
    ======================= */
    public ?FacturaVenta $factura = null;
    public ?int $facturaId = null;
    public bool $editable = true;

    /* =======================
        CAMPOS FACTURA (BORRADOR)
    ======================= */
    public string $serie = '';
    public string $fecha_emision = '';
    public string $fecha_contable = '';
    public ?string $vencimiento = null;

    public ?int $cliente_id = null;
    public ?int $obra_id = null;

    // Configuración fiscal (NO importes)
    public float $iva_porcentaje = 21;
    public float $retencion_porcentaje = 0;

    public ?string $observaciones = null;

    /* =======================
        SELECTS
    ======================= */
    public $clientes = [];
    public $obras = [];
    public $series = [];

    /* =======================
        MOUNT
    ======================= */
    public function mount(?int $facturaId = null): void
    {
        $this->facturaId = $facturaId;

        $this->clientes = Cliente::orderBy('nombre')->get();
        $this->obras    = Obra::orderBy('nombre')->get();
        $this->series   = FacturaSerie::where('activa', 1)->orderBy('serie')->get();

        $this->fecha_emision   = now()->toDateString();
        $this->fecha_contable = now()->toDateString();

        if ($facturaId) {
            $this->cargarFactura($facturaId);
        }
    }

    /* =======================
        CARGAR FACTURA
    ======================= */
    private function cargarFactura(int $id): void
    {
        $this->factura = FacturaVenta::findOrFail($id);

        $this->serie = $this->factura->serie;

        $this->fecha_emision = $this->factura->fecha_emision
            ? date('Y-m-d', strtotime($this->factura->fecha_emision))
            : null;

        $this->fecha_contable = $this->factura->fecha_contable
            ? date('Y-m-d', strtotime($this->factura->fecha_contable))
            : null;

        $this->vencimiento = $this->factura->vencimiento
            ? date('Y-m-d', strtotime($this->factura->vencimiento))
            : null;


        $this->cliente_id = $this->factura->cliente_id;
        $this->obra_id    = $this->factura->obra_id;

        $this->iva_porcentaje       = $this->factura->iva_porcentaje;
        $this->retencion_porcentaje = $this->factura->retencion_porcentaje;

        $this->observaciones = $this->factura->observaciones;

        $this->editable = $this->factura->estado === 'borrador';
    }


    /* =======================
        GUARDAR BORRADOR
    ======================= */
    public function guardar(): void
    {
        if (!$this->editable) {
            abort(403);
        }

        $this->validate([
            'serie'           => 'required|exists:factura_series,serie',
            'cliente_id'      => 'required|exists:clientes,id',
            'fecha_emision'   => 'required|date',
            'fecha_contable'  => 'required|date',
            'iva_porcentaje'  => 'required|numeric|min:0',
            'retencion_porcentaje' => 'required|numeric|min:0',
        ]);

        $payload = [
            'serie'                 => $this->serie,
            'numero_factura'        => null,
            'origen'                => 'manual',
            'estado'                => 'borrador',

            'fecha_emision'         => $this->fecha_emision,
            'fecha_contable'        => $this->fecha_contable,
            'vencimiento'           => $this->vencimiento,

            'cliente_id'            => $this->cliente_id,
            'obra_id'               => $this->obra_id,

            // Importes a cero (se calculan desde líneas)
            'base_imponible'        => 0,
            'iva_porcentaje'        => $this->iva_porcentaje,
            'iva_importe'           => 0,
            'retencion_porcentaje'  => $this->retencion_porcentaje,
            'retencion_importe'     => 0,
            'total'                 => 0,

            'observaciones'         => $this->observaciones,
        ];

        $this->dispatch('guardarFactura', $payload);
    }

    /* =======================
        RENDER
    ======================= */
    public function render()
    {
        return view('livewire.empresa.facturas-ventas.formulario');
    }
}
