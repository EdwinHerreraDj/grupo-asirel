<?php

namespace App\Http\Controllers\Api\Certificaciones;

use App\Http\Controllers\Controller;
use App\Models\FacturaSerie;
use App\Models\Obra;
use App\Services\FacturaVentaGenerator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

class CertificacionFacturarController extends Controller
{
    public function __construct(
        private readonly FacturaVentaGenerator $generator,
    ) {}

    // -------------------------
    // GET /api/obras/{obra}/certificaciones/facturables
    // -------------------------
    public function facturables(Obra $obra): JsonResponse
    {
        return response()->json([
            'grupos' => $this->generator->obtenerFacturables($obra),
            'series' => FacturaSerie::where('activa', true)
                ->orderBy('serie')
                ->get(['id', 'serie']),
        ]);
    }

    // -------------------------
    // POST /api/obras/{obra}/certificaciones/facturar
    // -------------------------
    public function facturar(Request $request, Obra $obra): JsonResponse
    {
        $data = $request->validate([
            'numero_certificacion' => 'required|string',
            'serie'                => 'required|string|exists:factura_series,serie',
        ]);

        $serie = FacturaSerie::where('serie', $data['serie'])->firstOrFail();

        try {
            $factura = $this->generator->emitirDesdeCertificaciones(
                $obra,
                $data['numero_certificacion'],
                $serie,
            );
        } catch (RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        } catch (\Throwable $e) {
            report($e);

            return response()->json(['message' => 'Error al emitir la factura.'], 500);
        }

        return response()->json([
            'factura_id'   => $factura->id,
            'redirect_url' => route('empresa.facturas-ventas.detalle', $factura->id),
        ]);
    }
}
