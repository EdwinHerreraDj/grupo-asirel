<?php
// app/Http/Controllers/Api/ProveedorController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Proveedor;
use Illuminate\Http\Request;

class ProveedorController extends Controller
{
    public function index(Request $request)
    {
        $query = Proveedor::query();

        // Filtro de búsqueda
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
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

        // Filtro de tipo
        if ($request->has('filtroTipo') && $request->filtroTipo !== '') {
            $query->where('tipo', $request->filtroTipo);
        }

        // Paginación
        $proveedores = $query->orderBy('nombre', 'asc')
            ->paginate(10);

        return response()->json($proveedores);
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
            'telefonos.*.numero' => 'required|string|max:20',
            'telefonos.*.etiqueta' => 'nullable|string|max:50',
            'direccion' => 'nullable|string|max:500',
            'tipo' => 'required|in:servicios,productos,mixto',
            'activo' => 'boolean',
        ]);

        $proveedor = Proveedor::create($validated);

        return response()->json([
            'message' => 'Proveedor creado exitosamente',
            'proveedor' => $proveedor
        ], 201);
    }

    public function show($id)
    {
        $proveedor = Proveedor::findOrFail($id);
        return response()->json($proveedor);
    }

    public function update(Request $request, $id)
    {
        $proveedor = Proveedor::findOrFail($id);

        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'cif' => 'nullable|string|max:20',
            'email' => 'required|email|max:255',
            'telefono' => 'required|string|max:20',
            'emails' => 'nullable|array',
            'emails.*' => 'nullable|email|max:255',
            'telefonos' => 'nullable|array',
            'telefonos.*.numero' => 'required|string|max:20',
            'telefonos.*.etiqueta' => 'nullable|string|max:50',
            'direccion' => 'nullable|string|max:500',
            'tipo' => 'required|in:servicios,productos,mixto',
            'activo' => 'boolean',
        ]);

        $proveedor->update($validated);

        return response()->json([
            'message' => 'Proveedor actualizado exitosamente',
            'proveedor' => $proveedor
        ]);
    }

    public function destroy($id)
    {
        $proveedor = Proveedor::findOrFail($id);

        // Verificar si tiene facturas asociadas
        if ($proveedor->facturas()->count() > 0) {
            return response()->json([
                'message' => 'No se puede eliminar el proveedor porque tiene facturas asociadas'
            ], 422);
        }

        $proveedor->delete();

        return response()->json([
            'message' => 'Proveedor eliminado exitosamente'
        ]);
    }
}
