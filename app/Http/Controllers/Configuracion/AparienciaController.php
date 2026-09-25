<?php

namespace App\Http\Controllers\Configuracion;

use App\Http\Controllers\Controller;
use App\Models\PanelApariencia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * Imágenes del panel: logo del menú, icono del menú plegado y favicon.
 * Solo admin y super_admin (middleware en las rutas).
 */
class AparienciaController extends Controller
{
    public function index()
    {
        return view('configuracion.apariencia', [
            'apariencia' => PanelApariencia::actual(),
        ]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'logo' => ['nullable', 'file', 'mimes:png,jpg,jpeg,webp', 'max:2048'],
            'logo_pequeno' => ['nullable', 'file', 'mimes:png,jpg,jpeg,webp', 'max:1024'],
            'favicon' => ['nullable', 'file', 'mimes:png,jpg,jpeg,webp,ico', 'max:512'],
        ], [
            'logo.mimes' => 'El logo debe ser PNG, JPG o WEBP.',
            'logo.max' => 'El logo no puede pasar de 2 MB.',
            'logo_pequeno.mimes' => 'El icono del menú plegado debe ser PNG, JPG o WEBP.',
            'logo_pequeno.max' => 'El icono del menú plegado no puede pasar de 1 MB.',
            'favicon.mimes' => 'El favicon debe ser PNG, JPG, WEBP o ICO.',
            'favicon.max' => 'El favicon no puede pasar de 512 KB.',
        ]);

        $apariencia = PanelApariencia::actual();
        $cambiadas = 0;

        foreach (PanelApariencia::CAMPOS as $campo) {
            if (! $request->hasFile($campo)) {
                continue;
            }

            $anterior = $apariencia->{$campo};
            $apariencia->update([$campo => $request->file($campo)->store('panel', 'public')]);

            // El archivo viejo ya no se usa.
            if ($anterior) {
                Storage::disk('public')->delete($anterior);
            }
            $cambiadas++;
        }

        PanelApariencia::olvidar();

        return redirect()->route('configuracion.apariencia')->with(
            'apariencia_ok',
            $cambiadas === 0
                ? 'No elegiste ninguna imagen, así que no se ha cambiado nada.'
                : ($cambiadas === 1 ? 'Imagen actualizada.' : "{$cambiadas} imágenes actualizadas."),
        );
    }

    /** Vuelve a la imagen por defecto del tema. */
    public function eliminar(string $campo)
    {
        abort_unless(in_array($campo, PanelApariencia::CAMPOS, true), 404);

        $apariencia = PanelApariencia::actual();

        if ($apariencia->{$campo}) {
            Storage::disk('public')->delete($apariencia->{$campo});
            $apariencia->update([$campo => null]);
            PanelApariencia::olvidar();
        }

        return redirect()->route('configuracion.apariencia')
            ->with('apariencia_ok', 'Imagen quitada: se usa la que trae la aplicación.');
    }
}
