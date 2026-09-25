<?php

namespace App\Http\Controllers;

use App\Models\LoginLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

/**
 * Gestión de usuarios. La ruta está restringida a admin y super_admin
 * (middleware role). Reglas adicionales:
 *  - Solo un super_admin puede crear, editar o borrar super_admins.
 *  - Nadie puede borrarse a sí mismo.
 *  - No se puede borrar ni degradar al último super_admin.
 */
class UserController extends Controller
{
    private const POR_PAGINA = 15;

    /** Listado con búsqueda, filtro por rol y último acceso de cada uno. */
    public function index(Request $request)
    {
        $filtros = $request->validate([
            'buscar' => ['nullable', 'string', 'max:100'],
            'rol' => ['nullable', Rule::in([User::ROLE_SUPER_ADMIN, User::ROLE_ADMIN, User::ROLE_USER])],
        ]);

        $query = User::query();

        if (! empty($filtros['buscar'])) {
            $like = '%'.addcslashes($filtros['buscar'], '%_\\').'%';
            $query->where(fn ($q) => $q->where('name', 'like', $like)->orWhere('email', 'like', $like));
        }
        if (! empty($filtros['rol'])) {
            $query->where('role', $filtros['rol']);
        }

        $users = $query->orderBy('name')->paginate(self::POR_PAGINA)->withQueryString();

        // Último acceso de los usuarios de esta página, en una sola consulta.
        $ultimosAccesos = LoginLog::selectRaw('user_id, MAX(logged_in_at) as ultimo')
            ->whereIn('user_id', $users->pluck('id'))
            ->groupBy('user_id')
            ->pluck('ultimo', 'user_id');

        $porRol = User::selectRaw('role, COUNT(*) as total')->groupBy('role')->pluck('total', 'role');

        return view('users.index', [
            'users' => $users,
            'actor' => $request->user(),
            'totalSuperAdmins' => (int) ($porRol[User::ROLE_SUPER_ADMIN] ?? 0),
            'ultimosAccesos' => $ultimosAccesos,
            'filtros' => $filtros,
            'stats' => [
                'total' => (int) $porRol->sum(),
                'super_admins' => (int) ($porRol[User::ROLE_SUPER_ADMIN] ?? 0),
                'admins' => (int) ($porRol[User::ROLE_ADMIN] ?? 0),
                'usuarios' => (int) ($porRol[User::ROLE_USER] ?? 0),
            ],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validar los datos de entrada
        $validatedData = $request->validate([
            'name' => 'required|string|max:30',
            'email' => 'required|email|max:150|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => ['required', Rule::in($this->rolesAsignables($request->user()))],
        ], [
            'role.in' => 'No tienes permisos para asignar ese rol.',
        ]);

        // Crear el usuario
        User::create([
            'name' => $validatedData['name'],
            'email' => $validatedData['email'],
            'password' => Hash::make($validatedData['password']),
            'role' => $validatedData['role'],
        ]);

        // Redirigir con mensaje de éxito
        return redirect()->route('users.index')->with('success', 'Usuario creado exitosamente.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $actor = $request->user();

        if (! $actor->puedeGestionarUsuario($user)) {
            return redirect()->route('users.index')
                ->with('error', 'Solo un super administrador puede modificar a otro super administrador.');
        }

        // Validar los datos
        $validatedData = $request->validate([
            'name' => 'required|string|max:30',
            'email' => "required|string|email|max:150|unique:users,email,$id",
            'password' => 'nullable|string|min:8|confirmed',
            'role' => ['required', Rule::in($this->rolesAsignables($actor))],
        ], [
            'role.in' => 'No tienes permisos para asignar ese rol.',
        ]);

        if ($this->esUltimoSuperAdmin($user) && $validatedData['role'] !== User::ROLE_SUPER_ADMIN) {
            return redirect()->route('users.index')
                ->with('error', 'No se puede quitar el rol al único super administrador.');
        }

        // Actualizar el usuario
        $user->update([
            'name' => $validatedData['name'],
            'email' => $validatedData['email'],
            'role' => $validatedData['role'],
            'password' => $request->filled('password') ? Hash::make($validatedData['password']) : $user->password,
        ]);

        // El menú de arriba lee el nombre de la sesión.
        if ($user->id === $actor->id) {
            session(['user_name' => $user->name, 'user_email' => $user->email]);
        }

        return redirect()->route('users.index')->with('success', 'Usuario actualizado exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, $id)
    {
        try {
            $user = User::findOrFail($id);
            $actor = $request->user();

            $motivo = match (true) {
                $user->id === $actor->id => 'No puedes eliminar tu propio usuario.',
                ! $actor->puedeGestionarUsuario($user) => 'Solo un super administrador puede eliminar a otro super administrador.',
                $this->esUltimoSuperAdmin($user) => 'No se puede eliminar al único super administrador.',
                default => null,
            };

            if ($motivo) {
                return $request->expectsJson()
                    ? response()->json(['success' => false, 'message' => $motivo], 403)
                    : redirect()->route('users.index')->with('error', $motivo);
            }

            $nombre = $user->name;
            $user->delete();

            return $request->expectsJson()
                ? response()->json(['success' => true, 'message' => 'Usuario eliminado exitosamente.'])
                : redirect()->route('users.index')->with('success', "Usuario «{$nombre}» eliminado.");
        } catch (\Exception $e) {
            Log::error('Error al eliminar el usuario: '.$e->getMessage());

            return $request->expectsJson()
                ? response()->json(['success' => false, 'message' => 'Ocurrió un error al eliminar el usuario.'], 500)
                : redirect()->route('users.index')->with('error', 'Ocurrió un error al eliminar el usuario.');
        }
    }

    private function rolesAsignables(User $actor): array
    {
        return $actor->isSuperAdmin()
            ? [User::ROLE_SUPER_ADMIN, User::ROLE_ADMIN, User::ROLE_USER]
            : [User::ROLE_ADMIN, User::ROLE_USER];
    }

    private function esUltimoSuperAdmin(User $user): bool
    {
        return $user->isSuperAdmin()
            && User::where('role', User::ROLE_SUPER_ADMIN)->count() <= 1;
    }
}
