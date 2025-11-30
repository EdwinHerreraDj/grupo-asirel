<?php

namespace App\Livewire\Empresa;

use App\Models\Empresa;
use Livewire\Component;

class EmpresaInfo extends Component
{
    public $empresa;

    protected $listeners = ['empresaGuardada' => 'actualizarDatos'];

    public function mount()
    {
        $this->empresa = Empresa::first();
    }

    public function actualizarDatos()
    {
        // Recarga la información desde la base de datos
        $this->empresa = Empresa::first();
    }

    public function render()
    {
        return view('livewire.empresa.empresa-info');
    }
}
