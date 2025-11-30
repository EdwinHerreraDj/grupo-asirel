<?php

namespace App\Livewire\Obras;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Proveedor;
use App\Models\FacturaRecibida;
use App\Models\ObraGastoCategoria;
use Illuminate\Support\Facades\Storage;
use Livewire\WithPagination;


class FacturasRecibidas extends Component
{
    use WithFileUploads;
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    public $obra;

    // Listas cargadas
    public $proveedores = [];
    public $oficios = [];

    // Campos del formulario
    public $proveedor_id;
    public $oficio_id;
    public $tipo_coste = 'material';
    public $numero_factura;
    public $concepto;
    public $importe;
    public $fecha_factura;
    public $fecha_contable;
    public $vencimiento;
    public $tipo_pago;
    public $estado = 'pendiente_de_vencimiento';
    public $adjunto;

    // Atributos para eliminar factura
    public $facturaAEliminar = null;
    public $confirmarEliminacion = false;



    // Filtros
    public $search = '';
    public $filtroProveedor = '';
    public $filtroOficio = '';
    public $filtroEstado = '';
    public $filtroTipoCoste = '';
    public $activarFiltros = false;

    // Control modal
    public $showForm = false;




    protected $rules = [
        'proveedor_id'   => 'required|exists:proveedores,id',
        'oficio_id'      => 'required|exists:obra_gasto_categorias,id',
        'tipo_coste'     => 'required|in:material,mano_obra',
        'numero_factura' => 'nullable|string|max:255',
        'concepto'       => 'nullable|string|max:255',
        'importe'        => 'required|numeric|min:0',
        'fecha_factura'  => 'required|date',
        'fecha_contable' => 'nullable|date',
        'vencimiento'    => 'nullable|date',
        'tipo_pago'      => 'nullable|in:transferencia,pronto_pago,confirming,pagare,contado',
        'estado'         => 'required|in:pendiente_de_vencimiento,pagada,pendiente_de_emision,aplazada,impagada',
        'adjunto'        => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
    ];

    public function mount($obra)
    {
        $this->obra = $obra;

        // Cargar proveedores
        $this->proveedores = Proveedor::where('activo', 1)
            ->orderBy('nombre')
            ->get();


        // Cargar oficios de esta obra
        $this->oficios = $this->obra->categoriasGasto()->orderBy('nombre')->get();
    }

    public function updated($property)
    {
        $this->validateOnly($property);
    }

    public function abrirFormulario()
    {
        $this->resetForm();
        $this->showForm = true;
    }

    public function resetForm()
    {
        $this->reset([
            'proveedor_id',
            'oficio_id',
            'tipo_coste',
            'numero_factura',
            'concepto',
            'importe',
            'fecha_factura',
            'fecha_contable',
            'vencimiento',
            'tipo_pago',
            'estado',
            'adjunto',
        ]);

        $this->tipo_coste = 'material';
        $this->estado = 'pendiente_de_vencimiento';
    }

    public function guardar()
    {
        $this->validate();

        // Guardar adjunto si existe
        $rutaAdjunto = null;
        if ($this->adjunto) {
            $rutaAdjunto = $this->adjunto->store('facturas/recibidas', 'public');
        }

        FacturaRecibida::create([
            'obra_id'        => $this->obra->id,
            'proveedor_id'   => $this->proveedor_id,
            'oficio_id'      => $this->oficio_id,
            'tipo_coste'     => $this->tipo_coste,
            'numero_factura' => $this->numero_factura,
            'concepto'       => $this->concepto,
            'importe'        => $this->importe,
            'fecha_factura'  => $this->fecha_factura,
            'fecha_contable' => $this->fecha_contable,
            'vencimiento'    => $this->vencimiento,
            'tipo_pago'      => $this->tipo_pago,
            'estado'         => $this->estado,
            'adjunto'        => $rutaAdjunto,
        ]);

        $this->showForm = false;

        $this->dispatch('toast', type: 'success', text: 'Factura registrada correctamente.');
    }

    public function confirmarEliminar($id)
    {
        $this->facturaAEliminar = $id;
        $this->confirmarEliminacion = true;
    }


    public function eliminarFactura($id)
    {
        $factura = FacturaRecibida::where('obra_id', $this->obra->id)
            ->findOrFail($id);

        if ($factura->adjunto && Storage::disk('public')->exists($factura->adjunto)) {
            Storage::disk('public')->delete($factura->adjunto);
        }

        $factura->delete();

        $this->confirmarEliminacion = false;
        $this->facturaAEliminar = null;

        $this->dispatch('toast', type: 'success', text: 'Factura eliminada correctamente.');

        $this->resetPage();
    }


    // Actualizar los campos de tipo de pago y estado 
    public function actualizarEstado($id, $nuevoEstado)
    {
        $factura = FacturaRecibida::where('obra_id', $this->obra->id)->findOrFail($id);
        $factura->estado = $nuevoEstado;
        $factura->save();

        $this->dispatch('toast', type: 'success', text: 'Estado actualizado correctamente.');
    }

    public function actualizarTipoPago($id, $nuevoTipo)
    {
        $factura = FacturaRecibida::where('obra_id', $this->obra->id)->findOrFail($id);
        $factura->tipo_pago = $nuevoTipo;
        $factura->save();

        $this->dispatch('toast', type: 'success', text: 'Tipo de pago actualizado.');
    }




    // Filtros
    public function aplicarFiltros()
    {
        $this->activarFiltros = true;
        $this->resetPage();
    }

    public function limpiarFiltros()
    {
        $this->reset([
            'search',
            'filtroProveedor',
            'filtroOficio',
            'filtroEstado',
            'filtroTipoCoste',
            'activarFiltros'
        ]);

        $this->resetPage();
    }


    public function updatingSearch()
    {
        $this->resetPage();
    }
    public function updatingFiltroProveedor()
    {
        $this->resetPage();
    }
    public function updatingFiltroOficio()
    {
        $this->resetPage();
    }
    public function updatingFiltroEstado()
    {
        $this->resetPage();
    }
    public function updatingFiltroTipoCoste()
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = FacturaRecibida::with(['proveedor', 'oficio'])
            ->where('obra_id', $this->obra->id);

        if ($this->activarFiltros) {

            // Buscador
            if ($this->search !== '') {
                $query->where(function ($q) {
                    $q->where('concepto', 'like', '%' . $this->search . '%')
                        ->orWhere('numero_factura', 'like', '%' . $this->search . '%')
                        ->orWhere('importe', 'like', '%' . $this->search . '%');
                });
            }

            // Proveedor
            if ($this->filtroProveedor !== '') {
                $query->where('proveedor_id', $this->filtroProveedor);
            }

            // Oficio
            if ($this->filtroOficio !== '') {
                $query->where('oficio_id', $this->filtroOficio);
            }

            // Estado
            if ($this->filtroEstado !== '') {
                $query->where('estado', $this->filtroEstado);
            }

            // Tipo de coste
            if ($this->filtroTipoCoste !== '') {
                $query->where('tipo_coste', $this->filtroTipoCoste);
            }
        }

        $facturas = $query->orderBy('fecha_factura', 'desc')->paginate(10);

        return view('livewire.obras.facturas-recibidas', [
            'facturas' => $facturas,
        ]);
    }
}
