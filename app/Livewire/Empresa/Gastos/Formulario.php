<?php

namespace App\Livewire\Empresa\Gastos;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\GastoGeneralEmpresa;
use App\Models\CategoriaGastoEmpresa;

class Formulario extends Component
{
    use WithFileUploads;

    // Campos del formulario
    public $concepto;
    public $numero_factura;
    public $importe;
    public $fecha_factura;
    public $fecha_contable;
    public $fecha_vencimiento;
    public $especificacion;
    public $categoria_id;
    public $factura;

    // Validación
    protected $rules = [
        'concepto'          => 'required|string|max:255',
        'importe'           => 'required|numeric',
        'fecha_factura'     => 'required|date',
        'categoria_id'      => 'required|exists:categorias_gastos_empresa,id',
        'factura'           => 'nullable|file|max:4096', // 4MB
    ];

    public function guardar()
    {
        $this->validate();

        // Subida de factura (si existe)
        $facturaUrl = $this->factura
            ? $this->factura->store('facturas-empresa', 'public')
            : null;

        // Crear registro
        GastoGeneralEmpresa::create([
            'concepto'          => $this->concepto,
            'importe'           => $this->importe,
            'fecha_factura'     => $this->fecha_factura,
            'fecha_contable'    => $this->fecha_contable,
            'fecha_vencimiento' => $this->fecha_vencimiento,
            'numero_factura'    => $this->numero_factura,
            'especificacion'    => $this->especificacion,
            'categoria_id'      => $this->categoria_id,
            'factura_url'       => $facturaUrl,
        ]);

        // Toast
        $this->dispatch('toast', type: 'success', text: 'Gasto guardado correctamente.');

        // Cerrar modal desde el padre
        $this->dispatch('gastoGuardado');

        // Limpiar formulario
        $this->reset();
    }

    public function render()
    {
        return view('livewire.empresa.gastos.formulario', [
            'categoriasPadre' => CategoriaGastoEmpresa::where('nivel', 1)
                ->with('children')
                ->ordenado()
                ->get(),
        ]);
    }
}
