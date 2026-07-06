<?php

namespace App\Console\Commands;

use App\Models\Certificacion;
use App\Models\CertificacionEvento;
use App\Models\FacturaVenta;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Corrige certificaciones que quedaron bloqueadas en 'facturada' porque su
 * factura fue anulada ANTES de que existiera la liberación automática
 * (ver App\Services\FacturaVentaService::liberarCertificaciones).
 *
 * Solo libera una certificación si TODAS las facturas que la enlazan están
 * anuladas: si alguna factura viva la mantiene facturada, se respeta.
 *
 * Uso:
 *   php artisan certificaciones:liberar-bloqueadas --dry-run   (solo informe)
 *   php artisan certificaciones:liberar-bloqueadas             (aplica cambios)
 */
class LiberarCertificacionesBloqueadas extends Command
{
    protected $signature = 'certificaciones:liberar-bloqueadas {--dry-run : Muestra qué se liberaría sin escribir nada}';

    protected $description = 'Libera certificaciones bloqueadas por facturas ya anuladas (corrección puntual)';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');

        // El enlace factura↔certificación es por obra_id + (codigo_certificacion
        // = numero_certificacion), no por la tabla pivote (que no se rellena).
        //
        // Certificaciones facturadas que tienen alguna factura de su grupo,
        // pero cuyas facturas están TODAS anuladas.
        $certs = Certificacion::where('estado_factura', 'facturada')
            ->whereExists(function ($q) {
                $q->select(DB::raw(1))
                    ->from('facturas_venta as fv')
                    ->where('fv.origen', 'certificacion')
                    ->whereColumn('fv.obra_id', 'certificaciones.obra_id')
                    ->whereColumn('fv.codigo_certificacion', 'certificaciones.numero_certificacion');
            })
            ->whereNotExists(function ($q) {
                // No debe existir NINGUNA factura viva (no anulada) en el grupo.
                $q->select(DB::raw(1))
                    ->from('facturas_venta as fv')
                    ->where('fv.origen', 'certificacion')
                    ->whereColumn('fv.obra_id', 'certificaciones.obra_id')
                    ->whereColumn('fv.codigo_certificacion', 'certificaciones.numero_certificacion')
                    ->where('fv.estado', '!=', FacturaVenta::ESTADO_ANULADA);
            })
            ->get();

        if ($certs->isEmpty()) {
            $this->info('No hay certificaciones bloqueadas por facturas anuladas. Nada que hacer.');

            return self::SUCCESS;
        }

        $this->warn(($dryRun ? '[DRY-RUN] ' : '') . "Certificaciones a liberar: {$certs->count()}");
        $this->table(
            ['ID', 'Nº cert.', 'Obra', 'Estado cert.', 'Estado factura'],
            $certs->map(fn ($c) => [
                $c->id,
                $c->numero_certificacion,
                $c->obra_id,
                $c->estado_certificacion,
                $c->estado_factura,
            ])->all()
        );

        if ($dryRun) {
            $this->info('Dry-run: no se ha modificado nada.');

            return self::SUCCESS;
        }

        if (! $this->confirm('¿Liberar estas certificaciones a estado_factura = pendiente?', true)) {
            $this->info('Cancelado.');

            return self::SUCCESS;
        }

        $ahora = now();
        $liberadas = 0;

        DB::transaction(function () use ($certs, $ahora, &$liberadas) {
            foreach ($certs as $cert) {
                $fresco = Certificacion::where('id', $cert->id)
                    ->where('estado_factura', 'facturada')
                    ->lockForUpdate()
                    ->first();

                if (! $fresco) {
                    continue;
                }

                $estadoCertPrevio = $fresco->estado_certificacion;

                $fresco->update([
                    'estado_factura'       => 'pendiente',
                    'estado_certificacion' => 'pendiente',
                ]);

                CertificacionEvento::create([
                    'certificacion_id' => $fresco->id,
                    'user_id'          => null,
                    'tipo'             => 'factura_anulada',
                    'estado_previo'    => $estadoCertPrevio,
                    'estado_nuevo'     => 'pendiente',
                    'motivo'           => 'Corrección: factura anulada sin liberar (comando certificaciones:liberar-bloqueadas)',
                    'created_at'       => $ahora,
                    'updated_at'       => $ahora,
                ]);

                $liberadas++;
            }
        });

        $this->info("Certificaciones liberadas: {$liberadas}");

        return self::SUCCESS;
    }
}
