<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Proveedor;
use Illuminate\Http\Request;

class ProveedorController extends Controller
{
    private const TIPOS_VALIDOS = 'material,mano_obra,servicio,mixto';

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
            'telefonos.*.etiqueta' => 'nullable|string|max:50',
            'direccion'            => 'nullable|string|max:500',
            'codigo_postal'        => 'nullable|string|max:20',
            'poblacion'            => 'nullable|string|max:150',
            'provincia'            => 'nullable|string|max:150',
            'pais'                 => 'nullable|string|max:100',
            'tipo'                 => 'nullable|in:' . self::TIPOS_VALIDOS,
            'activo'               => 'boolean',
        ];
    }

    public function index(Request $request)
    {
        $query = Proveedor::query();

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

        if ($request->filled('filtroTipo')) {
            $query->where('tipo', $request->filtroTipo);
        }

        $proveedores = $query->orderBy('nombre', 'asc')->paginate(10);

        // Stats globales (no filtrados por paginación)
        $statsBase = Proveedor::query();
        $stats = [
            'total'    => (clone $statsBase)->count(),
            'activos'  => (clone $statsBase)->where('activo', true)->count(),
            'por_tipo' => [
                'material'  => (clone $statsBase)->where('tipo', 'material')->count(),
                'mano_obra' => (clone $statsBase)->where('tipo', 'mano_obra')->count(),
                'servicio'  => (clone $statsBase)->where('tipo', 'servicio')->count(),
                'mixto'     => (clone $statsBase)->where('tipo', 'mixto')->count(),
            ],
        ];

        $payload = $proveedores->toArray();
        $payload['stats'] = $stats;

        return response()->json($payload);
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->baseRules());

        $proveedor = Proveedor::create($validated);

        return response()->json([
            'message'   => 'Proveedor creado exitosamente',
            'proveedor' => $proveedor,
        ], 201);
    }

    public function show($id)
    {
        return response()->json(Proveedor::findOrFail($id));
    }

    public function update(Request $request, $id)
    {
        $proveedor = Proveedor::findOrFail($id);
        $validated = $request->validate($this->baseRules());

        $proveedor->update($validated);

        return response()->json([
            'message'   => 'Proveedor actualizado exitosamente',
            'proveedor' => $proveedor,
        ]);
    }

    public function destroy($id)
    {
        $proveedor = Proveedor::findOrFail($id);

        if ($proveedor->facturas()->count() > 0) {
            return response()->json([
                'message' => 'No se puede eliminar el proveedor porque tiene facturas asociadas.',
            ], 422);
        }

        $proveedor->delete();

        return response()->json([
            'message' => 'Proveedor eliminado exitosamente.',
        ]);
    }
}
