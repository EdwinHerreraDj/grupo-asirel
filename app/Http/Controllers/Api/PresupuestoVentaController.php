<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Obra;
use App\Models\ObraPresupuestoVenta;
use App\Models\PresupuestoVentaPartida;
use App\Models\GastoInicialPartida;
use Illuminate\Http\Request;

class PresupuestoVentaController extends Controller
{
    // -------------------------
    // GET /api/obras/{obra}/presupuesto-venta
    // -------------------------
    public function index(Obra $obra)
    {
        // Coste teórico por oficio
        $costePorOficio = GastoInicialPartida::where('obra_id', $obra->id)
            ->selectRaw('obra_gasto_categoria_id, SUM(importe) as total_coste')
            ->groupBy('obra_gasto_categoria_id')
            ->pluck('total_coste', 'obra_gasto_categoria_id');

        $capitulos = ObraPresupuestoVenta::where('obra_id', $obra->id)
            ->with(['oficio', 'partidas' => fn($q) => $q->orderBy('orden')->orderBy('id')])
            ->get()
            ->map(fn($cap) => [
                'id'            => $cap->id,
                'oficio_id'     => $cap->obra_gasto_categoria_id,
                'oficio_nombre' => $cap->oficio->nombre ?? '—',
                'importe_total' => (float) $cap->importe_total,
                'coste_total'   => (float) ($costePorOficio[$cap->obra_gasto_categoria_id] ?? 0),
                'partidas'      => $cap->partidas->map(fn($p) => $this->formatPartida($p)),
            ]);

        $oficiosConCapitulo = $capitulos->pluck('oficio_id')->toArray();

        $oficiosSinCapitulo = $obra->categoriasGasto()
            ->whereNotIn('obra_gasto_categorias.id', $oficiosConCapitulo)
            ->orderByRaw("CAST(SUBSTRING_INDEX(nombre, '-', 1) AS UNSIGNED) ASC")
            ->orderBy('nombre')
            ->get()
            ->map(fn($oficio) => [
                'id'            => null,
                'oficio_id'     => $oficio->id,
                'oficio_nombre' => $oficio->nombre,
                'importe_total' => 0,
                'coste_total'   => (float) ($costePorOficio[$oficio->id] ?? 0),
                'partidas'      => [],
            ]);

        $todos = $capitulos->concat($oficiosSinCapitulo)
            ->sortBy('oficio_nombre')
            ->values();

        // Detectar si hay partidas de coste sin sincronizar
        $totalCostePartidas = GastoInicialPartida::where('obra_id', $obra->id)->count();
        $totalVentaPartidas = PresupuestoVentaPartida::where('obra_id', $obra->id)->count();
        $pendientesSincronizar = $totalCostePartidas > $totalVentaPartidas;

        // Indicador "N de M partidas de venta con v\u00ednculo a coste"
        $vinculadasACoste = PresupuestoVentaPartida::where('obra_id', $obra->id)
            ->whereNotNull('coste_partida_id')
            ->count();

        return response()->json([
            'capitulos'              => $todos,
            'pendientes_sincronizar' => $pendientesSincronizar,
            'indicador'              => [
                'vinculadas_a_coste' => $vinculadasACoste,
                'total_venta'        => $totalVentaPartidas,
            ],
        ]);
    }


    // -------------------------
    // POST /api/obras/{obra}/presupuesto-venta/partidas
    // -------------------------
    public function storePartida(Request $request, Obra $obra)
    {
        $request->validate([
            'oficio_id'       => 'required|integer|exists:obra_gasto_categorias,id',
            'codigo'          => 'nullable|string|max:50',
            'descripcion'     => 'required|string|max:500',
            'unidad'          => 'nullable|string|max:50',
            'medicion'        => 'required|numeric|min:0',
            'precio_unitario' => 'required|numeric|min:0',
        ]);

        // Crear o recuperar el capítulo
        $capitulo = ObraPresupuestoVenta::firstOrCreate(
            [
                'obra_id'                 => $obra->id,
                'obra_gasto_categoria_id' => $request->oficio_id,
            ],
            ['importe_total' => 0]
        );

        $orden = $capitulo->partidas()->count();

        PresupuestoVentaPartida::create([
            'obra_presupuesto_venta_id' => $capitulo->id,
            'obra_id'                   => $obra->id,
            'codigo'                    => $request->codigo,
            'descripcion'               => $request->descripcion,
            'unidad'                    => $request->unidad,
            'medicion'                  => $request->medicion,
            'precio_unitario'           => $request->precio_unitario,
            'orden'                     => $orden,
        ]);

        // Recargar partidas actualizadas
        $capitulo->refresh();
        $partidas = $capitulo->partidas()
            ->orderBy('orden')
            ->orderBy('id')
            ->get()
            ->map(fn($p) => $this->formatPartida($p));

        return response()->json([
            'capitulo_id'  => $capitulo->id,
            'oficio_id'    => $capitulo->obra_gasto_categoria_id,
            'partidas'     => $partidas,
        ]);
    }

    // -------------------------
    // PUT /api/obras/{obra}/presupuesto-venta/partidas/{partida}
    // -------------------------
    public function updatePartida(Request $request, Obra $obra, PresupuestoVentaPartida $partida)
    {
        // Si tiene coste_partida_id solo permitir medicion y precio
        if ($partida->coste_partida_id) {
            $request->validate([
                'medicion'        => 'required|numeric|min:0',
                'precio_unitario' => 'required|numeric|min:0',
            ]);

            $partida->update($request->only(['medicion', 'precio_unitario']));
        } else {
            $request->validate([
                'codigo'          => 'nullable|string|max:50',
                'descripcion'     => 'required|string|max:500',
                'unidad'          => 'nullable|string|max:50',
                'medicion'        => 'required|numeric|min:0',
                'precio_unitario' => 'required|numeric|min:0',
            ]);

            $partida->update($request->only([
                'codigo',
                'descripcion',
                'unidad',
                'medicion',
                'precio_unitario',
            ]));
        }

        $capitulo = $partida->capitulo;
        $partidas = $capitulo->partidas()
            ->orderBy('orden')->orderBy('id')
            ->get()
            ->map(fn($p) => $this->formatPartida($p));

        return response()->json([
            'capitulo_id' => $capitulo->id,
            'oficio_id'   => $capitulo->obra_gasto_categoria_id,
            'partidas'    => $partidas,
        ]);
    }

    // -------------------------
    // DELETE /api/obras/{obra}/presupuesto-venta/partidas/{partida}
    // -------------------------
    public function destroyPartida(Obra $obra, PresupuestoVentaPartida $partida)
    {
        $capitulo = $partida->capitulo;

        $partida->delete();

        $partidas = $capitulo->partidas()
            ->orderBy('orden')
            ->orderBy('id')
            ->get()
            ->map(fn($p) => $this->formatPartida($p));

        return response()->json([
            'capitulo_id' => $capitulo->id,
            'oficio_id'   => $capitulo->obra_gasto_categoria_id, // ← añadir
            'partidas'    => $partidas,
        ]);
    }

    // -------------------------
    // POST /api/obras/{obra}/presupuesto-venta/sincronizar
    // Copia partidas de coste que no existen aún en venta
    // -------------------------
    public function sincronizar(Obra $obra)
    {
        $partidasCoste = GastoInicialPartida::where('obra_id', $obra->id)
            ->get();

        $sincronizadas = 0;

        foreach ($partidasCoste as $partida) {
            // Buscar o crear el capítulo de venta
            $capitulo = ObraPresupuestoVenta::firstOrCreate(
                [
                    'obra_id'                 => $obra->id,
                    'obra_gasto_categoria_id' => $partida->obra_gasto_categoria_id,
                ],
                ['importe_total' => 0]
            );

            // Solo crear si no existe ya una partida de venta vinculada a esta de coste
            $existe = PresupuestoVentaPartida::where('coste_partida_id', $partida->id)->exists();

            if (!$existe) {
                PresupuestoVentaPartida::create([
                    'obra_presupuesto_venta_id' => $capitulo->id,
                    'obra_id'                   => $obra->id,
                    'coste_partida_id'  => $partida->id,
                    'codigo'                    => $partida->codigo,
                    'descripcion'               => $partida->descripcion,
                    'unidad'                    => $partida->unidad,
                    'medicion'                  => $partida->medicion,
                    'precio_unitario'           => $partida->precio_unitario,
                    'orden'                     => $partida->orden,
                ]);

                $sincronizadas++;
            }
        }

        // Devolver datos actualizados
        return $this->index($obra);
    }

    // -------------------------
    // POST /api/obras/{obra}/presupuesto-venta/incrementar
    // Aplica incremento porcentual a partidas de un capítulo o a toda la obra
    // -------------------------
    public function incrementar(Request $request, Obra $obra)
    {
        $request->validate([
            'porcentaje' => 'required|numeric|min:-100|max:1000',
            'oficio_id'  => 'nullable|integer|exists:obra_gasto_categorias,id',
        ]);

        $porcentaje = $request->porcentaje;
        $factor     = 1 + ($porcentaje / 100);

        $query = PresupuestoVentaPartida::where('obra_id', $obra->id);

        // Si viene oficio_id → solo ese capítulo
        if ($request->oficio_id) {
            $query->whereHas(
                'capitulo',
                fn($q) =>
                $q->where('obra_gasto_categoria_id', $request->oficio_id)
            );
        }

        $partidas = $query->get();

        foreach ($partidas as $partida) {
            $partida->update([
                'precio_unitario' => round($partida->precio_unitario * $factor, 4),
            ]);
        }

        // Devolver datos actualizados
        return $this->index($obra);
    }
    // POST /api/obras/{obra}/presupuesto-venta/restablecer
    public function restablecer(Request $request, Obra $obra)
    {
        $request->validate([
            'oficio_id' => 'nullable|integer|exists:obra_gasto_categorias,id',
        ]);

        $query = PresupuestoVentaPartida::where('obra_id', $obra->id)
            ->whereNotNull('coste_partida_id')
            ->with('costePartida');

        if ($request->oficio_id) {
            $query->whereHas(
                'capitulo',
                fn($q) =>
                $q->where('obra_gasto_categoria_id', $request->oficio_id)
            );
        }

        foreach ($query->get() as $partida) {
            $partida->update([
                'medicion'        => $partida->costePartida->medicion,
                'precio_unitario' => $partida->costePartida->precio_unitario,
            ]);
        }

        return $this->index($obra);
    }

    // -------------------------
    // HELPER
    // -------------------------

    // Actualizar formatPartida para incluir coste_partida_id
    private function formatPartida(PresupuestoVentaPartida $p): array
    {
        return [
            'id'                       => $p->id,
            'coste_partida_id' => $p->coste_partida_id,
            'codigo'                   => $p->codigo,
            'descripcion'              => $p->descripcion,
            'unidad'                   => $p->unidad,
            'medicion'                 => (float) $p->medicion,
            'precio_unitario'          => (float) $p->precio_unitario,
            'importe'                  => (float) $p->importe,
            'orden'                    => $p->orden,
        ];
    }
}
