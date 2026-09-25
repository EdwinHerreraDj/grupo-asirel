<?php

namespace App\Http\Controllers;

use App\Models\LoginLog;
use App\Models\User;
use Illuminate\Http\Request;

/**
 * Registro de accesos. Solo super_admin (middleware en la ruta).
 */
class LoginLogController extends Controller
{
    private const POR_PAGINA = 25;

    public function index(Request $request)
    {
        $filtros = $request->validate([
            'usuario' => ['nullable', 'integer'],
            'desde' => ['nullable', 'date'],
            'hasta' => ['nullable', 'date'],
            'estado' => ['nullable', 'in:abierta,cerrada,caducada'],
            'buscar' => ['nullable', 'string', 'max:100'],
        ]);

        $minutosSesion = (int) config('session.lifetime', 120);
        $corte = now()->subMinutes($minutosSesion);

        $query = LoginLog::query()->with('user:id,name,email,role,avatar');

        if (! empty($filtros['usuario'])) {
            $query->where('user_id', $filtros['usuario']);
        }
        if (! empty($filtros['desde'])) {
            $query->whereDate('logged_in_at', '>=', $filtros['desde']);
        }
        if (! empty($filtros['hasta'])) {
            $query->whereDate('logged_in_at', '<=', $filtros['hasta']);
        }
        if (! empty($filtros['buscar'])) {
            $like = '%'.addcslashes($filtros['buscar'], '%_\\').'%';
            $query->where(fn ($q) => $q->where('ip_address', 'like', $like)
                ->orWhereHas('user', fn ($q) => $q->where('name', 'like', $like)->orWhere('email', 'like', $like)));
        }

        match ($filtros['estado'] ?? null) {
            'cerrada' => $query->whereNotNull('logged_out_at'),
            'abierta' => $query->whereNull('logged_out_at')->where('logged_in_at', '>=', $corte),
            'caducada' => $query->whereNull('logged_out_at')->where('logged_in_at', '<', $corte),
            default => null,
        };

        $accesos = $query->orderByDesc('logged_in_at')->orderByDesc('id')
            ->paginate(self::POR_PAGINA)->withQueryString();

        return view('users.logs', [
            'accesos' => $accesos,
            'usuarios' => User::orderBy('name')->get(['id', 'name']),
            'filtros' => $filtros,
            'minutosSesion' => $minutosSesion,
            'stats' => [
                'hoy' => LoginLog::whereDate('logged_in_at', today())->count(),
                'usuarios_hoy' => LoginLog::whereDate('logged_in_at', today())->distinct()->count('user_id'),
                'abiertas' => LoginLog::abiertas()->count(),
                'total' => LoginLog::count(),
            ],
        ]);
    }

    /** Borra accesos antiguos para que la tabla no crezca sin fin. */
    public function purgar(Request $request)
    {
        $datos = $request->validate([
            'meses' => ['required', 'integer', 'min:1', 'max:60'],
        ], [
            'meses.required' => 'Indica a partir de cuántos meses se borran.',
        ]);

        $borrados = LoginLog::where('logged_in_at', '<', now()->subMonths($datos['meses']))->delete();

        return redirect()->route('login.logs')->with(
            'success',
            $borrados === 0
                ? 'No había accesos anteriores a esa fecha.'
                : ($borrados === 1 ? 'Se borró 1 acceso antiguo.' : "Se borraron {$borrados} accesos antiguos."),
        );
    }
}
