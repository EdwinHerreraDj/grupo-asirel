<?php

namespace App\Livewire\Empresa;

use App\Models\Empresa;
use Livewire\Component;

class PdfConfig extends Component
{
    public ?int $empresa_id = null;

    public string $color_primario = '#111827';
    public string $color_secundario = '#d1d5db';
    public bool $mostrar_logo_pdf = true;
    public ?string $pie_pdf = null;

    protected function rules(): array
    {
        return [
            'color_primario'   => 'required|regex:/^#[0-9A-Fa-f]{6}$/',
            'color_secundario' => 'required|regex:/^#[0-9A-Fa-f]{6}$/',
            'mostrar_logo_pdf' => 'required|boolean',
            'pie_pdf'          => 'nullable|string|max:500',
        ];
    }

    protected $messages = [
        'color_primario.regex'   => 'El color principal debe estar en formato HEX (#RRGGBB).',
        'color_secundario.regex' => 'El color secundario debe estar en formato HEX (#RRGGBB).',
    ];

    public function mount(): void
    {
        $empresa = Empresa::first();

        if ($empresa) {
            $this->empresa_id = $empresa->id;
            $this->color_primario = $empresa->color_primario ?? '#111827';
            $this->color_secundario = $empresa->color_secundario ?? '#d1d5db';
            $this->mostrar_logo_pdf = (bool) ($empresa->mostrar_logo_pdf ?? true);
            $this->pie_pdf = $empresa->pie_pdf;
        }
    }

    public function guardar(): void
    {
        $data = $this->validate();

        $empresa = $this->empresa_id
            ? Empresa::findOrFail($this->empresa_id)
            : Empresa::first();

        if (! $empresa) {
            $this->dispatch('notify', type: 'error', message: 'No hay datos de empresa. Crea primero los datos básicos.');

            return;
        }

        $empresa->update([
            'color_primario'   => $data['color_primario'],
            'color_secundario' => $data['color_secundario'],
            'mostrar_logo_pdf' => (bool) $data['mostrar_logo_pdf'],
            'pie_pdf'          => $data['pie_pdf'] ?: null,
        ]);

        $this->dispatch('notify', type: 'success', message: 'Diseño de PDFs guardado correctamente.');
    }

    public function restablecerDefaults(): void
    {
        $this->color_primario = '#111827';
        $this->color_secundario = '#d1d5db';
        $this->mostrar_logo_pdf = true;
        $this->pie_pdf = null;

        $this->dispatch('notify', type: 'success', message: 'Restablecido a valores por defecto. Pulsa "Guardar" para aplicar.');
    }

    public function render()
    {
        return view('livewire.empresa.pdf-config');
    }
}
