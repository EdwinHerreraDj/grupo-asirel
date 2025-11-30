<?php

namespace App\Livewire\Empresa\Certificaciones;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Certificacion;
use Illuminate\Support\Facades\Storage;
use App\Models\ObraGastoCategoria;

class Index extends Component
{
    use WithPagination;

    public $obraId;

    public $showModal = false;
    public $showDeleteModal = false;
    public $certificacionAEliminar = null;

    // Filtros

    // PENDING (usuario escribe)
    public $pendingOficio = '';
    public $pendingTipo = '';
    public $pendingFechaDesde = '';
    public $pendingFechaHasta = '';
    public $pendingSearch = '';

    // FILTROS REALES (se aplican solo al pulsar el botón)
    public $filtroOficio = '';
    public $filtroTipo = '';
    public $fechaDesde = '';
    public $fechaHasta = '';
    public $search = '';


    protected $paginationTheme = 'tailwind';

    protected $listeners = [
        'cerrarModal' => 'cerrarModal',
    ];

    public function mount($obraId)
    {
        $this->obraId = $obraId;
    }

    public function abrirModal()
    {
        $this->showModal = true;
    }

    public function cerrarModal()
    {
        $this->showModal = false;
        $this->dispatch('$refresh');
    }

    public function confirmarEliminar($id)
    {
        $this->certificacionAEliminar = $id;
        $this->showDeleteModal = true;
    }

    public function eliminar()
    {
        $cert = Certificacion::find($this->certificacionAEliminar);

        if ($cert) {
            if ($cert->adjunto_url && Storage::disk('public')->exists($cert->adjunto_url)) {
                Storage::disk('public')->delete($cert->adjunto_url);
            }
            $cert->delete();
        }

        $this->showDeleteModal = false;
        $this->certificacionAEliminar = null;

        $this->dispatch('toast', type: 'success', text: 'Certificación eliminada correctamente.');
    }

    public function aplicarFiltros()
    {
        $this->filtroOficio = $this->pendingOficio;
        $this->filtroTipo = $this->pendingTipo;
        $this->fechaDesde = $this->pendingFechaDesde;
        $this->fechaHasta = $this->pendingFechaHasta;
        $this->search = $this->pendingSearch;

        $this->resetPage();
    }

    public function limpiarFiltros()
    {
        $this->pendingOficio = '';
        $this->pendingTipo = '';
        $this->pendingFechaDesde = '';
        $this->pendingFechaHasta = '';
        $this->pendingSearch = '';

        $this->filtroOficio = '';
        $this->filtroTipo = '';
        $this->fechaDesde = '';
        $this->fechaHasta = '';
        $this->search = '';

        $this->resetPage();
    }


    public function render()
    {
        $query = Certificacion::where('obra_id', $this->obraId)
            ->with('oficio');

        // BUSCADOR GLOBAL
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('numero_certificacion', 'like', "%{$this->search}%")
                    ->orWhere('especificacion', 'like', "%{$this->search}%")
                    ->orWhere('tipo_documento', 'like', "%{$this->search}%");
            });
        }

        // FILTRO OFICIO
        if ($this->filtroOficio) {
            $query->where('obra_gasto_categoria_id', $this->filtroOficio);
        }

        // FILTRO TIPO DOCUMENTO
        if ($this->filtroTipo) {
            $query->where('tipo_documento', $this->filtroTipo);
        }

        // FECHAS
        if ($this->fechaDesde) {
            $query->whereDate('fecha_ingreso', '>=', $this->fechaDesde);
        }

        if ($this->fechaHasta) {
            $query->whereDate('fecha_ingreso', '<=', $this->fechaHasta);
        }

        return view('livewire.empresa.certificaciones.index', [
            'certificaciones' => $query->orderBy('fecha_ingreso', 'desc')->paginate(10),

            'oficios' => ObraGastoCategoria::where('obra_id', $this->obraId)
                ->orderBy('nombre')
                ->get(),
        ]);
    }
}
