<?php

namespace App\Services\Rrhh;

use App\Models\Empleado;
use App\Models\Folder;
use App\Models\RrhhTipoDocumento;
use Illuminate\Support\Collection;

/**
 * Carpetas de Recursos humanos en el Drive.
 *
 *   Trabajadores/                     (sistema = rrhh_activos)
 *     García López, Juan (12345678Z)/ (empleados.folder_id)
 *       DNI/                          (rrhh_tipo_documento_id)
 *       Contrato/
 *   Trabajadores de baja/             (sistema = rrhh_bajas)
 *
 * Nunca borra nada: solo crea, renombra y mueve carpetas.
 */
class CarpetasEmpleados
{
    public const SISTEMA_ACTIVOS = 'rrhh_activos';

    public const SISTEMA_BAJAS = 'rrhh_bajas';

    /** Subcarpetas internas de la carpeta del empleado (protegidas en el Drive). */
    public const SISTEMA_AUSENCIAS = 'rrhh_ausencias';

    public const SISTEMA_NOMINAS = 'rrhh_nominas';

    public const SISTEMA_ANTICIPOS = 'rrhh_anticipos';

    public const SISTEMA_FORMACION = 'rrhh_formacion';

    public const SISTEMA_SANCIONES = 'rrhh_sanciones';

    public const CARPETAS_INTERNAS = [
        self::SISTEMA_AUSENCIAS => 'Ausencias y bajas',
        self::SISTEMA_NOMINAS => 'Recibos de nómina',
        self::SISTEMA_ANTICIPOS => 'Anticipos y vales',
        self::SISTEMA_FORMACION => 'Cursos y formación',
        self::SISTEMA_SANCIONES => 'Sanciones',
    ];

    private const NOMBRES_SISTEMA = [
        self::SISTEMA_ACTIVOS => 'Trabajadores',
        self::SISTEMA_BAJAS => 'Trabajadores de baja',
    ];

    /**
     * Carpeta raíz de activos o bajas. Si ya existe una carpeta en la raíz con
     * ese nombre (creada a mano), se reutiliza.
     */
    public function carpetaSistema(string $clave): Folder
    {
        if ($carpeta = Folder::where('sistema', $clave)->first()) {
            return $carpeta;
        }

        $nombre = self::NOMBRES_SISTEMA[$clave];

        $existente = Folder::where('parent_id', 0)
            ->where('nombre', $nombre)
            ->whereNull('sistema')
            ->first();

        if ($existente) {
            $existente->update(['sistema' => $clave]);

            return $existente;
        }

        return Folder::create([
            'nombre' => $this->nombreUnico($nombre, 0),
            'parent_id' => 0,
            'tipo' => 1,
            'usuario_id' => auth()->id(),
            'sistema' => $clave,
        ]);
    }

    public function nombreCarpeta(Empleado $empleado): string
    {
        return mb_substr(
            trim($empleado->apellidos).', '.trim($empleado->nombre).' ('.$empleado->dni.')',
            0,
            150,
        );
    }

    /** Crea la carpeta del empleado si no existe y completa sus apartados. */
    public function asegurarCarpeta(Empleado $empleado): Folder
    {
        $carpeta = $empleado->folder_id ? Folder::find($empleado->folder_id) : null;

        if (! $carpeta) {
            $padre = $this->carpetaSistema($this->claveSegunEstado($empleado));

            $carpeta = Folder::create([
                'nombre' => $this->nombreUnico($this->nombreCarpeta($empleado), (int) $padre->id),
                'parent_id' => $padre->id,
                'tipo' => 1,
                'usuario_id' => auth()->id(),
            ]);

            $empleado->forceFill(['folder_id' => $carpeta->id])->saveQuietly();
        }

        $this->sincronizarApartados($carpeta);

        return $carpeta;
    }

    /**
     * Crea los apartados que falten. Si ya hay una subcarpeta con el nombre
     * del tipo (creada a mano), se reutiliza.
     *
     * @param  Collection<int, RrhhTipoDocumento>|null  $tipos  por defecto, los activos
     */
    public function sincronizarApartados(Folder $carpeta, ?Collection $tipos = null): void
    {
        $tipos ??= RrhhTipoDocumento::where('activo', true)->get();
        $hijas = Folder::where('parent_id', $carpeta->id)->get();

        foreach ($tipos as $tipo) {
            if ($hijas->contains(fn ($h) => (int) $h->rrhh_tipo_documento_id === (int) $tipo->id)) {
                continue;
            }

            $mismoNombre = $hijas->first(fn ($h) => $h->rrhh_tipo_documento_id === null
                && $h->sistema === null
                && mb_strtolower($h->nombre) === mb_strtolower($tipo->nombre));

            if ($mismoNombre) {
                $mismoNombre->update(['rrhh_tipo_documento_id' => $tipo->id]);

                continue;
            }

            $hijas->push(Folder::create([
                'nombre' => $this->nombreUnico($tipo->nombre, (int) $carpeta->id),
                'parent_id' => $carpeta->id,
                'tipo' => 1,
                'usuario_id' => auth()->id(),
                'rrhh_tipo_documento_id' => $tipo->id,
            ]));
        }
    }

    /**
     * Subcarpeta interna del empleado ("Ausencias y bajas", "Recibos de
     * nómina"…), creada la primera vez que se necesita.
     */
    public function carpetaInterna(Empleado $empleado, string $clave): Folder
    {
        $carpeta = $this->asegurarCarpeta($empleado);

        $existente = Folder::where('parent_id', $carpeta->id)
            ->where('sistema', $clave)
            ->first();

        return $existente ?? Folder::create([
            'nombre' => $this->nombreUnico(self::CARPETAS_INTERNAS[$clave], (int) $carpeta->id),
            'parent_id' => $carpeta->id,
            'tipo' => 1,
            'usuario_id' => auth()->id(),
            'sistema' => $clave,
        ]);
    }

    /** Añade un tipo nuevo (o reactivado) a las carpetas de todos los empleados. */
    public function sincronizarTipoEnTodos(RrhhTipoDocumento $tipo): void
    {
        Empleado::whereNotNull('folder_id')
            ->with('carpeta')
            ->chunkById(100, function ($empleados) use ($tipo) {
                foreach ($empleados as $empleado) {
                    if ($empleado->carpeta) {
                        $this->sincronizarApartados($empleado->carpeta, collect([$tipo]));
                    }
                }
            });
    }

    /** Si cambia el nombre del tipo, cambia el de sus apartados. */
    public function renombrarApartados(RrhhTipoDocumento $tipo): void
    {
        Folder::where('rrhh_tipo_documento_id', $tipo->id)
            ->where('nombre', '!=', $tipo->nombre)
            ->get()
            ->each(fn (Folder $f) => $f->update([
                'nombre' => $this->nombreUnico($tipo->nombre, (int) $f->parent_id, (int) $f->id),
            ]));
    }

    /** Si cambian nombre, apellidos o DNI, cambia el nombre de su carpeta. */
    public function renombrarCarpeta(Empleado $empleado): void
    {
        $carpeta = $empleado->folder_id ? Folder::find($empleado->folder_id) : null;

        if (! $carpeta) {
            return;
        }

        $nombre = $this->nombreCarpeta($empleado);

        if ($carpeta->nombre !== $nombre) {
            $carpeta->update([
                'nombre' => $this->nombreUnico($nombre, (int) $carpeta->parent_id, (int) $carpeta->id),
            ]);
        }
    }

    /** Baja → "Trabajadores de baja"; reingreso → "Trabajadores". */
    public function moverSegunEstado(Empleado $empleado): void
    {
        $carpeta = $empleado->folder_id ? Folder::find($empleado->folder_id) : null;

        if (! $carpeta) {
            $this->asegurarCarpeta($empleado);

            return;
        }

        $destino = $this->carpetaSistema($this->claveSegunEstado($empleado));

        if ((int) $carpeta->parent_id !== (int) $destino->id) {
            $carpeta->update([
                'parent_id' => $destino->id,
                'nombre' => $this->nombreUnico($carpeta->nombre, (int) $destino->id, (int) $carpeta->id),
            ]);
        }
    }

    private function claveSegunEstado(Empleado $empleado): string
    {
        return $empleado->estado === Empleado::ESTADO_BAJA ? self::SISTEMA_BAJAS : self::SISTEMA_ACTIVOS;
    }

    private function nombreUnico(string $base, int $parentId, ?int $ignorarId = null): string
    {
        $base = mb_substr($base, 0, 150);
        $nombre = $base;
        $contador = 1;

        while (Folder::where('parent_id', $parentId)
            ->where('nombre', $nombre)
            ->when($ignorarId, fn ($q) => $q->where('id', '!=', $ignorarId))
            ->exists()) {
            $nombre = mb_substr($base, 0, 140)." ({$contador})";
            $contador++;
        }

        return $nombre;
    }
}
