<?php

namespace App\Livewire\Empresa\Certificaciones;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\ObraGastoCategoria;
use Livewire\WithFileUploads;
use App\Models\Certificacion;
use App\Models\Cliente;
use Illuminate\Support\Facades\Storage;


class Index extends Component
{
    use WithFileUploads;
    use WithPagination;

    protected $paginationTheme = 'tailwind';

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

    // PENDING
    public $pendingCliente = '';
    public $pendingEstadoCertificacion = '';

    // FILTROS APLICADOS
    public $filtroCliente = '';
    public $estadoCertificacion = '';

    //Modal para las acciones
    public bool $modalAcciones = false;
    public ?int $certificacionActiva = null;
    public ?string $numeroCertificacionActiva = null;
    public ?string $estadoFacturaActiva = null;
    public ?int $facturaVentaIdActiva = null;


    // MODO FORMULARIO
    public bool $modoNuevoCapitulo = false;

    // Datos base clonados
    public ?int $certBaseId = null;
    public ?string $numero_certificacion = null;
    public ?int $cliente_id = null;
    public ?int $obra_gasto_categoria_id = null;

    public $fecha_contable;
    public $fecha_vencimiento;
    public $iva_porcentaje;
    public $retencion_porcentaje;

    /* Varibles para informes */
    public bool $showInformeModal = false;
    public ?string $numeroCertificacionInforme = null;

    public array $capitulosInforme = [];
    public array $capitulosSeleccionadosInforme = [];
    public float $totalInforme = 0;
    public ?string $urlInformePdf = null;
    public bool $mostrarComparativa = false;





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
        $this->modalAcciones = false;
        $this->certificacionActiva = null;
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
        $this->filtroCliente = $this->pendingCliente;
        $this->estadoCertificacion = $this->pendingEstadoCertificacion;

        $this->fechaDesde = $this->pendingFechaDesde;
        $this->fechaHasta = $this->pendingFechaHasta;
        $this->search = $this->pendingSearch;

        $this->resetPage();
    }


    public function limpiarFiltros()
    {
        $this->pendingOficio = '';
        $this->pendingCliente = '';
        $this->pendingEstadoCertificacion = '';
        $this->pendingFechaDesde = '';
        $this->pendingFechaHasta = '';
        $this->pendingSearch = '';

        $this->filtroOficio = '';
        $this->filtroCliente = '';
        $this->estadoCertificacion = '';
        $this->fechaDesde = '';
        $this->fechaHasta = '';
        $this->search = '';


        $this->resetPage();
    }

    public function verCertificacionDetalles($certificacionId)
    {
        return redirect()->route('empresa.certificaciones.show', ['certificacion' => $certificacionId]);
    }

    public function abrirAcciones(Certificacion $cert)
    {
        $this->certificacionActiva = $cert->id;
        $this->numeroCertificacionActiva = $cert->numero_certificacion;
        $this->estadoFacturaActiva = $cert->estado_factura;
        $this->facturaVentaIdActiva = $cert->factura_venta_id;
        $this->modalAcciones = true;
    }

    public function cerrarAcciones(): void
    {
        $this->modalAcciones = false;
        $this->certificacionActiva = null;
        $this->numeroCertificacionActiva = null;
    }


    /* =========================
   COMPARATIVA MENSUAL
========================= */

    public function abrirComparativa(): void
    {
        $this->mostrarComparativa = true;
    }

    public function cerrarComparativa(): void
    {
        $this->mostrarComparativa = false;
    }

    /* Mostrar comparativa mensual  */

    /* Implementación para crear un nuevo capitulo */
    public function nuevoCapitulo(int $certificacionId): void
    {
        $cert = Certificacion::findOrFail($certificacionId);

        if ($cert->estado_factura === 'facturada') {
            $this->dispatch('toast', type: 'error', text: 'Esta certificación ya está facturada.');
            return;
        }

        // Guardamos referencia
        $this->certBaseId = $cert->id;

        // Clonamos datos (NO oficio)
        $this->numero_certificacion = $cert->numero_certificacion;
        $this->cliente_id = $cert->cliente_id;
        $this->obra_gasto_categoria_id = null;

        $this->fecha_contable = $cert->fecha_contable;
        $this->fecha_vencimiento = $cert->fecha_vencimiento;
        $this->iva_porcentaje = $cert->iva_porcentaje;
        $this->retencion_porcentaje = $cert->retencion_porcentaje;

        $this->modoNuevoCapitulo = true;
        $this->modalAcciones = false;
    }

    public function guardarNuevoCapitulo()
    {
        $this->validate([
            'obra_gasto_categoria_id' => 'required|exists:obra_gasto_categorias,id',
        ]);

        $certBase = Certificacion::findOrFail($this->certBaseId);

        $existe = Certificacion::where('numero_certificacion', $this->numero_certificacion)
            ->where('obra_gasto_categoria_id', $this->obra_gasto_categoria_id)
            ->exists();

        if ($existe) {
            $this->dispatch('toast', type: 'error', text: 'Ya existe un capítulo con este oficio en la certificación.');
            return;
        }


        Certificacion::create([
            'obra_id'              => $certBase->obra_id,
            'cliente_id'           => $this->cliente_id,
            'numero_certificacion' => $this->numero_certificacion,

            'fecha_ingreso'        => now(),
            'fecha_contable'       => $this->fecha_contable,
            'fecha_vencimiento'    => $this->fecha_vencimiento,

            'obra_gasto_categoria_id' => $this->obra_gasto_categoria_id,

            'iva_porcentaje'       => $this->iva_porcentaje,
            'retencion_porcentaje' => $this->retencion_porcentaje,

            'estado_certificacion' => 'pendiente',
            'estado_factura'       => 'pendiente',

            'base_imponible'       => 0,
            'iva_importe'          => 0,
            'retencion_importe'    => 0,
            'total'                => 0,
        ]);

        $this->resetFormularioCapitulo();

        $this->dispatch('toast', type: 'success', text: 'Capítulo creado correctamente.');
    }

    public function resetFormularioCapitulo(): void
    {
        $this->modoNuevoCapitulo = false;
        $this->certBaseId = null;

        $this->numero_certificacion = null;
        $this->cliente_id = null;
        $this->obra_gasto_categoria_id = null;

        $this->fecha_contable = null;
        $this->fecha_vencimiento = null;
        $this->iva_porcentaje = null;
        $this->retencion_porcentaje = null;
    }

    /* Metodos para generar los informes de certificación */
    public function abrirInforme(string $numeroCertificacion): void
    {
        $this->reset([
            'capitulosInforme',
            'capitulosSeleccionadosInforme',
            'totalInforme',
        ]);

        $this->numeroCertificacionInforme = $numeroCertificacion;

        $certs = Certificacion::with('oficio')
            ->where('obra_id', $this->obraId)
            ->where('numero_certificacion', $numeroCertificacion)
            ->get();

        $this->capitulosInforme = $certs->map(fn($c) => [
            'id'     => $c->id,
            'oficio' => $c->oficio->nombre ?? 'Capítulo',
            'total'  => (float) $c->total,
        ])->toArray();

        $this->showInformeModal = true;
    }

    public function toggleCapituloInforme(int $id): void
    {
        if (in_array($id, $this->capitulosSeleccionadosInforme)) {
            $this->capitulosSeleccionadosInforme = array_values(
                array_diff($this->capitulosSeleccionadosInforme, [$id])
            );
        } else {
            $this->capitulosSeleccionadosInforme[] = $id;
        }

        $this->recalcularTotalInforme();
    }

    public function seleccionarTodosInforme(): void
    {
        $this->capitulosSeleccionadosInforme = array_column($this->capitulosInforme, 'id');
        $this->recalcularTotalInforme();
    }

    public function limpiarInforme(): void
    {
        $this->capitulosSeleccionadosInforme = [];
        $this->totalInforme = 0;
    }

    protected function recalcularTotalInforme(): void
    {
        $this->totalInforme = collect($this->capitulosInforme)
            ->whereIn('id', $this->capitulosSeleccionadosInforme)
            ->sum('total');
    }

    public function generarInformePDF()
    {
        if (empty($this->capitulosSeleccionadosInforme)) {
            $this->dispatch('toast', type: 'error', text: 'Selecciona al menos un capítulo.');
            return;
        }

        $certs = Certificacion::with(['oficio', 'detalles', 'cliente'])
            ->whereIn('id', $this->capitulosSeleccionadosInforme)
            ->get();

        if ($certs->isEmpty()) {
            $this->dispatch('toast', type: 'error', text: 'No hay datos para generar el informe.');
            return;
        }

        // Validación fuerte: una sola obra
        if ($certs->pluck('obra_id')->unique()->count() !== 1) {
            $this->dispatch('toast', type: 'error', text: 'Los capítulos no pertenecen a la misma obra.');
            return;
        }

        $obra = \App\Models\Obra::findOrFail($certs->first()->obra_id);
        $empresa = \App\Models\Empresa::first();
        $cliente = $certs->first()->cliente; // si por tu lógica siempre es el mismo cliente
        $fecha = now()->format('d/m/Y');
        $numero_certificacion = $this->numeroCertificacionInforme;

        // Construir estructura EXACTA para tu Blade (arrays con descripcion/cantidad/precio/total)
        $capitulos = $certs->groupBy('obra_gasto_categoria_id')->map(function ($grupo) {

            $lineas = $grupo->flatMap->detalles->map(function ($d) {
                return [
                    'descripcion' => $d->concepto,
                    'cantidad'    => (float) $d->cantidad,
                    'precio'      => (float) $d->precio_unitario,
                    'total'       => (float) $d->importe_linea,
                ];
            })->values()->toArray();

            $totalCapitulo = collect($lineas)->sum('total');

            return [
                'oficio' => $grupo->first()->oficio->nombre ?? 'Capítulo',
                'lineas' => $lineas,
                'total'  => $totalCapitulo,
            ];
        })->values()->toArray();

        $total = $certs->sum('total');

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView(
            'pdf.certificacion',
            compact('obra', 'empresa', 'cliente', 'fecha', 'numero_certificacion', 'capitulos', 'total')
        )->setPaper('a4', 'portrait');

        return response()->streamDownload(
            fn() => print($pdf->output()),
            'informe_certificacion_' . $numero_certificacion . '.pdf'
        );
    }






    public function render()
    {
        $query = Certificacion::where('obra_id', $this->obraId)
            ->with(['oficio', 'cliente']);

        // BUSCADOR (solo campos válidos)
        if ($this->search) {
            $query->where('numero_certificacion', 'like', "%{$this->search}%");
        }

        // FILTRO OFICIO
        if ($this->filtroOficio) {
            $query->where('obra_gasto_categoria_id', $this->filtroOficio);
        }

        // FILTRO CLIENTE
        if ($this->filtroCliente) {
            $query->where('cliente_id', $this->filtroCliente);
        }

        // ESTADO CERTIFICACIÓN
        if ($this->estadoCertificacion) {
            $query->where('estado_certificacion', $this->estadoCertificacion);
        }

        // FECHAS (fecha_certificacion)
        if ($this->fechaDesde) {
            $query->whereDate('fecha_ingreso', '>=', $this->fechaDesde);
        }

        if ($this->fechaHasta) {
            $query->whereDate('fecha_ingreso', '<=', $this->fechaHasta);
        }




        return view('livewire.empresa.certificaciones.index', [
            'certificaciones' => $query
                ->orderBy('fecha_ingreso', 'desc')
                ->paginate(10),

            'oficios' => ObraGastoCategoria::where('obra_id', $this->obraId)
                ->orderBy('nombre')
                ->get(),

            // NECESARIO para el filtro
            'clientes' => Cliente::orderBy('nombre')->get(),
        ]);
    }
}
