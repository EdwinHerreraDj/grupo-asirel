<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use Illuminate\Http\Request;

class ClienteController extends Controller
{
    public function index(Request $request)
    {
        $query = Cliente::query();

        // Filtro de búsqueda
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nombre', 'LIKE', "%{$search}%")
                  ->orWhere('cif', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%")
                  ->orWhere('telefono', 'LIKE', "%{$search}%");
            });
        }

        // Filtro de activo
        if ($request->has('filtroActivo') && $request->filtroActivo !== '') {
            $query->where('activo', $request->filtroActivo);
        }

        // Paginación
        $clientes = $query->orderBy('nombre', 'asc')
                          ->paginate(10);

        return response()->json($clientes);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'cif' => 'nullable|string|max:20',
            'email' => 'required|email|max:255',
            'telefono' => 'required|string|max:20',
            'emails' => 'nullable|array',
            'emails.*' => 'nullable|email|max:255',
            'telefonos' => 'nullable|array',
            'telefonos.*' => 'nullable|string|max:20',
            'direccion' => 'nullable|string|max:500',
            'descripcion' => 'nullable|string|max:1000',
            'activo' => 'boolean',
        ]);

        $cliente = Cliente::create($validated);

        return response()->json([
            'message' => 'Cliente creado exitosamente',
            'cliente' => $cliente
        ], 201);
    }

    public function show($id)
    {
        $cliente = Cliente::findOrFail($id);
        return response()->json($cliente);
    }

    public function update(Request $request, $id)
    {
        $cliente = Cliente::findOrFail($id);

        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'cif' => 'nullable|string|max:20',
            'email' => 'required|email|max:255',
            'telefono' => 'required|string|max:20',
            'emails' => 'nullable|array',
            'emails.*' => 'nullable|email|max:255',
            'telefonos' => 'nullable|array',
            'telefonos.*' => 'nullable|string|max:20',
            'direccion' => 'nullable|string|max:500',
            'descripcion' => 'nullable|string|max:1000',
            'activo' => 'boolean',
        ]);

        $cliente->update($validated);

        return response()->json([
            'message' => 'Cliente actualizado exitosamente',
            'cliente' => $cliente
        ]);
    }

    public function destroy($id)
    {
        $cliente = Cliente::findOrFail($id);
        $cliente->delete();

        return response()->json([
            'message' => 'Cliente eliminado exitosamente'
        ]);
    }
}