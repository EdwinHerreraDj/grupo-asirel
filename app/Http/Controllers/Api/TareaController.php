<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Obra;
use App\Models\Tarea;
use App\Models\User;
use Illuminate\Http\Request;

class TareaController extends Controller
{
    private function baseRules(): array
    {
        return [
            'titulo'       => 'required|string|max:255',
            'descripcion'  => 'nullable|string|max:2000',
            'prioridad'    => 'required|in:baja,media,alta',
            'estado'       => 'required|in:pendiente,en_curso,completada',
            'fecha_limite' => 'nullable|date',
            'obra_id'      => 'nullable|integer|exists:obras,id',
            'asignado_a'   => 'required|integer|exists:users,id',
        ];
    }

    private function aplicarFiltros($query, Request $request, int $userId, bool $isAdmin)
    {
        $vista = $request->input('vista', 'mis_tareas');

        if ($vista === 'mis_tareas') {
            $query->where('asignado_a', $userId);
        } elseif ($vista === 'creadas_por_mi') {
            $query->where('creado_por', $userId);
        } elseif ($vista === 'todas') {
            if (! $isAdmin) {
                // Usuario normal solo ve las suyas o creadas por él
                $query->where(function ($q) use ($userId) {
                    $q->where('asignado_a', $userId)
                        ->orWhere('creado_por', $userId);
                });
            }
        }

        if ($request->filled('search')) {
            $term = $request->input('search');
            $query->where(function ($q) use ($term) {
                $q->where('titulo', 'like', "%{$term}%")
                    ->orWhere('descripcion', 'like', "%{$term}%");
            });
        }

        if ($request->filled('prioridad')) {
            $query->where('prioridad', $request->input('prioridad'));
        }

        if ($request->filled('asignado_a')) {
            $query->where('asignado_a', $request->input('asignado_a'));
        }

        if ($request->filled('obra_id')) {
            $query->where('obra_id', $request->input('obra_id'));
        }

        if ($request->filled('fecha_desde')) {
            $query->whereDate('fecha_limite', '>=', $request->input('fecha_desde'));
        }

        if ($request->filled('fecha_hasta')) {
            $query->whereDate('fecha_limite', '<=', $request->input('fecha_hasta'));
        }

        return $query;
    }

    public function index(Request $request)
    {
        $user = $request->user();
        $isAdmin = in_array($user->role ?? '', ['admin', 'super_admin']);

        $query = Tarea::with(['obra:id,nombre', 'asignadoA:id,name', 'creadoPor:id,name']);

        $this->aplicarFiltros($query, $request, $user->id, $isAdmin);

        $tareas = $query
            ->orderByRaw("CASE estado WHEN 'pendiente' THEN 0 WHEN 'en_curso' THEN 1 WHEN 'completada' THEN 2 END")
            ->orderByRaw('CASE prioridad WHEN "alta" THEN 0 WHEN "media" THEN 1 WHEN "baja" THEN 2 END')
            ->orderByRaw('fecha_limite IS NULL, fecha_limite ASC')
            ->orderByDesc('id')
            ->get();

        // Stats sobre el conjunto filtrado
        $stats = [
            'total'       => $tareas->count(),
            'pendiente'   => $tareas->where('estado', 'pendiente')->count(),
            'en_curso'    => $tareas->where('estado', 'en_curso')->count(),
            'completada'  => $tareas->where('estado', 'completada')->count(),
            'vencidas'    => $tareas->filter(fn ($t) => $t->vencida)->count(),
        ];

        return response()->json([
            'tareas'   => $tareas,
            'stats'    => $stats,
            'usuarios' => User::orderBy('name')->get(['id', 'name']),
            'obras'    => Obra::orderBy('nombre')->get(['id', 'nombre']),
            'meta'     => [
                'user_id'  => $user->id,
                'is_admin' => $isAdmin,
            ],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->baseRules());

        $tarea = Tarea::create([
            ...$validated,
            'creado_por' => $request->user()->id,
        ]);

        $tarea->load(['obra:id,nombre', 'asignadoA:id,name', 'creadoPor:id,name']);

        return response()->json([
            'message' => 'Tarea creada correctamente.',
            'tarea'   => $tarea,
        ], 201);
    }

    public function show(Tarea $tarea)
    {
        $tarea->load(['obra:id,nombre', 'asignadoA:id,name', 'creadoPor:id,name']);

        return response()->json($tarea);
    }

    public function update(Request $request, Tarea $tarea)
    {
        $validated = $request->validate($this->baseRules());

        $tarea->update($validated);
        $tarea->load(['obra:id,nombre', 'asignadoA:id,name', 'creadoPor:id,name']);

        return response()->json([
            'message' => 'Tarea actualizada correctamente.',
            'tarea'   => $tarea,
        ]);
    }

    public function cambiarEstado(Request $request, Tarea $tarea)
    {
        $validated = $request->validate([
            'estado' => 'required|in:pendiente,en_curso,completada',
        ]);

        $tarea->update(['estado' => $validated['estado']]);
        $tarea->load(['obra:id,nombre', 'asignadoA:id,name', 'creadoPor:id,name']);

        return response()->json([
            'message' => 'Estado actualizado.',
            'tarea'   => $tarea,
        ]);
    }

    public function destroy(Request $request, Tarea $tarea)
    {
        $user = $request->user();
        $isAdmin = in_array($user->role ?? '', ['admin', 'super_admin']);

        // Solo el creador o admin puede eliminar
        if (! $isAdmin && $tarea->creado_por !== $user->id) {
            return response()->json([
                'message' => 'No tienes permiso para eliminar esta tarea.',
            ], 403);
        }

        $tarea->delete();

        return response()->json([
            'message' => 'Tarea eliminada correctamente.',
        ]);
    }
}
