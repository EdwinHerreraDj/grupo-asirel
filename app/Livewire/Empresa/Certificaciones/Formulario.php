<?php

namespace App\Livewire\Empresa\Certificaciones;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Certificacion;
use App\Models\ObraGastoCategoria;

class Formulario extends Component
{
    use WithFileUploads;

    public $obraId;

    public $fecha_ingreso;
    public $fecha_contable;
    public $fecha_vencimiento;
    public $numero_certificacion;
    public $obra_gasto_categoria_id;
    public $especificacion;
    public $tipo_documento = "defecto";
    public $total;
    public $adjunto;


    public function mount($obraId)
    {
        $this->obraId = $obraId;
        $this->tipo_documento = null;
    }

    protected $rules = [
        'fecha_ingreso'             => 'required|date',
        'total'                     => 'required|numeric|min:0',
        'tipo_documento'            => 'required|in:factura,certificacion',
        'obra_gasto_categoria_id'   => 'required|exists:obra_gasto_categorias,id',
        'adjunto'                   => 'nullable|file|max:4096',
        'fecha_contable'            => 'nullable|date',
        'numero_certificacion'      => 'nullable|string|max:255',
        'especificacion'            => 'nullable|string|max:255',
        'fecha_vencimiento'         => 'nullable|date',
    ];

    public function updatedTipoDocumento($value)
    {
        $this->tipo_documento = $value;
    }


    public function guardar()
    {
        $this->validate();

        $adjuntoUrl = $this->adjunto
            ? $this->adjunto->store('certificaciones', 'public')
            : null;

        Certificacion::create([
            'obra_id'                   => $this->obraId,
            'fecha_ingreso'             => $this->fecha_ingreso,
            'fecha_contable'            => $this->fecha_contable,
            'fecha_vencimiento'         => $this->fecha_vencimiento,
            'numero_certificacion'      => $this->numero_certificacion,
            'obra_gasto_categoria_id'   => $this->obra_gasto_categoria_id,
            'especificacion'            => $this->especificacion,
            'tipo_documento'            => $this->tipo_documento,
            'total'                     => $this->total,
            'adjunto_url'               => $adjuntoUrl,
        ]);

        $this->dispatch('toast', type: 'success', text: 'Certificación registrada correctamente.');
        $this->dispatch('cerrarModal');

        $this->reset([
            'fecha_ingreso',
            'fecha_contable',
            'fecha_vencimiento',
            'numero_certificacion',
            'obra_gasto_categoria_id',
            'especificacion',
            'tipo_documento',
            'total',
            'adjunto',
        ]);
    }

    public function render()
    {
        return view('livewire.empresa.certificaciones.formulario', [
            'oficios' => ObraGastoCategoria::where('obra_id', $this->obraId)
                ->orderBy('nombre')
                ->get(),
        ]);
    }
}
