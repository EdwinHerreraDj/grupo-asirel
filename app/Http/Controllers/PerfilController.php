<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

/**
 * Mi perfil: cada usuario cambia sus datos, su foto y su contraseña.
 * No toca el rol: eso se gestiona en Usuarios.
 */
class PerfilController extends Controller
{
    public function index()
    {
        return view('perfil.index', ['usuario' => auth()->user()]);
    }

    /** Nombre, email y foto. */
    public function actualizar(Request $request)
    {
        $usuario = $request->user();

        $datos = $request->validate([
            'name' => ['required', 'string', 'max:30'],
            'email' => ['required', 'email', 'max:150', Rule::unique('users', 'email')->ignore($usuario->id)],
            'avatar' => ['nullable', 'file', 'mimes:png,jpg,jpeg,webp', 'max:2048'],
        ], [
            'email.unique' => 'Ese correo ya lo usa otra persona.',
            'avatar.mimes' => 'La foto debe ser PNG, JPG o WEBP.',
            'avatar.max' => 'La foto no puede pasar de 2 MB.',
        ]);

        if ($request->hasFile('avatar')) {
            $anterior = $usuario->avatar;
            $datos['avatar'] = $request->file('avatar')->store('avatares', 'public');

            if ($anterior) {
                Storage::disk('public')->delete($anterior);
            }
        } else {
            unset($datos['avatar']);
        }

        $usuario->update($datos);

        // El menú de arriba lee el nombre de la sesión.
        session(['user_name' => $usuario->name, 'user_email' => $usuario->email]);

        return redirect()->route('perfil.index')->with('perfil_ok', 'Datos actualizados.');
    }

    public function eliminarAvatar(Request $request)
    {
        $usuario = $request->user();

        if ($usuario->avatar) {
            Storage::disk('public')->delete($usuario->avatar);
            $usuario->update(['avatar' => null]);
        }

        return redirect()->route('perfil.index')->with('perfil_ok', 'Foto quitada: se vuelven a ver tus iniciales.');
    }

    /** Cambio de contraseña: hay que saber la actual. */
    public function cambiarContrasena(Request $request)
    {
        $request->validate([
            'contrasena_actual' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ], [
            'contrasena_actual.required' => 'Escribe tu contraseña actual.',
            'contrasena_actual.current_password' => 'La contraseña actual no es correcta.',
            'password.confirmed' => 'La nueva contraseña y su repetición no coinciden.',
        ]);

        $request->user()->update(['password' => $request->input('password')]);

        // Las demás sesiones de este usuario dejan de valer.
        $request->session()->regenerate();

        return redirect()->route('perfil.index')->with('perfil_ok', 'Contraseña cambiada.');
    }
}
