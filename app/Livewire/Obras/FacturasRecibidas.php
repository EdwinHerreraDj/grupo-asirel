<?php

namespace App\Livewire\Obras;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Proveedor;
use App\Models\FacturaRecibida;
use App\Services\FacturaRecibidaCalculator;
use App\Models\Obra;
use App\Models\Empresa;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\FacturasRecibidasExport;
use Illuminate\Support\Facades\Storage;
use Livewire\WithPagination;


class FacturasRecibidas extends Component
{
    use WithFileUploads;
    use WithPagination;
    protected $paginationTheme = 'tailwind';

    public Obra $obra;

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


    // FISCAL
    public $base_imponible;
    public $iva_porcentaje = 21;
    public $retencion_porcentaje = 0;


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
    public $facturaId = null;
    public $modoEdicion = false;
    public $showInformeModal = false;


    // Filtros del informe (independientes de la tabla)
    public $informeProveedor = '';
    public $informeOficio = '';
    public $informeEstado = '';
    public $informeTipoCoste = '';
    public $informeFechaDesde = '';
    public $informeFechaHasta = '';





    protected $rules = [
        'proveedor_id'   => 'required|exists:proveedores,id',
        'oficio_id'      => 'required|exists:obra_gasto_categorias,id',
        'tipo_coste'     => 'required|in:material,mano_obra',
        'numero_factura' => 'nullable|string|max:255',
        'concepto'       => 'nullable|string|max:255',

        'base_imponible' => 'required|numeric|min:0',
        'iva_porcentaje' => 'required|numeric|min:0',
        'retencion_porcentaje' => 'required|numeric|min:0',

        'fecha_factura'  => 'required|date',
        'fecha_contable' => 'nullable|date',
        'vencimiento'    => 'nullable|date',
        'tipo_pago'      => 'nullable|in:transferencia,pronto_pago,confirming,pagare,contado',

        'estado' => 'required|in:pendiente_emision_doc_pago,pendiente_vencimiento,devuelta,pagada,impagada',

        'adjunto' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
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

        $this->facturaId = null;
        $this->modoEdicion = false;

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

        // Cálculos fiscales
        $ivaImporte = ($this->base_imponible * $this->iva_porcentaje) / 100;
        $retencionImporte = ($this->base_imponible * $this->retencion_porcentaje) / 100;
        $total = $this->base_imponible + $ivaImporte - $retencionImporte;

        // Subir adjunto si hay uno nuevo
        $rutaAdjunto = null;
        if ($this->adjunto) {
            $rutaAdjunto = $this->adjunto->store('facturas/recibidas', 'public');
        }

        if ($this->modoEdicion && $this->facturaId) {

            $factura = FacturaRecibida::findOrFail($this->facturaId);

            $factura->update([
                'proveedor_id'          => $this->proveedor_id,
                'oficio_id'             => $this->oficio_id,
                'tipo_coste'            => $this->tipo_coste,
                'numero_factura'        => $this->numero_factura,
                'concepto'              => $this->concepto,

                'base_imponible'        => $this->base_imponible,
                'iva_porcentaje'        => $this->iva_porcentaje,
                'iva_importe'           => $ivaImporte,
                'retencion_porcentaje'  => $this->retencion_porcentaje,
                'retencion_importe'     => $retencionImporte,
                'total'                 => $total,

                'fecha_factura'         => $this->fecha_factura,
                'fecha_contable'        => $this->fecha_contable,
                'vencimiento'           => $this->vencimiento,
                'tipo_pago'             => $this->tipo_pago,
                'estado'                => $this->estado,
                'adjunto'               => $rutaAdjunto ?? $factura->adjunto,
            ]);

            $mensaje = 'Factura actualizada correctamente.';
        } else {

            FacturaRecibida::create([
                'obra_id'               => $this->obra->id,
                'proveedor_id'          => $this->proveedor_id,
                'oficio_id'             => $this->oficio_id,
                'tipo_coste'            => $this->tipo_coste,
                'numero_factura'        => $this->numero_factura,
                'concepto'              => $this->concepto,

                'importe'               => $this->base_imponible, // compatibilidad
                'base_imponible'        => $this->base_imponible,
                'iva_porcentaje'        => $this->iva_porcentaje,
                'iva_importe'           => $ivaImporte,
                'retencion_porcentaje'  => $this->retencion_porcentaje,
                'retencion_importe'     => $retencionImporte,
                'total'                 => $total,

                'fecha_factura'         => $this->fecha_factura,
                'fecha_contable'        => $this->fecha_contable,
                'vencimiento'           => $this->vencimiento,
                'tipo_pago'             => $this->tipo_pago,
                'estado'                => $this->estado,
                'adjunto'               => $rutaAdjunto,
            ]);

            $mensaje = 'Factura registrada correctamente.';
        }

        $this->showForm = false;
        $this->resetForm();

        $this->dispatch('toast', type: 'success', text: $mensaje);
    }


    public function editarFactura($id)
    {
        $factura = FacturaRecibida::where('obra_id', $this->obra->id)
            ->findOrFail($id);

        $this->facturaId = $factura->id;
        $this->modoEdicion = true;

        // Cargar datos en el formulario
        $this->proveedor_id          = $factura->proveedor_id;
        $this->oficio_id             = $factura->oficio_id;
        $this->tipo_coste            = $factura->tipo_coste;
        $this->numero_factura        = $factura->numero_factura;
        $this->concepto              = $factura->concepto;

        $this->base_imponible        = $factura->base_imponible;
        $this->iva_porcentaje        = $factura->iva_porcentaje;
        $this->retencion_porcentaje  = $factura->retencion_porcentaje;

        $this->fecha_factura         = $factura->fecha_factura?->format('Y-m-d');
        $this->fecha_contable        = $factura->fecha_contable?->format('Y-m-d');
        $this->vencimiento           = $factura->vencimiento?->format('Y-m-d');

        $this->tipo_pago             = $factura->tipo_pago;
        $this->estado                = $factura->estado;

        // No cargues adjunto aquí (Livewire no lo permite)
        $this->adjunto = null;

        $this->showForm = true;
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

    // Modal para informe
    public function abrirModalInforme()
    {
        $this->reset([
            'informeProveedor',
            'informeOficio',
            'informeEstado',
            'informeTipoCoste',
            'informeFechaDesde',
            'informeFechaHasta',
        ]);

        $this->showInformeModal = true;
    }

    public function cerrarModalInforme()
    {
        $this->showInformeModal = false;
    }



    // Exportancion en Excel y PDF
    private function getFacturasQuery()
    {
        $query = FacturaRecibida::with(['proveedor', 'oficio'])
            ->where('obra_id', $this->obra->id);

        if ($this->activarFiltros) {

            if ($this->search !== '') {
                $query->where(function ($q) {
                    $q->where('concepto', 'like', "%{$this->search}%")
                        ->orWhere('numero_factura', 'like', "%{$this->search}%");
                });
            }

            if ($this->filtroProveedor) {
                $query->where('proveedor_id', $this->filtroProveedor);
            }

            if ($this->filtroOficio) {
                $query->where('oficio_id', $this->filtroOficio);
            }

            if ($this->filtroEstado) {
                $query->where('estado', $this->filtroEstado);
            }

            if ($this->filtroTipoCoste) {
                $query->where('tipo_coste', $this->filtroTipoCoste);
            }
        }

        return $query;
    }

    private function getFacturasInformeQuery()
    {
        $query = FacturaRecibida::with(['proveedor', 'oficio'])
            ->where('obra_id', $this->obra->id);

        if ($this->informeProveedor) {
            $query->where('proveedor_id', $this->informeProveedor);
        }

        if ($this->informeOficio) {
            $query->where('oficio_id', $this->informeOficio);
        }

        if ($this->informeEstado) {
            $query->where('estado', $this->informeEstado);
        }

        if ($this->informeTipoCoste) {
            $query->where('tipo_coste', $this->informeTipoCoste);
        }

        if ($this->informeFechaDesde) {
            $query->whereDate('fecha_factura', '>=', $this->informeFechaDesde);
        }

        if ($this->informeFechaHasta) {
            $query->whereDate('fecha_factura', '<=', $this->informeFechaHasta);
        }

        return $query;
    }


    public function generarInformePDF()
    {
        $facturas = $this->getFacturasInformeQuery()->get();

        $totales = [
            'base'      => $facturas->sum('base_imponible'),
            'iva'       => $facturas->sum('iva_importe'),
            'retencion' => $facturas->sum('retencion_importe'),
            'total'     => $facturas->sum('total'),
        ];

        $obra = $this->obra;
        $empresa = Empresa::first();

        $pdf = Pdf::loadView(
            'pdf.facturas-recibidas',
            compact('facturas', 'totales', 'obra', 'empresa')
        )->setPaper('a4', 'landscape');

        return response()->streamDownload(
            fn() => print($pdf->output()),
            'informe_facturas_' . now()->format('Ymd_His') . '.pdf'
        );
    }

    public function exportarExcel()
    {
        $facturas = $this->getFacturasInformeQuery()->get();

        $totales = [
            'base'      => $facturas->sum('base_imponible'),
            'iva'       => $facturas->sum('iva_importe'),
            'retencion' => $facturas->sum('retencion_importe'),
            'total'     => $facturas->sum('total'),
        ];

        return Excel::download(
            new FacturasRecibidasExport(
                $facturas,
                $totales,
                $this->obra,
                \App\Models\Empresa::first()
            ),
            'informe_facturas_' . now()->format('Ymd_His') . '.xlsx'
        );
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
