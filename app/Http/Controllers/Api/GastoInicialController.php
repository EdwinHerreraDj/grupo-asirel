<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Obra;
use App\Models\GastoInicialPartida;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\ObraGastoCategoria;

class GastoInicialController extends Controller
{
    // -------------------------
    // GET /api/obras/{obra}/gastos-iniciales
    // -------------------------
    public function index(Obra $obra)
    {
        $oficios = $obra->categoriasGasto()
            ->orderByRaw("CAST(SUBSTRING_INDEX(nombre, '-', 1) AS UNSIGNED) ASC")
            ->orderBy('nombre')
            ->get();

        // Partidas agrupadas por oficio
        $partidas = GastoInicialPartida::where('obra_id', $obra->id)
            ->orderBy('orden')
            ->orderBy('id')
            ->get()
            ->groupBy('obra_gasto_categoria_id');

        $capitulos = $oficios->map(function ($oficio) use ($partidas) {
            $partidasOficio = $partidas->get($oficio->id, collect());

            return [
                'id'            => $oficio->id,
                'oficio_id'     => $oficio->id,
                'oficio_nombre' => $oficio->nombre,
                'importe_total' => $partidasOficio->sum('importe'),
                'partidas'      => $partidasOficio->map(fn($p) => $this->formatPartida($p))->values(),
            ];
        });

        return response()->json(['capitulos' => $capitulos]);
    }

    // -------------------------
    // POST /api/obras/{obra}/gastos-iniciales/partidas
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

        $orden = GastoInicialPartida::where('obra_id', $obra->id)
            ->where('obra_gasto_categoria_id', $request->oficio_id)
            ->count();

        GastoInicialPartida::create([
            'obra_id'                 => $obra->id,
            'obra_gasto_categoria_id' => $request->oficio_id,
            'codigo'                  => $request->codigo,
            'descripcion'             => $request->descripcion,
            'unidad'                  => $request->unidad,
            'medicion'                => $request->medicion,
            'precio_unitario'         => $request->precio_unitario,
            'orden'                   => $orden,
        ]);

        $partidas = $this->getPartidasOficio($obra->id, $request->oficio_id);

        return response()->json([
            'capitulo_id' => $request->oficio_id,
            'oficio_id'   => $request->oficio_id,
            'partidas'    => $partidas,
        ]);
    }

    // -------------------------
    // PUT /api/obras/{obra}/gastos-iniciales/partidas/{partida}
    // -------------------------
    public function updatePartida(Request $request, Obra $obra, GastoInicialPartida $partida)
    {
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

        $partidas = $this->getPartidasOficio($obra->id, $partida->obra_gasto_categoria_id);

        return response()->json([
            'capitulo_id' => $partida->obra_gasto_categoria_id,
            'oficio_id'   => $partida->obra_gasto_categoria_id,
            'partidas'    => $partidas,
        ]);
    }

    // -------------------------
    // DELETE /api/obras/{obra}/gastos-iniciales/partidas/{partida}
    // -------------------------
    public function destroyPartida(Obra $obra, GastoInicialPartida $partida)
    {
        $oficioId = $partida->obra_gasto_categoria_id;

        $partida->delete();

        $partidas = $this->getPartidasOficio($obra->id, $oficioId);

        return response()->json([
            'capitulo_id' => $oficioId,
            'oficio_id'   => $oficioId,
            'partidas'    => $partidas,
        ]);
    }

    // -------------------------
    // HELPERS
    // -------------------------
    private function getPartidasOficio(int $obraId, int $oficioId): array
    {
        return GastoInicialPartida::where('obra_id', $obraId)
            ->where('obra_gasto_categoria_id', $oficioId)
            ->orderBy('orden')
            ->orderBy('id')
            ->get()
            ->map(fn($p) => $this->formatPartida($p))
            ->toArray();
    }

    // -------------------------
    // POST /api/obras/{obra}/capitulos
    // -------------------------
    public function storeCapitulo(Request $request, Obra $obra)
    {
        $request->validate([
            'nombre'      => 'required|string|max:255',
            'descripcion' => 'nullable|string',
        ]);

        $capitulo = \App\Models\ObraGastoCategoria::create([
            'obra_id'     => $obra->id,
            'nombre'      => $request->nombre,
            'descripcion' => $request->descripcion,
        ]);

        return response()->json([
            'capitulo' => [
                'id'            => $capitulo->id,
                'oficio_id'     => $capitulo->id,
                'oficio_nombre' => $capitulo->nombre,
                'importe_total' => 0,
                'partidas'      => [],
            ],
        ]);
    }

    // -------------------------
    // PUT /api/obras/{obra}/capitulos/{capitulo}
    // -------------------------
    public function updateCapitulo(Request $request, Obra $obra, \App\Models\ObraGastoCategoria $capitulo)
    {
        $request->validate([
            'nombre'      => 'required|string|max:255',
            'descripcion' => 'nullable|string',
        ]);

        $capitulo->update([
            'nombre'      => $request->nombre,
            'descripcion' => $request->descripcion,
        ]);

        return response()->json([
            'capitulo' => [
                'id'            => $capitulo->id,
                'oficio_id'     => $capitulo->id,
                'oficio_nombre' => $capitulo->nombre,
                'descripcion'   => $capitulo->descripcion,
            ],
        ]);
    }

    // -------------------------
    // DELETE /api/obras/{obra}/capitulos/{capitulo}
    // -------------------------
    public function destroyCapitulo(Obra $obra, ObraGastoCategoria $capitulo)
    {
        // Bloquear si tiene partidas de coste
        $tieneCoste = GastoInicialPartida::where('obra_gasto_categoria_id', $capitulo->id)->exists();

        // Bloquear si tiene partidas de venta
        $tieneVenta = \App\Models\PresupuestoVentaPartida::where('obra_id', $obra->id)
            ->whereHas('capitulo', fn($q) => $q->where('obra_gasto_categoria_id', $capitulo->id))
            ->exists();

        // Bloquear si tiene certificaciones vinculadas
        $tieneCertificaciones = \App\Models\Certificacion::where('obra_gasto_categoria_id', $capitulo->id)
            ->exists();

        if ($tieneCoste || $tieneVenta || $tieneCertificaciones) {
            $motivo = match (true) {
                $tieneCertificaciones => 'tiene certificaciones asociadas',
                $tieneVenta           => 'tiene partidas de presupuesto de venta asociadas',
                $tieneCoste           => 'tiene partidas de coste asociadas',
            };

            return response()->json([
                'message' => "No se puede eliminar el capítulo porque {$motivo}.",
            ], 422);
        }

        $capitulo->delete();

        return response()->json(['deleted' => true]);
    }

    private function formatPartida(GastoInicialPartida $p): array
    {
        return [
            'id'              => $p->id,
            'codigo'          => $p->codigo,
            'descripcion'     => $p->descripcion,
            'unidad'          => $p->unidad,
            'medicion'        => (float) $p->medicion,
            'precio_unitario' => (float) $p->precio_unitario,
            'importe'         => (float) $p->importe,
            'orden'           => $p->orden,
        ];
    }
}
