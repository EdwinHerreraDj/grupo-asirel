<?php

namespace App\Livewire\Empresa\FacturasVentas;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\FacturaVenta;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;

class Index extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    /* =======================
        ESTADO UI
    ======================= */
    public bool $showFormulario = false;
    public ?int $facturaId = null;

    /* =======================
    FILTROS APLICADOS
======================= */
    public ?string $estado = null;
    public ?string $search = null;
    public ?string $codigo = null;
    public ?string $fechaDesde = null;
    public ?string $fechaHasta = null;

    /* =======================
    FILTROS TEMPORALES (UI)
======================= */
    public ?string $tmpEstado = null;
    public ?string $tmpSearch = null;
    public ?string $tmpCodigo = null;


    public ?string $tmpFechaDesde = null;
    public ?string $tmpFechaHasta = null;



    public function aplicarFiltros(): void
    {
        if ($this->tmpFechaDesde && $this->tmpFechaHasta) {
            if ($this->tmpFechaDesde > $this->tmpFechaHasta) {
                $this->dispatch('toast', type: 'error', text: 'La fecha desde no puede ser mayor que la fecha hasta');
                return;
            }
        }

        $this->estado = $this->tmpEstado;
        $this->search = $this->tmpSearch;
        $this->codigo = $this->tmpCodigo;
        $this->fechaDesde = $this->tmpFechaDesde;
        $this->fechaHasta = $this->tmpFechaHasta;

        $this->resetPage();
    }


    public function limpiarFiltros(): void
    {
        $this->reset([
            'estado',
            'search',
            'codigo',
            'tmpEstado',
            'tmpSearch',
            'tmpCodigo',
            'fechaDesde',
            'fechaHasta',
            'tmpFechaDesde',
            'tmpFechaHasta',
        ]);

        $this->resetPage();
    }



    /* =======================
        MODAL
    ======================= */
    public function nuevaFactura(): void
    {
        $this->facturaId = null;
        $this->showFormulario = true;
    }

    public function editarFactura(int $id): void
    {
        $factura = FacturaVenta::findOrFail($id);

        if ($factura->estado !== 'borrador') {
            abort(403);
        }

        $this->facturaId = $id;
        $this->showFormulario = true;
    }

    #[On('cerrarModalForm')]
    public function cerrarModalForm(): void
    {
        $this->reset('showFormulario', 'facturaId');
    }

    /* =======================
        GUARDAR (ÚNICO PUNTO)
    ======================= */
    #[On('guardarFactura')]
    public function guardarFactura(array $data)
    {
        DB::transaction(function () use ($data) {

            if ($this->facturaId) {

                $factura = FacturaVenta::lockForUpdate()->findOrFail($this->facturaId);

                if ($factura->estado !== 'borrador') {
                    abort(403);
                }

                $factura->update($data);
            } else {

                FacturaVenta::create(array_merge($data, [
                    'origen'         => 'manual',
                    'numero_factura' => null,
                    'estado'         => 'borrador',
                ]));
            }
        });

        return redirect()->route('empresa.facturas-ventas');
    }

    /* =======================
        LISTADO
    ======================= */
    public function render()
    {
        $query = FacturaVenta::query();

        if ($this->estado) {
            $query->where('estado', $this->estado);
        }

        if ($this->search !== '') {

            $search = trim($this->search);

            $query->where(function ($q) use ($search) {

                // Caso FV-1, FV-000123, etc.
                if (str_contains($search, '-')) {

                    [$serie, $numero] = explode('-', $search, 2);

                    $q->where('serie', $serie)
                        ->where('numero_factura', ltrim($numero, '0'));
                } else {
                    // Solo número o solo serie
                    $q->where('numero_factura', 'like', "%{$search}%")
                        ->orWhere('serie', 'like', "%{$search}%");
                }
            });
        }


        if ($this->codigo) {
            $query->where('codigo_certificacion', 'like', "%{$this->codigo}%");
        }

        // 🔹 FECHA DESDE
        if ($this->fechaDesde) {
            $query->whereDate('fecha_emision', '>=', $this->fechaDesde);
        }

        // 🔹 FECHA HASTA
        if ($this->fechaHasta) {
            $query->whereDate('fecha_emision', '<=', $this->fechaHasta);
        }

        return view('livewire.empresa.facturas-ventas.index', [
            'facturas' => $query
                ->orderByDesc('fecha_emision')
                ->paginate(10),
        ]);
    }
}
