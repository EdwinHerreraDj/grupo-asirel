<?php

namespace App\Livewire\Empresa\Facturas\Series;

use Livewire\Component;
use App\Models\FacturaSerie;

class Index extends Component
{
    public $serie = '';
    public $ultimo_numero = 0;
    public $editandoId = null;

    public bool $mostrarModal = false;

    public string $tmpSerie = '';
    public string $tmpEstado = '';

    public string $filtroSerie = '';
    public string $filtroEstado = '';



    /* =========================
       ABRIR MODAL NUEVA SERIE
    ========================== */
    public function nuevaSerie()
    {
        $this->reset(['serie', 'ultimo_numero', 'editandoId']);
        $this->mostrarModal = true;
    }

    /* =========================
       EDITAR SERIE
    ========================== */
    public function editar($id)
    {
        $serie = FacturaSerie::findOrFail($id);

        $this->editandoId = $serie->id;
        $this->serie = $serie->serie;
        $this->ultimo_numero = $serie->ultimo_numero;

        $this->mostrarModal = true;
    }

    /* =========================
       CERRAR MODAL
    ========================== */
    public function cancelarEdicion()
    {
        $this->reset(['serie', 'ultimo_numero', 'editandoId']);
        $this->mostrarModal = false;
    }

    /* =========================
       GUARDAR / ACTUALIZAR
    ========================== */
    public function guardar()
    {
        $this->validate([
            'serie' => 'required|string|max:10|unique:factura_series,serie,' . $this->editandoId,
            'ultimo_numero' => 'required|integer|min:0',
        ]);

        $esEdicion = !is_null($this->editandoId);

        FacturaSerie::updateOrCreate(
            ['id' => $this->editandoId],
            [
                'serie' => $this->serie,
                'ultimo_numero' => $this->ultimo_numero,
                'activa' => true,
            ]
        );

        $this->cancelarEdicion();

        $this->dispatch(
            'toast',
            type: 'success',
            text: $esEdicion
                ? 'Serie actualizada correctamente.'
                : 'Serie creada correctamente.'
        );
    }


    /* =========================
       ACTIVAR / DESACTIVAR
    ========================== */
    public function toggleActiva($id)
    {
        $serie = FacturaSerie::findOrFail($id);
        $serie->activa = ! $serie->activa;
        $serie->save();
    }

    public function aplicarFiltros()
    {
        $this->filtroSerie = $this->tmpSerie;
        $this->filtroEstado = $this->tmpEstado;
    }

    public function limpiarFiltros()
    {
        $this->reset(['tmpSerie', 'tmpEstado', 'filtroSerie', 'filtroEstado']);
    }



    public function render()
    {
        $query = FacturaSerie::query();

        if ($this->filtroSerie !== '') {
            $query->where('serie', 'like', '%' . $this->filtroSerie . '%');
        }

        if ($this->filtroEstado !== '') {
            $query->where('activa', (int) $this->filtroEstado);
        }

        return view('livewire.empresa.facturas.series.index', [
            'series' => $query->orderBy('serie')->get(),
        ]);
    }
}
