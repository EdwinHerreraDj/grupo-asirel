<?php

namespace App\Livewire\Empresa\FacturasVentas;

use App\Models\FacturaVenta;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    // UI modal formulario
    public bool $showFormulario = false;
    public ?int $facturaId = null;

    // Filtros aplicados
    public ?string $estado = null;
    public ?string $search = null;
    public ?string $codigo = null;
    public ?string $fechaDesde = null;
    public ?string $fechaHasta = null;

    // Filtros temporales (UI)
    public ?string $tmpEstado = null;
    public ?string $tmpSearch = null;
    public ?string $tmpCodigo = null;
    public ?string $tmpFechaDesde = null;
    public ?string $tmpFechaHasta = null;

    // Modal acciones
    public bool $showAccionesModal = false;
    public ?int $facturaAccionesId = null;

    public function aplicarFiltros(): void
    {
        if ($this->tmpFechaDesde && $this->tmpFechaHasta
            && $this->tmpFechaDesde > $this->tmpFechaHasta) {
            $this->dispatch('notify', type: 'error', message: 'La fecha desde no puede ser mayor que la fecha hasta.');

            return;
        }

        $this->estado     = $this->tmpEstado;
        $this->search     = $this->tmpSearch;
        $this->codigo     = $this->tmpCodigo;
        $this->fechaDesde = $this->tmpFechaDesde;
        $this->fechaHasta = $this->tmpFechaHasta;

        $this->resetPage();
    }

    public function limpiarFiltros(): void
    {
        $this->reset([
            'estado', 'search', 'codigo', 'fechaDesde', 'fechaHasta',
            'tmpEstado', 'tmpSearch', 'tmpCodigo', 'tmpFechaDesde', 'tmpFechaHasta',
        ]);
        $this->resetPage();
    }

    // Modal formulario
    public function nuevaFactura(): void
    {
        $this->facturaId = null;
        $this->showFormulario = true;
    }

    public function editarFactura(int $id): void
    {
        $factura = FacturaVenta::findOrFail($id);
        if (! $factura->esEditable()) {
            $this->dispatch('notify', type: 'error', message: 'Solo se pueden editar facturas en borrador.');

            return;
        }

        $this->facturaId = $id;
        $this->showFormulario = true;
        $this->showAccionesModal = false;
    }

    #[On('cerrarModalForm')]
    public function cerrarModalForm(): void
    {
        $this->reset('showFormulario', 'facturaId');
    }

    #[On('factura-guardada')]
    public function onFacturaGuardada(): void
    {
        // Provoca re-render del listado
    }

    // Modal acciones
    public function abrirAcciones(int $id): void
    {
        $this->facturaAccionesId = $id;
        $this->showAccionesModal = true;
    }

    public function cerrarAcciones(): void
    {
        $this->reset('showAccionesModal', 'facturaAccionesId');
    }

    public function render()
    {
        $query = FacturaVenta::query()
            ->with(['cliente:id,nombre', 'obra:id,nombre'])
            ->withSum('pagos as total_pagado', 'importe');

        if ($this->estado) {
            $query->where('estado', $this->estado);
        }

        if ($this->search) {
            $search = trim($this->search);
            $query->where(function ($q) use ($search) {
                if (str_contains($search, '-')) {
                    [$serie, $numero] = explode('-', $search, 2);
                    $q->where('serie', $serie)
                        ->where('numero_factura', ltrim($numero, '0'));
                } else {
                    $q->where('numero_factura', 'like', "%{$search}%")
                        ->orWhere('serie', 'like', "%{$search}%");
                }
            });
        }

        if ($this->codigo) {
            $query->where('codigo_certificacion', 'like', "%{$this->codigo}%");
        }

        if ($this->fechaDesde) {
            $query->whereDate('fecha_emision', '>=', $this->fechaDesde);
        }

        if ($this->fechaHasta) {
            $query->whereDate('fecha_emision', '<=', $this->fechaHasta);
        }

        return view('livewire.empresa.facturas-ventas.index', [
            'facturas' => $query->orderByDesc('fecha_emision')->paginate(10),
            'facturaAcciones' => $this->facturaAccionesId
                ? FacturaVenta::with(['cliente:id,nombre'])->find($this->facturaAccionesId)
                : null,
            'estadosMeta' => FacturaVenta::ESTADOS_META,
        ]);
    }
}
