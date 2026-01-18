<?php

namespace App\Livewire\Empresa\Certificaciones;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Certificacion;
use App\Models\ObraGastoCategoria;
use App\Models\Cliente;

class Formulario extends Component
{
    use WithFileUploads;

    public $obraId;

    public $cliente_id;
    public $obra_gasto_categoria_id;

    public $fecha_ingreso;
    public $fecha_contable;

    public $iva_porcentaje = 21;
    public $retencion_porcentaje = 0;

    public $numero_certificacion;
    public $adjunto;

    /* Crear nuevo capitulo de certificación */
    protected $listeners = ['crearCapitulo'];
    public $modoCapitulo = false;
    public $obra_id;
    public $fecha_vencimiento;



    public function mount($obraId)
    {
        $this->obraId = $obraId;
    }

    protected $rules = [
        'cliente_id'               => 'required|exists:clientes,id',
        'obra_gasto_categoria_id'  => 'required|exists:obra_gasto_categorias,id',

        'fecha_ingreso'      => 'required|date',
        'fecha_contable'           => 'nullable|date',

        'iva_porcentaje'          => 'nullable|numeric|min:0',
        'retencion_porcentaje'    => 'nullable|numeric|min:0',

        'numero_certificacion'     => 'nullable|string|max:255',
        'adjunto'                  => 'nullable|file|max:4096',
    ];

    public function guardar()
    {
        $this->validate();

        // Subida de adjunto SOLO en modo normal
        $adjuntoUrl = null;

        if (! $this->modoCapitulo && $this->adjunto) {
            $adjuntoUrl = $this->adjunto->store('certificaciones', 'public');
        }

        Certificacion::create([
            // 🔒 OBRA
            'obra_id' => $this->modoCapitulo
                ? $this->obra_id
                : $this->obraId,

            // 🔒 CLIENTE
            'cliente_id' => $this->cliente_id,

            // ✏️ OFICIO (obligatorio en ambos)
            'obra_gasto_categoria_id' => $this->obra_gasto_categoria_id,

            // FECHAS
            'fecha_ingreso' => $this->fecha_ingreso ?? now(),
            'fecha_contable' => $this->fecha_contable,
            'fecha_vencimiento' => $this->fecha_vencimiento,

            // 🔒 NÚMERO DE CERTIFICACIÓN
            'numero_certificacion' => $this->numero_certificacion,

            // IMPORTES INICIALES
            'base_imponible' => 0,
            'iva_porcentaje' => $this->iva_porcentaje,
            'iva_importe' => 0,
            'retencion_porcentaje' => $this->retencion_porcentaje,
            'retencion_importe' => 0,
            'total' => 0,

            // ESTADOS
            'estado_certificacion' => 'pendiente',
            'estado_factura' => 'pendiente',

            // ADJUNTO
            'adjunto_url' => $adjuntoUrl,
        ]);

        $this->dispatch(
            'toast',
            type: 'success',
            text: $this->modoCapitulo
                ? 'Capítulo creado. Añade ahora los conceptos.'
                : 'Certificación creada. Añade ahora los conceptos.'
        );

        $this->dispatch('cerrarModal');

        // Reset
        $this->reset([
            'cliente_id',
            'obra_gasto_categoria_id',
            'fecha_ingreso',
            'fecha_contable',
            'fecha_vencimiento',
            'iva_porcentaje',
            'retencion_porcentaje',
            'numero_certificacion',
            'adjunto',
            'modoCapitulo',
            'obra_id',
        ]);
    }


    /* Metodos para crear capítulos */
    public function crearCapitulo(int $certificacionId)
    {
        $cert = Certificacion::findOrFail($certificacionId);

        $this->modoCapitulo = true;

        // DATOS HEREDADOS (NO editables)
        $this->numero_certificacion = $cert->numero_certificacion;
        $this->obra_id = $cert->obra_id;
        $this->cliente_id = $cert->cliente_id;
        $this->fecha_contable = $cert->fecha_contable;
        $this->fecha_vencimiento = $cert->fecha_vencimiento;
        $this->iva_porcentaje = $cert->iva_porcentaje;
        $this->retencion_porcentaje = $cert->retencion_porcentaje;

        // CAMPOS QUE EL USUARIO DEBE DEFINIR
        $this->obra_gasto_categoria_id = null;
        $this->fecha_ingreso = now();

        // NO adjunto en capítulos
        $this->adjunto = null;
    }



    public function render()
    {
        return view('livewire.empresa.certificaciones.formulario', [
            'oficios' => ObraGastoCategoria::where('obra_id', $this->obraId)
                ->orderBy('nombre')
                ->get(),

            'clientes' => Cliente::orderBy('nombre')->get(),
        ]);
    }
}
