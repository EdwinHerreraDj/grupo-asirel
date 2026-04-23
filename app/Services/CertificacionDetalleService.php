<?php

namespace App\Services;

use App\Exceptions\CantidadExcedePendienteException;
use App\Models\Certificacion;
use App\Models\CertificacionDetalle;
use App\Models\CertificacionEvento;
use App\Models\PresupuestoVentaPartida;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Operaciones de edici\u00f3n de l\u00edneas de certificaci\u00f3n y transiciones de estado.
 *
 * Garantiza:
 *   - estado editable antes de cualquier mutaci\u00f3n,
 *   - mutaci\u00f3n + recalculo fiscal dentro de la misma transacci\u00f3n con lock,
 *   - validaci\u00f3n de pendiente contratado (bloqueo suave: forzable con confirmaci\u00f3n),
 *   - registro de evento de auditor\u00eda en cada transici\u00f3n de estado.
 */
class CertificacionDetalleService
{
    public function __construct(
        private readonly CertificacionCalculator $calculator,
    ) {}

    public function crear(
        Certificacion $certificacion,
        array $data,
        bool $forzar = false,
    ): CertificacionDetalle {
        return DB::transaction(function () use ($certificacion, $data, $forzar) {
            $cert = Certificacion::where('id', $certificacion->id)
                ->lockForUpdate()
                ->firstOrFail();

            $this->asegurarEditable($cert);

            $partida = PresupuestoVentaPartida::findOrFail(
                $data['presupuesto_venta_partida_id']
            );

            $cantidad = (float) $data['cantidad'];

            if (! $forzar) {
                $this->verificarNoExcedePendiente($cert, $partida, $cantidad);
            }

            $precio = (float) $partida->precio_unitario;

            $linea = CertificacionDetalle::create([
                'certificacion_id'             => $cert->id,
                'presupuesto_venta_partida_id' => $partida->id,
                'concepto'                     => $partida->descripcion,
                'unidad'                       => $partida->unidad,
                'cantidad'                     => $cantidad,
                'precio_unitario'              => $precio,
                'importe_linea'                => round($cantidad * $precio, 2),
            ]);

            $this->calculator->recalcular($cert->fresh());

            return $linea;
        });
    }

    public function actualizar(
        Certificacion $certificacion,
        CertificacionDetalle $detalle,
        array $data,
        bool $forzar = false,
    ): CertificacionDetalle {
        return DB::transaction(function () use ($certificacion, $detalle, $data, $forzar) {
            $cert = Certificacion::where('id', $certificacion->id)
                ->lockForUpdate()
                ->firstOrFail();

            $this->asegurarEditable($cert);

            $cantidad = (float) $data['cantidad'];
            $precio = (float) $data['precio_unitario'];

            if (! $forzar && $detalle->presupuesto_venta_partida_id) {
                $partida = PresupuestoVentaPartida::findOrFail(
                    $detalle->presupuesto_venta_partida_id
                );
                $this->verificarNoExcedePendiente($cert, $partida, $cantidad, $detalle);
            }

            $detalle->update([
                'concepto'        => $data['concepto'],
                'unidad'          => $data['unidad'],
                'cantidad'        => $cantidad,
                'precio_unitario' => $precio,
                'importe_linea'   => round($cantidad * $precio, 2),
            ]);

            $this->calculator->recalcular($cert->fresh());

            return $detalle->fresh();
        });
    }

    public function eliminar(
        Certificacion $certificacion,
        CertificacionDetalle $detalle,
    ): void {
        DB::transaction(function () use ($certificacion, $detalle) {
            $cert = Certificacion::where('id', $certificacion->id)
                ->lockForUpdate()
                ->firstOrFail();

            $this->asegurarEditable($cert);

            $detalle->delete();

            $this->calculator->recalcular($cert->fresh());
        });
    }

    /**
     * Aplica iva/retenci\u00f3n a todas las certificaciones de un mismo
     * numero_certificacion. Invariante AI_CONTEXT: mismos porcentajes
     * fiscales dentro de un n\u00famero.
     */
    public function aplicarImpuestos(
        Certificacion $certificacion,
        float $ivaPorcentaje,
        float $retencionPorcentaje,
    ): void {
        DB::transaction(function () use ($certificacion, $ivaPorcentaje, $retencionPorcentaje) {
            $certificaciones = Certificacion::where(
                'numero_certificacion',
                $certificacion->numero_certificacion,
            )
                ->where('obra_id', $certificacion->obra_id)
                ->lockForUpdate()
                ->get();

            foreach ($certificaciones as $cert) {
                $this->asegurarEditable($cert);
            }

            foreach ($certificaciones as $cert) {
                $cert->update([
                    'iva_porcentaje'       => $ivaPorcentaje,
                    'retencion_porcentaje' => $retencionPorcentaje,
                ]);

                $this->calculator->recalcular($cert->fresh());
            }
        });
    }

    public function aceptar(Certificacion $certificacion): void
    {
        DB::transaction(function () use ($certificacion) {
            $cert = Certificacion::where('id', $certificacion->id)
                ->lockForUpdate()
                ->firstOrFail();

            if (! $cert->estaPendiente()) {
                throw new RuntimeException('La certificación ya está aceptada.');
            }

            if (! $cert->detalles()->exists()) {
                throw new RuntimeException(
                    'No puedes aceptar una certificación sin líneas.'
                );
            }

            $cert->update(['estado_certificacion' => 'aceptada']);

            $this->registrarEvento($cert, 'aceptada', 'pendiente', 'aceptada');
        });
    }

    public function anular(Certificacion $certificacion, ?string $motivo = null): void
    {
        DB::transaction(function () use ($certificacion, $motivo) {
            $cert = Certificacion::where('id', $certificacion->id)
                ->lockForUpdate()
                ->firstOrFail();

            if (! $cert->puedeAnular()) {
                throw new RuntimeException(
                    'No se puede anular esta certificación en su estado actual.'
                );
            }

            $cert->update(['estado_certificacion' => 'pendiente']);

            $this->registrarEvento($cert, 'anulada', 'aceptada', 'pendiente', $motivo);
        });
    }

    public function registrarCreacion(Certificacion $certificacion): void
    {
        $this->registrarEvento($certificacion, 'creada', null, 'pendiente');
    }

    // -------------------------------------------------------------
    // INTERNOS
    // -------------------------------------------------------------

    private function asegurarEditable(Certificacion $cert): void
    {
        if (! $cert->puedeEditar()) {
            throw new RuntimeException(
                'La certificación no admite modificaciones en su estado actual.'
            );
        }
    }

    /**
     * Suma la cantidad ya certificada para una partida en el \u00e1mbito
     * (obra + oficio) de la certificaci\u00f3n, excluyendo opcionalmente un
     * detalle (al actualizar, se excluye s\u00ed mismo).
     */
    private function verificarNoExcedePendiente(
        Certificacion $cert,
        PresupuestoVentaPartida $partida,
        float $cantidadIntentada,
        ?CertificacionDetalle $excluir = null,
    ): void {
        $scope = fn ($q) => $q->where('obra_id', $cert->obra_id)
            ->where('obra_gasto_categoria_id', $cert->obra_gasto_categoria_id);

        $query = CertificacionDetalle::whereHas('certificacion', $scope)
            ->where('presupuesto_venta_partida_id', $partida->id);

        if ($excluir) {
            $query->where('id', '!=', $excluir->id);
        }

        $acumulado = (float) $query->sum('cantidad');
        $pendiente = round((float) $partida->medicion - $acumulado, 4);

        if ($cantidadIntentada > $pendiente) {
            throw new CantidadExcedePendienteException(
                pendiente: $pendiente,
                cantidadIntentada: $cantidadIntentada,
            );
        }
    }

    private function registrarEvento(
        Certificacion $cert,
        string $tipo,
        ?string $estadoPrevio,
        ?string $estadoNuevo,
        ?string $motivo = null,
    ): void {
        CertificacionEvento::create([
            'certificacion_id' => $cert->id,
            'user_id'          => Auth::id(),
            'tipo'             => $tipo,
            'estado_previo'    => $estadoPrevio,
            'estado_nuevo'     => $estadoNuevo,
            'motivo'           => $motivo,
        ]);
    }
}
