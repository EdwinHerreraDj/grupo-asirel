<?php

namespace App\Livewire\Empresa;

use App\Models\Empresa;
use Livewire\Component;
use Livewire\WithFileUploads;

class EmpresaDatos extends Component
{
    use WithFileUploads;

    // Identificador y campos del formulario
    public ?int $empresa_id = null;

    public ?string $nombre = null;

    public ?string $cif = null;

    public ?string $direccion = null;

    public ?string $codigo_postal = null;

    public ?string $ciudad = null;

    public ?string $provincia = null;

    public string $pais = 'España';

    public ?string $telefono = null;

    public ?string $email = null;

    public ?string $sitio_web = null;

    public ?string $descripcion = null;

    // Para subida de archivos
    public $logo; // Livewire file

    // Modo (crear/editar)
    public bool $modoEdicion = false;

    // Mensaje simple inline (opcional)
    public ?string $mensaje = null;

    protected function rules(): array
    {
        return [
            'nombre' => 'required|string|max:255',
            'cif' => 'nullable|string|max:50',
            'direccion' => 'nullable|string|max:255',
            'codigo_postal' => 'nullable|string|max:10',
            'ciudad' => 'nullable|string|max:100',
            'provincia' => 'nullable|string|max:100',
            'pais' => 'nullable|string|max:100',
            'telefono' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:150',
            'sitio_web' => 'nullable|url|max:150',
            'descripcion' => 'nullable|string',
            'logo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ];
    }

    protected $messages = [
        'nombre.required' => 'El nombre de la empresa es obligatorio.',
        'email.email' => 'El correo electrónico no es válido.',
        'sitio_web.url' => 'La URL del sitio web no es válida.',
        'logo.image' => 'El logo debe ser un archivo de tipo: jpg, jpeg, png o webp.',
        'logo.mimes' => 'El logo debe ser un archivo de tipo: jpg, jpeg, png o webp.',
        'logo.max' => 'El logo no debe superar los 2 MB de tamaño.',
    ];

    public function mount(): void
    {
        $empresa = Empresa::first();

        if ($empresa) {
            $this->empresa_id = $empresa->id;
            $this->nombre = $empresa->nombre;
            $this->cif = $empresa->cif;
            $this->direccion = $empresa->direccion;
            $this->codigo_postal = $empresa->codigo_postal;
            $this->ciudad = $empresa->ciudad;
            $this->provincia = $empresa->provincia;
            $this->pais = $empresa->pais ?? 'España';
            $this->telefono = $empresa->telefono;
            $this->email = $empresa->email;
            $this->sitio_web = $empresa->sitio_web;
            $this->logo = $empresa->logo;
            $this->descripcion = $empresa->descripcion;

            $this->modoEdicion = true;
        }
    }

    public function guardar(): void
    {
        $this->validate();

        if ($this->logo) {
            $path = $this->logo->store('logos', 'public');
        }

        if ($this->modoEdicion && $this->empresa_id) {
            $empresa = Empresa::findOrFail($this->empresa_id);
            $empresa->update([
                'nombre' => $this->nombre,
                'cif' => $this->cif,
                'direccion' => $this->direccion,
                'codigo_postal' => $this->codigo_postal,
                'ciudad' => $this->ciudad,
                'provincia' => $this->provincia,
                'pais' => $this->pais,
                'telefono' => $this->telefono,
                'email' => $this->email,
                'sitio_web' => $this->sitio_web,
                'descripcion' => $this->descripcion,
                'logo' => isset($path) ? $path : $empresa->logo,
            ]);

            $mensaje = 'Datos de la empresa actualizados correctamente.';
        } else {
            $empresa = Empresa::create([
                'nombre' => $this->nombre,
                'cif' => $this->cif,
                'direccion' => $this->direccion,
                'codigo_postal' => $this->codigo_postal,
                'ciudad' => $this->ciudad,
                'provincia' => $this->provincia,
                'pais' => $this->pais,
                'telefono' => $this->telefono,
                'email' => $this->email,
                'sitio_web' => $this->sitio_web,
                'descripcion' => $this->descripcion,
                'logo' => isset($path) ? $path : null,
            ]);

            $this->empresa_id = $empresa->id;
            $this->modoEdicion = true;
            $mensaje = 'Datos de la empresa creados correctamente.';
        }

        // Emitir evento al frontend con el mensaje
        $this->dispatch('empresaGuardada', message: $mensaje);
    }

    public function updatedLogo()
    {
        $this->validateOnly('logo');
    }

    public function render()
    {
        // Puedes pasar la empresa para previsualizar logo, etc.
        return view('livewire.empresa.empresa-datos', [
            'empresa' => Empresa::first(),
        ]);
    }
}
