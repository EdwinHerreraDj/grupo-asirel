<?php

namespace App\Livewire\Informes;

use App\Models\Obra;
use Livewire\Component;

class InformesGeneral extends Component
{
    // === Filtros: Liquidación de IVA ===
    public ?string $ivaFechaInicio = null;
    public ?string $ivaFechaFin = null;
    public string $ivaFormato = 'pdf';

    // === Filtros: Análisis bruto de obras ===
    public string $abObraSeleccionada = 'todas';
    public string $abEstadoSeleccionado = 'todas';
    public ?string $abFechaInicio = null;
    public ?string $abFechaFin = null;
    public string $abFormato = 'pdf';

    public $obras = [];

    public function mount(): void
    {
        $this->obras = Obra::orderBy('nombre')->get();

        // Por defecto, periodo de IVA = trimestre actual
        $now = now();
        $mesInicioTrimestre = (int) (floor(($now->month - 1) / 3) * 3) + 1;
        $this->ivaFechaInicio = $now->copy()->month($mesInicioTrimestre)->startOfMonth()->format('Y-m-d');
        $this->ivaFechaFin    = $now->copy()->month($mesInicioTrimestre + 2)->endOfMonth()->format('Y-m-d');
    }

    public function exportarLiquidacionIva(): void
    {
        if ($this->ivaFechaInicio && $this->ivaFechaFin
            && $this->ivaFechaInicio > $this->ivaFechaFin) {
            $this->dispatch('notify', type: 'error', message: 'La fecha desde no puede ser mayor que la fecha hasta.');

            return;
        }

        $url = route('informes.exportar.liquidacion-iva', [
            'fecha_inicio' => $this->ivaFechaInicio,
            'fecha_fin'    => $this->ivaFechaFin,
            'formato'      => $this->ivaFormato,
        ]);

        $this->dispatch(
            'descargar-informe',
            url: $url,
            filename: 'liquidacion_iva.' . ($this->ivaFormato === 'excel' ? 'xlsx' : 'pdf'),
        );
    }

    public function exportarAnalisisBrutoObras(): void
    {
        if ($this->abFechaInicio && $this->abFechaFin
            && $this->abFechaInicio > $this->abFechaFin) {
            $this->dispatch('notify', type: 'error', message: 'La fecha desde no puede ser mayor que la fecha hasta.');

            return;
        }

        $url = route('informes.exportar.analisis-bruto-obras', [
            'obra_id'      => $this->abObraSeleccionada !== 'todas' ? $this->abObraSeleccionada : null,
            'estado'       => $this->abEstadoSeleccionado,
            'fecha_inicio' => $this->abFechaInicio,
            'fecha_fin'    => $this->abFechaFin,
            'formato'      => $this->abFormato,
        ]);

        $this->dispatch(
            'descargar-informe',
            url: $url,
            filename: 'analisis_bruto_obras.' . ($this->abFormato === 'excel' ? 'xlsx' : 'pdf'),
        );
    }

    public function aplicarTrimestreActual(): void
    {
        $now = now();
        $mesInicio = (int) (floor(($now->month - 1) / 3) * 3) + 1;
        $this->ivaFechaInicio = $now->copy()->month($mesInicio)->startOfMonth()->format('Y-m-d');
        $this->ivaFechaFin    = $now->copy()->month($mesInicio + 2)->endOfMonth()->format('Y-m-d');
    }

    public function aplicarTrimestreAnterior(): void
    {
        $now = now()->subMonths(3);
        $mesInicio = (int) (floor(($now->month - 1) / 3) * 3) + 1;
        $this->ivaFechaInicio = $now->copy()->month($mesInicio)->startOfMonth()->format('Y-m-d');
        $this->ivaFechaFin    = $now->copy()->month($mesInicio + 2)->endOfMonth()->format('Y-m-d');
    }

    public function aplicarAnioActual(): void
    {
        $this->ivaFechaInicio = now()->startOfYear()->format('Y-m-d');
        $this->ivaFechaFin    = now()->endOfYear()->format('Y-m-d');
    }

    public function render()
    {
        return view('livewire.empresa.informes.informes-general');
    }
}
