<?php

namespace App\Livewire\Informes;


use Livewire\Component;
use App\Models\Obra;

class InformesGeneral extends Component
{
    public $obraSeleccionada = 'todas';
    public $anioSeleccionado;
    public $formato = 'excel';
    public $estadoSeleccionado = 'todas';
    public $fechaInicio;
    public $fechaFin;
    public $porcentajeAdicional = 10;



    public $obras = [];

    public function mount()
    {
        $this->anioSeleccionado = now()->year;
        $this->obras = Obra::orderBy('nombre')->get();
    }

    public function render()
    {
        return view('livewire.empresa.informes.informes-general');

    }

    /**
     * Exportar informe (por ahora sólo Coste Total)
     */
    public function exportarCosteTotal()
    {
        $params = [
            'obra_id' => $this->obraSeleccionada !== 'todas' ? $this->obraSeleccionada : null,
            'estado' => $this->estadoSeleccionado ?? 'todas',
            'fecha_inicio' => $this->fechaInicio ?? null,
            'fecha_fin' => $this->fechaFin ?? null,
            'formato' => $this->formato,
        ];

        return redirect()->route('informes.exportar.coste-total-obras', $params);
    }

    public function exportarFacturacionTotal()
    {
        $params = [
            'obra_id' => $this->obraSeleccionada !== 'todas' ? $this->obraSeleccionada : null,
            'estado' => $this->estadoSeleccionado ?? 'todas',
            'fecha_inicio' => $this->fechaInicio ?? null,
            'fecha_fin' => $this->fechaFin ?? null,
            'formato' => $this->formato,
        ];

        return redirect()->route('informes.exportar.facturacion-total', $params);
    }

    public function exportarCosteVentaMensual()
    {
        $params = [
            'obra_id' => $this->obraSeleccionada !== 'todas' ? $this->obraSeleccionada : null,
            'estado' => $this->estadoSeleccionado ?? 'todas',
            'fecha_inicio' => $this->fechaInicio ?? null,
            'fecha_fin' => $this->fechaFin ?? null,
            'porcentaje' => $this->porcentajeAdicional ?? 10,
            'formato' => $this->formato,
        ];
    
        return redirect()->route('informes.exportar.coste-venta-mensual', $params);
    }


    public function exportarRentabilidad()
    {
        $params = [
            'obra_id' => $this->obraSeleccionada !== 'todas' ? $this->obraSeleccionada : null,
            'estado' => $this->estadoSeleccionado ?? 'todas',
            'fecha_inicio' => $this->fechaInicio ?? null,
            'fecha_fin' => $this->fechaFin ?? null,
            'formato' => $this->formato,
        ];

        return redirect()->route('informes.exportar.rentabilidad', $params);
    }

}
