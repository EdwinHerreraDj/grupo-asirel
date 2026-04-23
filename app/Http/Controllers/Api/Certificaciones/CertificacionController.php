<?php

namespace App\Http\Controllers\Api\Certificaciones;

use App\Http\Controllers\Controller;
use App\Models\Certificacion;
use App\Models\Obra;
use App\Models\ObraGastoCategoria;
use App\Models\Cliente;
use App\Services\CertificacionDetalleService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CertificacionController extends Controller
{
    public function __construct(
        private readonly CertificacionDetalleService $service,
    ) {}

    // -------------------------
    // GET /api/obras/{obra}/certificaciones
    // -------------------------
    public function index(Request $request, Obra $obra)
    {
        $query = Certificacion::where('obra_id', $obra->id)
            ->with(['oficio', 'cliente']);

        if ($request->search) {
            $query->where('numero_certificacion', 'like', "%{$request->search}%");
        }

        if ($request->oficio_id) {
            $query->where('obra_gasto_categoria_id', $request->oficio_id);
        }

        if ($request->cliente_id) {
            $query->where('cliente_id', $request->cliente_id);
        }

        if ($request->estado_certificacion) {
            $query->where('estado_certificacion', $request->estado_certificacion);
        }

        if ($request->fecha_desde) {
            $query->whereDate('fecha_ingreso', '>=', $request->fecha_desde);
        }

        if ($request->fecha_hasta) {
            $query->whereDate('fecha_ingreso', '<=', $request->fecha_hasta);
        }

        $certificaciones = $query
            ->orderByRaw("COALESCE(numero_certificacion, '') ASC")
            ->orderBy('fecha_ingreso', 'desc')
            ->orderBy('obra_gasto_categoria_id')
            ->paginate(10);

        return response()->json([
            'data'       => $certificaciones->map(fn($c) => $this->formatCertificacion($c)),
            'total'      => $certificaciones->total(),
            'page'       => $certificaciones->currentPage(),
            'last_page'  => $certificaciones->lastPage(),
            'oficios'    => ObraGastoCategoria::where('obra_id', $obra->id)
                ->orderBy('nombre')->get(['id', 'nombre']),
            'clientes'   => Cliente::orderBy('nombre')->get(['id', 'nombre']),
        ]);
    }

    // -------------------------
    // POST /api/obras/{obra}/certificaciones
    // -------------------------
    public function store(Request $request, Obra $obra)
    {
        $request->validate([
            'cliente_id'              => 'required|exists:clientes,id',
            'obra_gasto_categoria_id' => 'required|exists:obra_gasto_categorias,id',
            'fecha_ingreso'           => 'required|date',
            'fecha_contable'          => 'nullable|date',
            'fecha_vencimiento'       => 'nullable|date',
            'iva_porcentaje'          => 'nullable|numeric|min:0',
            'retencion_porcentaje'    => 'nullable|numeric|min:0',
            'numero_certificacion'    => 'nullable|string|max:255',
        ]);

        $cert = Certificacion::create([
            'obra_id'                 => $obra->id,
            'cliente_id'              => $request->cliente_id,
            'obra_gasto_categoria_id' => $request->obra_gasto_categoria_id,
            'fecha_ingreso'           => $request->fecha_ingreso,
            'fecha_contable'          => $request->fecha_contable,
            'fecha_vencimiento'       => $request->fecha_vencimiento,
            'numero_certificacion'    => $request->numero_certificacion,
            'iva_porcentaje'          => $request->iva_porcentaje ?? 21,
            'retencion_porcentaje'    => $request->retencion_porcentaje ?? 0,
            'base_imponible'          => 0,
            'iva_importe'             => 0,
            'retencion_importe'       => 0,
            'total'                   => 0,
            'estado_certificacion'    => 'pendiente',
            'estado_factura'          => 'pendiente',
        ]);

        $this->service->registrarCreacion($cert);

        return response()->json([
            'certificacion' => $this->formatCertificacion($cert->load(['oficio', 'cliente'])),
        ], 201);
    }

    // -------------------------
    // POST /api/obras/{obra}/certificaciones/capitulo
    // Nuevo capítulo dentro de un numero_certificacion existente
    // -------------------------
    public function storeCapitulo(Request $request, Obra $obra)
    {
        $request->validate([
            'certificacion_id'        => 'required|exists:certificaciones,id',
            'obra_gasto_categoria_id' => 'required|exists:obra_gasto_categorias,id',
        ]);

        $certBase = Certificacion::findOrFail($request->certificacion_id);

        if ($certBase->estaFacturada()) {
            return response()->json([
                'message' => 'Esta certificación ya está facturada.',
            ], 422);
        }

        $existe = Certificacion::where('numero_certificacion', $certBase->numero_certificacion)
            ->where('obra_gasto_categoria_id', $request->obra_gasto_categoria_id)
            ->exists();

        if ($existe) {
            return response()->json([
                'message' => 'Ya existe un capítulo con este oficio en la certificación.',
            ], 422);
        }

        $cert = Certificacion::create([
            'obra_id'                 => $certBase->obra_id,
            'cliente_id'              => $certBase->cliente_id,
            'numero_certificacion'    => $certBase->numero_certificacion,
            'obra_gasto_categoria_id' => $request->obra_gasto_categoria_id,
            'fecha_ingreso'           => now(),
            'fecha_contable'          => $certBase->fecha_contable,
            'fecha_vencimiento'       => $certBase->fecha_vencimiento,
            'iva_porcentaje'          => $certBase->iva_porcentaje,
            'retencion_porcentaje'    => $certBase->retencion_porcentaje,
            'base_imponible'          => 0,
            'iva_importe'             => 0,
            'retencion_importe'       => 0,
            'total'                   => 0,
            'estado_certificacion'    => 'pendiente',
            'estado_factura'          => 'pendiente',
        ]);

        $this->service->registrarCreacion($cert);

        return response()->json([
            'certificacion' => $this->formatCertificacion($cert->load(['oficio', 'cliente'])),
        ], 201);
    }

    // -------------------------
    // DELETE /api/certificaciones/{certificacion}
    // -------------------------
    public function destroy(Certificacion $certificacion)
    {
        if (! $certificacion->puedeEliminar()) {
            return response()->json([
                'message' => 'No se puede eliminar una certificación facturada.',
            ], 422);
        }

        if (
            $certificacion->adjunto_url &&
            Storage::disk('public')->exists($certificacion->adjunto_url)
        ) {
            Storage::disk('public')->delete($certificacion->adjunto_url);
        }

        $certificacion->delete();

        return response()->json(['deleted' => true]);
    }

    // -------------------------
    // HELPER
    // -------------------------
    private function formatCertificacion(Certificacion $c): array
    {
        return [
            'id'                      => $c->id,
            'numero_certificacion'    => $c->numero_certificacion,
            'oficio_id'               => $c->obra_gasto_categoria_id,
            'oficio_nombre'           => $c->oficio->nombre ?? '—',
            'cliente_id'              => $c->cliente_id,
            'cliente_nombre'          => $c->cliente->nombre ?? '—',
            'fecha_ingreso'           => $c->fecha_ingreso,
            'fecha_contable'          => $c->fecha_contable,
            'fecha_vencimiento'       => $c->fecha_vencimiento,
            'base_imponible'          => (float) $c->base_imponible,
            'iva_porcentaje'          => (float) $c->iva_porcentaje,
            'iva_importe'             => (float) $c->iva_importe,
            'retencion_porcentaje'    => (float) $c->retencion_porcentaje,
            'retencion_importe'       => (float) $c->retencion_importe,
            'total'                   => (float) $c->total,
            'estado_certificacion'    => $c->estado_certificacion,
            'estado_factura'          => $c->estado_factura,
            'factura_venta_id'        => $c->factura_venta_id ?? null,
        ];
    }
}
