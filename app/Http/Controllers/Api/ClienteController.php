<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;

class ClienteController extends Controller
{
    private function baseRules(): array
    {
        return [
            'nombre'               => 'required|string|max:255',
            'cif'                  => 'nullable|string|max:20',
            'email'                => 'nullable|email|max:255',
            'telefono'             => 'nullable|string|max:20',
            'emails'               => 'nullable|array',
            'emails.*'             => 'nullable|email|max:255',
            'telefonos'            => 'nullable|array',
            'telefonos.*.numero'   => 'required|string|max:20',
            'telefonos.*.etiqueta' => 'nullable|string|max:100',
            'direccion'            => 'nullable|string|max:500',
            'codigo_postal'        => 'nullable|string|max:20',
            'poblacion'            => 'nullable|string|max:150',
            'provincia'            => 'nullable|string|max:150',
            'pais'                 => 'nullable|string|max:100',
            'descripcion'          => 'nullable|string|max:1000',
            'activo'               => 'boolean',
        ];
    }

    public function index(Request $request)
    {
        $query = Cliente::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nombre', 'LIKE', "%{$search}%")
                    ->orWhere('cif', 'LIKE', "%{$search}%")
                    ->orWhere('email', 'LIKE', "%{$search}%")
                    ->orWhere('telefono', 'LIKE', "%{$search}%");
            });
        }

        if ($request->has('filtroActivo') && $request->filtroActivo !== '') {
            $query->where('activo', $request->filtroActivo);
        }

        $clientes = $query->orderBy('nombre', 'asc')->paginate(10);

        $statsBase = Cliente::query();
        $stats = [
            'total'     => (clone $statsBase)->count(),
            'activos'   => (clone $statsBase)->where('activo', true)->count(),
            'inactivos' => (clone $statsBase)->where('activo', false)->count(),
        ];

        $payload = $clientes->toArray();
        $payload['stats'] = $stats;

        return response()->json($payload);
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->baseRules());

        $cliente = Cliente::create($validated);

        return response()->json([
            'message' => 'Cliente creado exitosamente',
            'cliente' => $cliente,
        ], 201);
    }

    public function show($id)
    {
        return response()->json(Cliente::findOrFail($id));
    }

    public function update(Request $request, $id)
    {
        $cliente = Cliente::findOrFail($id);
        $validated = $request->validate($this->baseRules());

        $cliente->update($validated);

        return response()->json([
            'message' => 'Cliente actualizado exitosamente',
            'cliente' => $cliente,
        ]);
    }

    public function destroy(Cliente $cliente)
    {
        try {
            $cliente->delete();

            return response()->json([
                'message' => 'Cliente eliminado correctamente',
            ], 200);
        } catch (\Throwable $e) {
            if ($e instanceof QueryException) {
                if (($e->errorInfo[1] ?? null) === 1451) {
                    return response()->json([
                        'message' => 'No se puede eliminar el cliente porque está siendo utilizado en certificaciones u otros documentos.',
                    ], 409);
                }
            }

            return response()->json([
                'message' => 'Error al eliminar el cliente.',
            ], 500);
        }
    }
}
