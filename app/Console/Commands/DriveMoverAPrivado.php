<?php

namespace App\Console\Commands;

use App\Models\File;
use App\Services\Drive\DriveStorage;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

/**
 * Mueve los archivos del Drive que están en el disco público (accesibles por
 * URL sin sesión) al disco privado.
 *
 * Solo toca archivos registrados en la tabla `files`. Para cada uno: copia al
 * disco privado, comprueba el tamaño, actualiza el registro y, al final, borra
 * la copia pública. Se puede ejecutar varias veces (lo ya movido se salta).
 *
 * Uso:
 *   php artisan drive:mover-a-privado --dry-run   (solo informe, no cambia nada)
 *   php artisan drive:mover-a-privado
 */
class DriveMoverAPrivado extends Command
{
    protected $signature = 'drive:mover-a-privado {--dry-run : Muestra qué se movería sin cambiar nada}';

    protected $description = 'Mueve los archivos del Drive del disco público al privado';

    public function handle(DriveStorage $storage): int
    {
        $simular = (bool) $this->option('dry-run');
        $publico = Storage::disk('public');
        $stats = ['a_mover' => 0, 'movidos' => 0, 'sin_fichero' => 0, 'errores' => 0];

        $consulta = File::query()->where(function ($q) {
            $q->where('disco', 'public')
                ->orWhere(fn ($q2) => $q2->whereNull('disco')->where('ruta', 'like', 'uploads/%'));
        });

        $total = (clone $consulta)->count();
        $this->info(($simular ? '[SIMULACIÓN] ' : '')."Archivos del Drive en el disco público: {$total}");

        $consulta->orderBy('id')->chunkById(200, function ($files) use ($storage, $publico, $simular, &$stats) {
            foreach ($files as $file) {
                if (! $publico->exists($file->ruta)) {
                    $stats['sin_fichero']++;
                    $this->warn("  Sin fichero físico: #{$file->id} {$file->nombre} ({$file->ruta})");
                    continue;
                }

                $stats['a_mover']++;

                if ($simular) {
                    continue;
                }

                $nuevo = null;

                try {
                    $nuevo = $storage->guardarDesdeRuta($publico->path($file->ruta), $file->nombre);

                    if (Storage::disk($nuevo['disco'])->size($nuevo['ruta']) !== $publico->size($file->ruta)) {
                        throw new RuntimeException('El tamaño de la copia no coincide.');
                    }

                    $rutaPublica = $file->ruta;
                    $file->forceFill(['ruta' => $nuevo['ruta'], 'disco' => $nuevo['disco']])->save();
                    $nuevo = null; // ya es la copia buena: no borrarla si falla lo siguiente

                    $publico->delete($rutaPublica);
                    $stats['movidos']++;
                } catch (Throwable $e) {
                    if ($nuevo) {
                        Storage::disk($nuevo['disco'])->delete($nuevo['ruta']);
                    }
                    $stats['errores']++;
                    $this->error("  Error en #{$file->id} {$file->nombre}: {$e->getMessage()}");
                }
            }
        });

        $this->table(
            ['A mover', 'Movidos', 'Sin fichero físico', 'Errores'],
            [[$stats['a_mover'], $simular ? '— (simulación)' : $stats['movidos'], $stats['sin_fichero'], $stats['errores']]],
        );

        if ($simular) {
            $this->info('Simulación terminada: no se ha cambiado nada.');
        }

        return $stats['errores'] > 0 ? self::FAILURE : self::SUCCESS;
    }
}
