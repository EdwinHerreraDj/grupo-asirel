<?php

namespace App\Livewire\Obras;

use App\Exports\FacturasRecibidasExport;
use App\Models\Empresa;
use App\Models\FacturaRecibida;
use App\Models\Obra;
use App\Models\Proveedor;
use App\Services\FacturaRecibidaService;
use Barryvdh\DomPDF\Facade\Pdf;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;
use RuntimeException;

class FacturasRecibidas extends Component
{
    use WithFileUploads;
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    public ?Obra $obra = null;

    // Modo global: cuando no se monta con una obra específica,
    // mostramos un selector para que el usuario cambie de obra.
    public bool $globalMode = false;
    public ?int $selectedObraId = null;

    // Listas cargadas
    public $proveedores = [];
    public $oficios = [];
    public $obrasList = [];

    // Campos del formulario
    public ?int $proveedor_id = null;
    public ?int $oficio_id = null;
    public string $tipo_coste = 'material';
    public ?string $numero_factura = null;
    public ?string $concepto = null;
    public ?string $fecha_factura = null;
    public ?string $fecha_contable = null;
    public ?string $vencimiento = null;
    public ?string $tipo_pago = null;
    public string $estado = 'pendiente_vencimiento';
    public $adjunto = null;

    // Fiscal
    public $base_imponible = null;
    public $iva_porcentaje = 21;
    public $retencion_porcentaje = 0;

    // Control modal formulario
    public bool $showForm = false;
    public ?int $facturaId = null;
    public bool $modoEdicion = false;

    // Eliminar
    public ?int $facturaAEliminar = null;

    // Cambio de estado cr\u00edtico (pagada/impagada) con confirmaci\u00f3n
    public ?int $facturaCambioEstadoId = null;
    public ?string $estadoPendiente = null;

    // Filtros
    public string $search = '';
    public string $filtroProveedor = '';
    public string $filtroOficio = '';
    public string $filtroEstado = '';
    public string $filtroTipoCoste = '';
    public bool $activarFiltros = false;

    // Modal informe
    public bool $showInformeModal = false;
    public string $informeProveedor = '';
    public string $informeOficio = '';
    public string $informeEstado = '';
    public string $informeTipoCoste = '';
    public string $informeFechaDesde = '';
    public string $informeFechaHasta = '';

    protected function rules(): array
    {
        return [
            'proveedor_id'         => 'required|exists:proveedores,id',
            'oficio_id'            => 'required|exists:obra_gasto_categorias,id',
            'tipo_coste'           => 'required|in:material,mano_obra',
            'numero_factura'       => 'nullable|string|max:255',
            'concepto'             => 'nullable|string|max:500',
            'base_imponible'       => 'required|numeric|min:0',
            'iva_porcentaje'       => 'required|numeric|min:0',
            'retencion_porcentaje' => 'required|numeric|min:0',
            'fecha_factura'        => 'required|date',
            'fecha_contable'       => 'nullable|date',
            'vencimiento'          => 'nullable|date',
            'tipo_pago'            => 'nullable|in:transferencia,pronto_pago,confirming,pagare,contado',
            'estado'               => 'required|in:pendiente_emision_doc_pago,pendiente_vencimiento,devuelta,pagada,impagada',
            'adjunto'              => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ];
    }

    public function mount(?Obra $obra = null): void
    {
        $this->proveedores = Proveedor::where('activo', 1)->orderBy('nombre')->get();

        if ($obra && $obra->exists) {
            // Modo per-obra (route binding)
            $this->obra = $obra;
            $this->oficios = $this->obra->categoriasGasto()->orderBy('nombre')->get();
            $this->globalMode = false;
        } else {
            // Modo global: se monta sin obra y aparece el selector
            $this->globalMode = true;
            $this->obrasList = Obra::orderBy('nombre')->get(['id', 'nombre', 'estado']);
            $this->oficios = collect();
        }
    }

    public function updatedSelectedObraId($value): void
    {
        if (! $value) {
            $this->obra = null;
            $this->oficios = collect();
            $this->resetPage();
            $this->showForm = false;

            return;
        }

        $this->obra = Obra::find($value);
        $this->oficios = $this->obra
            ? $this->obra->categoriasGasto()->orderBy('nombre')->get()
            : collect();

        // Limpiar filtros que dependen de oficios/proveedores específicos de obra
        $this->reset(['filtroOficio']);
        $this->resetPage();
        $this->showForm = false;
    }

    // -------------------------
    // FORMULARIO CREAR / EDITAR
    // -------------------------

    public function abrirFormulario(): void
    {
        if (! $this->obra) {
            $this->dispatch('notify', type: 'error', message: 'Selecciona una obra primero.');

            return;
        }

        $this->resetForm();
        $this->facturaId = null;
        $this->modoEdicion = false;
        $this->showForm = true;
    }

    public function cerrarFormulario(): void
    {
        $this->showForm = false;
        $this->resetForm();
    }

    public function editarFactura(int $id): void
    {
        $factura = FacturaRecibida::where('obra_id', $this->obra->id)->findOrFail($id);

        $this->facturaId = $factura->id;
        $this->modoEdicion = true;

        $this->proveedor_id = $factura->proveedor_id;
        $this->oficio_id = $factura->oficio_id;
        $this->tipo_coste = $factura->tipo_coste;
        $this->numero_factura = $factura->numero_factura;
        $this->concepto = $factura->concepto;
        $this->base_imponible = $factura->base_imponible;
        $this->iva_porcentaje = $factura->iva_porcentaje;
        $this->retencion_porcentaje = $factura->retencion_porcentaje;
        $this->fecha_factura = $factura->fecha_factura?->format('Y-m-d');
        $this->fecha_contable = $factura->fecha_contable?->format('Y-m-d');
        $this->vencimiento = $factura->vencimiento?->format('Y-m-d');
        $this->tipo_pago = $factura->tipo_pago;
        $this->estado = $factura->estado;
        $this->adjunto = null;

        $this->showForm = true;
    }

    public function guardar(FacturaRecibidaService $service): void
    {
        $data = $this->validate();

        $payload = collect($data)->except('adjunto')->all();

        try {
            if ($this->modoEdicion && $this->facturaId) {
                $factura = FacturaRecibida::where('obra_id', $this->obra->id)
                    ->findOrFail($this->facturaId);

                $service->actualizar($factura, $payload, $this->adjunto ?: null);
                $mensaje = 'Factura actualizada correctamente.';
            } else {
                $service->crear($this->obra, $payload, $this->adjunto ?: null);
                $mensaje = 'Factura registrada correctamente.';
            }
        } catch (\Throwable $e) {
            report($e);
            $this->dispatch('notify', type: 'error', message: 'Error al guardar la factura.');

            return;
        }

        $this->cerrarFormulario();
        $this->dispatch('notify', type: 'success', message: $mensaje);
    }

    protected function resetForm(): void
    {
        $this->reset([
            'proveedor_id',
            'oficio_id',
            'tipo_coste',
            'numero_factura',
            'concepto',
            'base_imponible',
            'fecha_factura',
            'fecha_contable',
            'vencimiento',
            'tipo_pago',
            'adjunto',
        ]);

        $this->tipo_coste = 'material';
        $this->estado = 'pendiente_vencimiento';
        $this->iva_porcentaje = 21;
        $this->retencion_porcentaje = 0;
    }

    // -------------------------
    // ELIMINAR
    // -------------------------

    public function confirmarEliminar(int $id): void
    {
        $this->facturaAEliminar = $id;
    }

    public function cancelarEliminar(): void
    {
        $this->facturaAEliminar = null;
    }

    public function eliminarFactura(FacturaRecibidaService $service): void
    {
        if (! $this->facturaAEliminar) {
            return;
        }

        $factura = FacturaRecibida::where('obra_id', $this->obra->id)
            ->findOrFail($this->facturaAEliminar);

        $service->eliminar($factura);

        $this->facturaAEliminar = null;
        $this->resetPage();
        $this->dispatch('notify', type: 'success', message: 'Factura eliminada correctamente.');
    }

    // -------------------------
    // CAMBIO DE ESTADO
    // -------------------------

    public function intentarCambiarEstado(
        int $id,
        string $nuevoEstado,
        FacturaRecibidaService $service,
    ): void {
        $factura = FacturaRecibida::where('obra_id', $this->obra->id)->findOrFail($id);

        if ($factura->esEstadoCritico($nuevoEstado)) {
            $this->facturaCambioEstadoId = $id;
            $this->estadoPendiente = $nuevoEstado;

            return;
        }

        $service->cambiarEstado($factura, $nuevoEstado);
        $this->dispatch('notify', type: 'success', message: 'Estado actualizado.');
    }

    public function confirmarCambioEstado(FacturaRecibidaService $service): void
    {
        if (! $this->facturaCambioEstadoId || ! $this->estadoPendiente) {
            return;
        }

        $factura = FacturaRecibida::where('obra_id', $this->obra->id)
            ->findOrFail($this->facturaCambioEstadoId);

        try {
            $service->cambiarEstado($factura, $this->estadoPendiente);
        } catch (RuntimeException | \InvalidArgumentException $e) {
            $this->dispatch('notify', type: 'error', message: $e->getMessage());

            return;
        }

        $this->cancelarCambioEstado();
        $this->dispatch('notify', type: 'success', message: 'Estado actualizado.');
    }

    public function cancelarCambioEstado(): void
    {
        $this->facturaCambioEstadoId = null;
        $this->estadoPendiente = null;
    }

    public function cambiarTipoPago(
        int $id,
        ?string $nuevoTipo,
        FacturaRecibidaService $service,
    ): void {
        $factura = FacturaRecibida::where('obra_id', $this->obra->id)->findOrFail($id);
        $service->cambiarTipoPago($factura, $nuevoTipo ?: null);
        $this->dispatch('notify', type: 'success', message: 'Tipo de pago actualizado.');
    }

    // -------------------------
    // FILTROS
    // -------------------------

    public function aplicarFiltros(): void
    {
        $this->activarFiltros = true;
        $this->resetPage();
    }

    public function limpiarFiltros(): void
    {
        $this->reset([
            'search',
            'filtroProveedor',
            'filtroOficio',
            'filtroEstado',
            'filtroTipoCoste',
            'activarFiltros',
        ]);
        $this->resetPage();
    }

    public function updatingSearch(): void { $this->resetPage(); }
    public function updatingFiltroProveedor(): void { $this->resetPage(); }
    public function updatingFiltroOficio(): void { $this->resetPage(); }
    public function updatingFiltroEstado(): void { $this->resetPage(); }
    public function updatingFiltroTipoCoste(): void { $this->resetPage(); }

    // -------------------------
    // INFORME
    // -------------------------

    public function abrirModalInforme(): void
    {
        if (! $this->obra) {
            $this->dispatch('notify', type: 'error', message: 'Selecciona una obra primero.');

            return;
        }

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

    public function cerrarModalInforme(): void
    {
        $this->showInformeModal = false;
    }

    public function generarInformePDF()
    {
        $facturas = $this->getFacturasInformeQuery()->get();
        $totales = $this->calcularTotales($facturas);

        $pdf = Pdf::loadView('pdf.facturas-recibidas', [
            'facturas' => $facturas,
            'totales'  => $totales,
            'obra'     => $this->obra,
            'empresa'  => Empresa::first(),
        ])->setPaper('a4', 'landscape');

        return response()->streamDownload(
            fn () => print($pdf->output()),
            'informe_facturas_' . now()->format('Ymd_His') . '.pdf'
        );
    }

    public function exportarExcel()
    {
        $facturas = $this->getFacturasInformeQuery()->get();

        return Excel::download(
            new FacturasRecibidasExport(
                $facturas,
                $this->calcularTotales($facturas),
                $this->obra,
                Empresa::first(),
            ),
            'informe_facturas_' . now()->format('Ymd_His') . '.xlsx'
        );
    }

    private function calcularTotales($facturas): array
    {
        return [
            'base'      => $facturas->sum('base_imponible'),
            'iva'       => $facturas->sum('iva_importe'),
            'retencion' => $facturas->sum('retencion_importe'),
            'total'     => $facturas->sum('total'),
        ];
    }

    // -------------------------
    // QUERIES
    // -------------------------

    private function getFacturasQuery()
    {
        if (! $this->obra) {
            return FacturaRecibida::query()->whereRaw('0 = 1');
        }

        $query = FacturaRecibida::with(['proveedor', 'oficio'])
            ->where('obra_id', $this->obra->id);

        if ($this->activarFiltros) {
            if ($this->search !== '') {
                $query->where(function ($q) {
                    $q->where('concepto', 'like', "%{$this->search}%")
                        ->orWhere('numero_factura', 'like', "%{$this->search}%");
                });
            }

            if ($this->filtroProveedor !== '') {
                $query->where('proveedor_id', $this->filtroProveedor);
            }

            if ($this->filtroOficio !== '') {
                $query->where('oficio_id', $this->filtroOficio);
            }

            if ($this->filtroEstado !== '') {
                $query->where('estado', $this->filtroEstado);
            }

            if ($this->filtroTipoCoste !== '') {
                $query->where('tipo_coste', $this->filtroTipoCoste);
            }
        }

        return $query;
    }

    private function getFacturasInformeQuery()
    {
        if (! $this->obra) {
            return FacturaRecibida::query()->whereRaw('0 = 1');
        }

        $query = FacturaRecibida::with(['proveedor', 'oficio'])
            ->where('obra_id', $this->obra->id);

        if ($this->informeProveedor !== '') {
            $query->where('proveedor_id', $this->informeProveedor);
        }
        if ($this->informeOficio !== '') {
            $query->where('oficio_id', $this->informeOficio);
        }
        if ($this->informeEstado !== '') {
            $query->where('estado', $this->informeEstado);
        }
        if ($this->informeTipoCoste !== '') {
            $query->where('tipo_coste', $this->informeTipoCoste);
        }
        if ($this->informeFechaDesde !== '') {
            $query->whereDate('fecha_factura', '>=', $this->informeFechaDesde);
        }
        if ($this->informeFechaHasta !== '') {
            $query->whereDate('fecha_factura', '<=', $this->informeFechaHasta);
        }

        return $query;
    }

    // -------------------------
    // RENDER
    // -------------------------

    public function render()
    {
        $facturas = $this->getFacturasQuery()
            ->orderBy('fecha_factura', 'desc')
            ->paginate(10);

        if ($this->obra) {
            $obraId = $this->obra->id;
            $resumen = [
                'total'      => FacturaRecibida::where('obra_id', $obraId)->sum('total'),
                'pagadas'    => FacturaRecibida::where('obra_id', $obraId)
                    ->where('estado', 'pagada')->sum('total'),
                'pendientes' => FacturaRecibida::where('obra_id', $obraId)
                    ->whereIn('estado', ['pendiente_emision_doc_pago', 'pendiente_vencimiento'])
                    ->sum('total'),
                'impagadas'  => FacturaRecibida::where('obra_id', $obraId)
                    ->where('estado', 'impagada')->sum('total'),
            ];
        } else {
            $resumen = ['total' => 0, 'pagadas' => 0, 'pendientes' => 0, 'impagadas' => 0];
        }

        $facturaCambioEstado = $this->facturaCambioEstadoId
            ? FacturaRecibida::find($this->facturaCambioEstadoId)
            : null;

        return view('livewire.obras.facturas-recibidas', [
            'facturas'            => $facturas,
            'resumen'             => $resumen,
            'estados'             => FacturaRecibida::ESTADOS,
            'facturaCambioEstado' => $facturaCambioEstado,
        ]);
    }
}
