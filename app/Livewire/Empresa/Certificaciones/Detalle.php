<?php

namespace App\Livewire\Empresa\Certificaciones;

use App\Models\Certificacion;
use App\Models\CertificacionDetalle;
use App\Services\CertificacionCalculator;
use Livewire\Component;

class Detalle extends Component
{
    public ?Certificacion $certificacion = null;

    public bool $showModal = false;
    public bool $modoEdicion = false;
    public ?int $detalleId = null;

    // Campos formulario
    public string $concepto = '';
    public string $unidad = '';
    public float $cantidad = 1;
    public float $precio_unitario = 0;

    public bool $showDeleteModal = false;
    public ?int $detalleAEliminarId = null;

    public bool $showAceptarModal = false;
    public bool $showImpuestosModal = false;

    // Filtros
    public $pendingSearch = '';
    public $pendingUnidad = '';
    public $pendingImporteMin = '';
    public $pendingImporteMax = '';

    public $search = '';
    public $filtroUnidad = '';
    public $importeMin = '';
    public $importeMax = '';

    // Impuestos
    public float $iva_porcentaje = 21;
    public float $retencion_porcentaje = 0;

    protected $rules = [
        'concepto'        => 'required|string|max:255',
        'unidad'          => 'required|string|max:100',
        'cantidad'        => 'required|numeric|min:0',
        'precio_unitario' => 'required|numeric|min:0',
    ];

    public function mount($certificacionId)
    {
        $this->certificacion = Certificacion::with([
            'cliente',
            'oficio',
            'detalles'
        ])->findOrFail($certificacionId);
    }

    /* =========================
       VALIDACIÓN CENTRAL
    ========================= */
    private function asegurarEditable(): bool
    {
        if ($this->certificacion->estado_certificacion !== 'pendiente') {
            $this->dispatch(
                'toast',
                type: 'error',
                text: 'La certificación no admite modificaciones.'
            );
            return false;
        }
        return true;
    }

    /* =========================
       CRUD DETALLES
    ========================= */

    public function abrirModalCrear()
    {
        if (!$this->asegurarEditable()) return;

        $this->resetFormulario();
        $this->modoEdicion = false;
        $this->showModal = true;
    }

    public function abrirModalEditar($detalleId)
    {
        if (!$this->asegurarEditable()) return;

        $detalle = CertificacionDetalle::findOrFail($detalleId);

        $this->detalleId = $detalle->id;
        $this->concepto = $detalle->concepto;
        $this->unidad = $detalle->unidad;
        $this->cantidad = (float) $detalle->cantidad;
        $this->precio_unitario = (float) $detalle->precio_unitario;

        $this->modoEdicion = true;
        $this->showModal = true;
    }

    public function guardarDetalle()
    {
        if (!$this->asegurarEditable()) return;

        $this->validate();

        $importe = $this->cantidad * $this->precio_unitario;

        if ($this->modoEdicion) {
            CertificacionDetalle::where('id', $this->detalleId)->update([
                'concepto'        => $this->concepto,
                'unidad'          => $this->unidad,
                'cantidad'        => $this->cantidad,
                'precio_unitario' => $this->precio_unitario,
                'importe_linea'   => $importe,
            ]);
        } else {
            CertificacionDetalle::create([
                'certificacion_id' => $this->certificacion->id,
                'concepto'         => $this->concepto,
                'unidad'           => $this->unidad,
                'cantidad'         => $this->cantidad,
                'precio_unitario'  => $this->precio_unitario,
                'importe_linea'    => $importe,
            ]);
        }

        app(CertificacionCalculator::class)
            ->recalcular($this->certificacion->fresh());

        $this->refrescarCertificacion();
        $this->cerrarModal();

        $this->dispatch(
            'toast',
            type: 'success',
            text: $this->modoEdicion
                ? 'Detalle actualizado correctamente.'
                : 'Detalle añadido correctamente.'
        );
    }

    public function confirmarEliminarDetalle($detalleId)
    {
        if (!$this->asegurarEditable()) return;

        $this->detalleAEliminarId = $detalleId;
        $this->showDeleteModal = true;
    }

    public function eliminarDetalle()
    {
        if (!$this->asegurarEditable()) return;

        CertificacionDetalle::where('id', $this->detalleAEliminarId)->delete();

        app(CertificacionCalculator::class)
            ->recalcular($this->certificacion->fresh());

        $this->refrescarCertificacion();

        $this->showDeleteModal = false;
        $this->detalleAEliminarId = null;

        $this->dispatch('toast', type: 'success', text: 'Detalle eliminado correctamente.');
    }

    /* =========================
       ACEPTAR CERTIFICACIÓN
    ========================= */

    public function confirmarAceptar()
    {
        if (!$this->asegurarEditable()) return;

        $this->showAceptarModal = true;
    }

    public function aceptarCertificacion()
    {
        if ($this->certificacion->detalles->isEmpty()) {
            $this->dispatch('toast', type: 'error', text: 'No puedes aceptar una certificación sin líneas.');
            return;
        }

        if (!$this->asegurarEditable()) return;

        $this->certificacion->update([
            'estado_certificacion' => 'aceptada',
        ]);

        $this->certificacion->refresh();
        $this->showAceptarModal = false;

        $this->dispatch(
            'toast',
            type: 'success',
            text: 'Certificación aceptada. Ya no se puede modificar.'
        );
    }

    /* =========================
       IMPUESTOS
    ========================= */

    public function abrirModalImpuestos()
    {
        if (!$this->asegurarEditable()) return;

        $this->iva_porcentaje = $this->certificacion->iva_porcentaje;
        $this->retencion_porcentaje = $this->certificacion->retencion_porcentaje;

        $this->showImpuestosModal = true;
    }

    public function guardarImpuestos()
    {
        if (!$this->asegurarEditable()) return;

        $this->validate([
            'iva_porcentaje'       => 'required|numeric|min:0',
            'retencion_porcentaje' => 'required|numeric|min:0',
        ]);

        // Actualizar TODAS las certificaciones del mismo número
        Certificacion::where('numero_certificacion', $this->certificacion->numero_certificacion)
            ->update([
                'iva_porcentaje'       => $this->iva_porcentaje,
                'retencion_porcentaje' => $this->retencion_porcentaje,
            ]);

        // Recalcular todas
        $certificaciones = Certificacion::where(
            'numero_certificacion',
            $this->certificacion->numero_certificacion
        )->get();

        foreach ($certificaciones as $cert) {
            app(CertificacionCalculator::class)->recalcular($cert);
        }

        // Refrescar la actual
        $this->refrescarCertificacion();

        $this->showImpuestosModal = false;

        $this->dispatch('toast', type: 'success', text: 'Fiscalidad actualizada para toda la certificación.');
    }
    
    /* =========================
       FILTROS
    ========================= */

    public function aplicarFiltros()
    {
        $this->search = $this->pendingSearch;
        $this->filtroUnidad = $this->pendingUnidad;
        $this->importeMin = $this->pendingImporteMin;
        $this->importeMax = $this->pendingImporteMax;
    }

    public function limpiarFiltros()
    {
        $this->pendingSearch = '';
        $this->pendingUnidad = '';
        $this->pendingImporteMin = '';
        $this->pendingImporteMax = '';

        $this->search = '';
        $this->filtroUnidad = '';
        $this->importeMin = '';
        $this->importeMax = '';
    }

    public function getDetallesFiltradosProperty()
    {
        return $this->certificacion->detalles
            ->when(
                $this->search,
                fn($c) =>
                $c->filter(
                    fn($d) =>
                    str_contains(mb_strtolower($d->concepto), mb_strtolower($this->search))
                )
            )
            ->when(
                $this->filtroUnidad,
                fn($c) =>
                $c->where('unidad', $this->filtroUnidad)
            )
            ->when(
                $this->importeMin !== '',
                fn($c) =>
                $c->where('importe_linea', '>=', (float) $this->importeMin)
            )
            ->when(
                $this->importeMax !== '',
                fn($c) =>
                $c->where('importe_linea', '<=', (float) $this->importeMax)
            );
    }

    /* =========================
       HELPERS
    ========================= */

    public function cerrarModal()
    {
        $this->resetFormulario();
        $this->modoEdicion = false;
        $this->showModal = false;
    }

    private function resetFormulario()
    {
        $this->detalleId = null;
        $this->concepto = '';
        $this->unidad = '';
        $this->cantidad = 1;
        $this->precio_unitario = 0;
    }

    private function refrescarCertificacion()
    {
        $this->certificacion = $this->certificacion->fresh([
            'cliente',
            'oficio',
            'detalles'
        ]);
    }

    public function render()
    {
        return view('livewire.empresa.certificaciones.detalle');
    }
}
