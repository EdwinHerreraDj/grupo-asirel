<?php

namespace App\Services\Rrhh;

use App\Models\File;
use App\Models\Folder;
use App\Models\RrhhTipoDocumento;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Estado de la documentación de los empleados, por apartado (tipo de
 * documento activo). Se calcula con pocas consultas para cualquier número de
 * empleados.
 *
 * Estados:
 *  - falta:      obligatorio y sin archivos
 *  - vacio:      opcional y sin archivos
 *  - entregado:  hay archivos y el tipo no caduca
 *  - sin_fecha:  el tipo caduca pero ningún archivo tiene fecha de caducidad
 *  - vencido:    la fecha de caducidad más reciente ya pasó
 *  - proximo:    caduca dentro de los días de aviso del tipo
 *  - vigente:    caduca más adelante
 */
class EstadoDocumentacion
{
    public const FALTA = 'falta';

    public const VACIO = 'vacio';

    public const ENTREGADO = 'entregado';

    public const SIN_FECHA = 'sin_fecha';

    public const VENCIDO = 'vencido';

    public const PROXIMO = 'proximo';

    public const VIGENTE = 'vigente';

    /** Estados que requieren atención. */
    public const PROBLEMAS = [self::FALTA, self::VENCIDO, self::PROXIMO, self::SIN_FECHA];

    /**
     * @param  Collection  $empleados  empleados con folder_id
     * @return array<int, array<int, array>> empleado_id => apartados
     */
    public function paraEmpleados(Collection $empleados, bool $conArchivos = false): array
    {
        $tipos = RrhhTipoDocumento::where('activo', true)
            ->orderBy('orden')
            ->orderBy('nombre')
            ->get();

        $carpetaIds = $empleados->pluck('folder_id')->filter()->values();

        $apartados = $carpetaIds->isEmpty()
            ? collect()
            : Folder::whereIn('parent_id', $carpetaIds)
                ->whereNotNull('rrhh_tipo_documento_id')
                ->get(['id', 'parent_id', 'rrhh_tipo_documento_id'])
                ->keyBy(fn ($a) => $a->parent_id.'-'.$a->rrhh_tipo_documento_id);

        $archivos = collect();
        if ($apartados->isNotEmpty()) {
            $consulta = File::whereIn('folder_id', $apartados->pluck('id'))->orderByDesc('created_at');
            if ($conArchivos) {
                $consulta->with('usuario:id,name');
            }
            $archivos = $consulta->get()->groupBy('folder_id');
        }

        $hoy = today();
        $resultado = [];

        foreach ($empleados as $empleado) {
            $lista = [];

            foreach ($tipos as $tipo) {
                $apartado = $apartados->get($empleado->folder_id.'-'.$tipo->id);
                $files = $apartado ? ($archivos->get($apartado->id) ?? collect()) : collect();

                [$estado, $caduca, $dias] = $this->evaluar($tipo, $files, $hoy);

                $item = [
                    'tipo_id' => $tipo->id,
                    'tipo' => $tipo->nombre,
                    'obligatorio' => $tipo->obligatorio,
                    'requiere_caducidad' => $tipo->requiere_caducidad,
                    'dias_aviso' => $tipo->dias_aviso,
                    'folder_id' => $apartado?->id,
                    'estado' => $estado,
                    'caduca' => $caduca,
                    'dias' => $dias,
                    'archivos_count' => $files->count(),
                ];

                if ($conArchivos) {
                    $item['archivos'] = $files->values();
                }

                $lista[] = $item;
            }

            $resultado[$empleado->id] = $lista;
        }

        return $resultado;
    }

    /** Contadores de problemas de una lista de apartados. */
    public static function resumen(array $apartados): array
    {
        $resumen = ['faltan' => 0, 'vencidos' => 0, 'proximos' => 0, 'sin_fecha' => 0];

        foreach ($apartados as $apartado) {
            match ($apartado['estado']) {
                self::FALTA => $resumen['faltan']++,
                self::VENCIDO => $resumen['vencidos']++,
                self::PROXIMO => $resumen['proximos']++,
                self::SIN_FECHA => $resumen['sin_fecha']++,
                default => null,
            };
        }

        $resumen['total'] = array_sum($resumen);

        return $resumen;
    }

    /** @return array{0: string, 1: ?string, 2: ?int} [estado, fecha de caducidad, días que faltan] */
    private function evaluar(RrhhTipoDocumento $tipo, Collection $files, Carbon $hoy): array
    {
        if ($files->isEmpty()) {
            return [$tipo->obligatorio ? self::FALTA : self::VACIO, null, null];
        }

        if (! $tipo->requiere_caducidad) {
            return [self::ENTREGADO, null, null];
        }

        $fecha = $files->pluck('fecha_caducidad')
            ->filter()
            ->map(fn ($f) => Carbon::parse($f)->startOfDay())
            ->max();

        if (! $fecha) {
            return [self::SIN_FECHA, null, null];
        }

        $dias = (int) $hoy->diffInDays($fecha, false);
        $estado = $dias < 0 ? self::VENCIDO : ($dias <= $tipo->dias_aviso ? self::PROXIMO : self::VIGENTE);

        return [$estado, $fecha->toDateString(), $dias];
    }
}
