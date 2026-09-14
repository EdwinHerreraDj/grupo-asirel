<?php

namespace App\Http\Controllers\Api\Drive;

use App\Http\Controllers\Controller;
use App\Models\DriveEliminacion;
use App\Models\File;
use App\Models\Folder;
use App\Services\Drive\DriveStorage;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use RuntimeException;
use Throwable;
use ZipArchive;

class FileController extends Controller
{
    /** Límites al extraer ZIP (evita ZIP bombs y extracciones enormes). */
    private const ZIP_MAX_ENTRADAS = 2000;

    private const ZIP_MAX_BYTES = 524288000; // 500 MB descomprimido

    /** Restos de sistema que no se importan desde un ZIP. */
    private const ZIP_IGNORAR = ['__MACOSX', '.DS_Store', 'Thumbs.db', 'desktop.ini'];

    public function __construct(
        private readonly DriveStorage $storage,
    ) {}

    public function store(Request $request)
    {
        $validated = $request->validate([
            'file' => 'required|file|max:51200', // 50MB
            'folder_id' => ['required', 'integer', 'min:1', Rule::exists('folders', 'id')],
            'tiene_caducidad' => 'nullable|boolean',
            'fecha_caducidad' => 'nullable|date|after_or_equal:today',
        ], [
            'folder_id.min' => 'No se pueden subir archivos en la carpeta raíz.',
            'folder_id.exists' => 'La carpeta de destino no existe.',
        ]);

        $archivo = $request->file('file');
        $tieneCaducidad = (bool) ($validated['tiene_caducidad'] ?? false);

        $guardado = $this->storage->guardarSubida($archivo);

        try {
            $fileRecord = File::create([
                'folder_id' => (int) $validated['folder_id'],
                'usuario_id' => auth()->id(),
                'nombre' => $archivo->getClientOriginalName(),
                'ruta' => $guardado['ruta'],
                'disco' => $guardado['disco'],
                'tipo' => $archivo->getMimeType(),
                'tamaño' => $archivo->getSize(),
                'tiene_caducidad' => $tieneCaducidad,
                'fecha_caducidad' => $tieneCaducidad ? ($validated['fecha_caducidad'] ?? null) : null,
            ]);
        } catch (Throwable $e) {
            // Sin registro en BD no debe quedar el fichero huérfano.
            Storage::disk($guardado['disco'])->delete($guardado['ruta']);
            throw $e;
        }

        return response()->json([
            'message' => 'Archivo subido exitosamente',
            'file' => $fileRecord,
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $file = File::findOrFail($id);

        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
        ]);

        $file->update(['nombre' => $validated['nombre']]);

        return response()->json([
            'message' => 'Archivo renombrado exitosamente',
            'file' => $file,
        ]);
    }

    public function destroy($id)
    {
        $file = File::with('folder')->findOrFail($id);

        DB::transaction(function () use ($file) {
            DriveEliminacion::create([
                'user_id' => auth()->id(),
                'tipo' => 'archivo',
                'nombre' => $file->nombre,
                'ubicacion' => $file->folder?->rutaCompleta() ?? 'Inicio',
                'carpetas' => 0,
                'archivos' => 1,
            ]);

            $file->delete();
        });

        try {
            $this->storage->borrar($file);
        } catch (Throwable $e) {
            Log::warning('Drive: no se pudo borrar el fichero físico', [
                'file_id' => $file->id,
                'ruta' => $file->ruta,
                'error' => $e->getMessage(),
            ]);
        }

        return response()->json([
            'message' => 'Archivo eliminado exitosamente',
        ]);
    }

    public function download($id)
    {
        $file = File::findOrFail($id);

        if (! $this->storage->existe($file)) {
            Log::error('Drive: fichero no encontrado', ['file_id' => $file->id, 'ruta' => $file->ruta]);

            return response()->json([
                'message' => 'Archivo no encontrado en el servidor',
            ], 404);
        }

        return $this->storage->descargar($file);
    }

    public function move(Request $request, $id)
    {
        $validated = $request->validate([
            'target_folder_id' => 'required|integer|exists:folders,id',
        ]);

        $file = File::findOrFail($id);
        $targetFolderId = (int) $validated['target_folder_id'];

        if ((int) $file->folder_id === $targetFolderId) {
            return response()->json([
                'message' => 'El archivo ya se encuentra en esta carpeta.',
            ], 422);
        }

        $wasRenamed = File::where('folder_id', $targetFolderId)
            ->where('nombre', $file->nombre)
            ->where('id', '!=', $file->id)
            ->exists();

        if ($wasRenamed) {
            $file->nombre = $this->getUniqueFileName($file->nombre, $targetFolderId);
        }

        $file->folder_id = $targetFolderId;
        $file->save();

        return response()->json([
            'message' => 'Archivo movido exitosamente',
            'file' => $file,
            'was_renamed' => $wasRenamed,
        ]);
    }

    /**
     * Extrae un ZIP en la carpeta donde está.
     *
     * Si el ZIP contiene una única carpeta raíz (lo habitual al comprimir una
     * carpeta), se usa esa carpeta como base: evita el duplicado "X/X/...".
     */
    public function extract(Request $request, $id)
    {
        $file = File::findOrFail($id);

        if (strtolower(pathinfo($file->nombre, PATHINFO_EXTENSION)) !== 'zip') {
            return response()->json(['message' => 'Solo se pueden extraer archivos ZIP'], 422);
        }

        if (! $this->storage->existe($file)) {
            return response()->json(['message' => 'Archivo ZIP no encontrado'], 404);
        }

        $zip = new ZipArchive;

        if ($zip->open($this->storage->rutaAbsoluta($file)) !== true) {
            return response()->json(['message' => 'No se pudo abrir el archivo ZIP'], 422);
        }

        if ($problema = $this->validarContenidoZip($zip)) {
            $zip->close();

            return response()->json(['message' => $problema], 422);
        }

        $temporal = storage_path('app'.DIRECTORY_SEPARATOR.'temp'.DIRECTORY_SEPARATOR.'zip_'.Str::uuid());
        $guardados = [];

        try {
            mkdir($temporal, 0755, true);

            if (! $zip->extractTo($temporal)) {
                throw new RuntimeException('No se pudo extraer el contenido del ZIP.');
            }

            $zip->close();
            $zip = null;

            [$origen, $nombreBase] = $this->raizDelContenido(
                $temporal,
                $this->nombreSeguro(pathinfo($file->nombre, PATHINFO_FILENAME)),
            );

            $resultado = DB::transaction(function () use ($file, $origen, $nombreBase, &$guardados) {
                $base = Folder::create([
                    'nombre' => $this->nombreCarpetaUnico($nombreBase, (int) $file->folder_id),
                    'parent_id' => (int) $file->folder_id,
                    'tipo' => 1,
                    'usuario_id' => auth()->id(),
                ]);

                return [
                    'base' => $base,
                    'stats' => $this->importarDirectorio($origen, (int) $base->id, $guardados),
                ];
            });

            Log::info('ZIP extraído exitosamente', [
                'file' => $file->nombre,
                'carpetas_creadas' => $resultado['stats']['folders'],
                'archivos_creados' => $resultado['stats']['files'],
            ]);

            return response()->json([
                'message' => 'ZIP extraído exitosamente',
                'stats' => $resultado['stats'],
                'base_folder_id' => $resultado['base']->id,
            ]);
        } catch (Throwable $e) {
            // La BD ya se ha revertido: borrar los ficheros copiados.
            foreach ($guardados as $guardado) {
                Storage::disk($guardado['disco'])->delete($guardado['ruta']);
            }

            Log::error('Error extrayendo ZIP', [
                'file' => $file->nombre,
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
            ]);

            return response()->json([
                'message' => 'Error al extraer el archivo ZIP: '.$e->getMessage(),
            ], 500);
        } finally {
            if ($zip instanceof ZipArchive) {
                @$zip->close();
            }

            $this->borrarDirectorio($temporal);
        }
    }

    public function expiringFiles(Request $request)
    {
        $days = min(max((int) $request->get('days', 30), 1), 365);
        $hoy = today();

        $files = File::where('tiene_caducidad', true)
            ->whereNotNull('fecha_caducidad')
            ->whereBetween('fecha_caducidad', [
                now()->subYears(1)->toDateString(),
                now()->addDays($days)->toDateString(),
            ])
            ->with('folder')
            ->orderBy('fecha_caducidad', 'asc')
            ->get()
            ->map(function ($file) use ($hoy) {
                $file->estado_caducidad = Carbon::parse($file->fecha_caducidad)->lt($hoy)
                    ? 'vencido'
                    : 'proximo';

                return $file;
            });

        return response()->json([
            'files' => $files,
            'total' => $files->count(),
        ]);
    }

    // -------------------------------------------------------------
    // Internos
    // -------------------------------------------------------------

    private function getUniqueFileName(string $baseName, int $folderId): string
    {
        $name = $baseName;
        $counter = 1;
        $pathInfo = pathinfo($baseName);
        $sinExtension = $pathInfo['filename'];
        $extension = isset($pathInfo['extension']) ? '.'.$pathInfo['extension'] : '';

        while (File::where('folder_id', $folderId)->where('nombre', $name)->exists()) {
            $name = $sinExtension." ({$counter})".$extension;
            $counter++;
        }

        return $name;
    }

    private function nombreCarpetaUnico(string $baseName, int $parentId): string
    {
        $baseName = mb_substr($baseName !== '' ? $baseName : 'Carpeta', 0, 150);
        $name = $baseName;
        $counter = 1;

        while (Folder::where('parent_id', $parentId)->where('nombre', $name)->exists()) {
            $name = mb_substr($baseName, 0, 140)." ({$counter})";
            $counter++;
        }

        return $name;
    }

    private function validarContenidoZip(ZipArchive $zip): ?string
    {
        if ($zip->numFiles > self::ZIP_MAX_ENTRADAS) {
            return 'El ZIP tiene demasiados elementos (máximo '.self::ZIP_MAX_ENTRADAS.').';
        }

        $total = 0;

        for ($i = 0; $i < $zip->numFiles; $i++) {
            $stat = $zip->statIndex($i);

            if ($stat === false) {
                return 'El archivo ZIP está dañado.';
            }

            $nombre = str_replace('\\', '/', $stat['name']);

            if (str_starts_with($nombre, '/') || preg_match('#(^|/)\.\.(/|$)#', $nombre) || preg_match('#^[A-Za-z]:#', $nombre)) {
                return 'El ZIP contiene rutas no válidas.';
            }

            $total += (int) $stat['size'];

            if ($total > self::ZIP_MAX_BYTES) {
                return 'El contenido del ZIP es demasiado grande (máximo 500 MB descomprimido).';
            }
        }

        return null;
    }

    /**
     * Si el contenido extraído es una única carpeta, se toma como raíz.
     *
     * @return array{0: string, 1: string} [directorio origen, nombre de la carpeta base]
     */
    private function raizDelContenido(string $directorio, string $nombreZip): array
    {
        $elementos = array_values(array_filter(
            scandir($directorio) ?: [],
            fn ($e) => $e !== '.' && $e !== '..' && ! $this->ignorar($e),
        ));

        if (count($elementos) === 1 && is_dir($directorio.DIRECTORY_SEPARATOR.$elementos[0])) {
            return [$directorio.DIRECTORY_SEPARATOR.$elementos[0], $this->nombreSeguro($elementos[0])];
        }

        return [$directorio, $nombreZip];
    }

    /** @return array{folders: int, files: int} */
    private function importarDirectorio(string $directorio, int $parentId, array &$guardados): array
    {
        $stats = ['folders' => 0, 'files' => 0];

        foreach (scandir($directorio) ?: [] as $elemento) {
            if ($elemento === '.' || $elemento === '..' || $this->ignorar($elemento)) {
                continue;
            }

            $ruta = $directorio.DIRECTORY_SEPARATOR.$elemento;
            $nombre = $this->nombreSeguro($elemento);

            if (is_link($ruta)) {
                continue;
            }

            if (is_dir($ruta)) {
                $carpeta = Folder::create([
                    'nombre' => $this->nombreCarpetaUnico($nombre, $parentId),
                    'parent_id' => $parentId,
                    'tipo' => 1,
                    'usuario_id' => auth()->id(),
                ]);

                $stats['folders']++;
                $sub = $this->importarDirectorio($ruta, (int) $carpeta->id, $guardados);
                $stats['folders'] += $sub['folders'];
                $stats['files'] += $sub['files'];

                continue;
            }

            if (is_file($ruta)) {
                $guardado = $this->storage->guardarDesdeRuta($ruta, $nombre);
                $guardados[] = $guardado;

                File::create([
                    'folder_id' => $parentId,
                    'usuario_id' => auth()->id(),
                    'nombre' => mb_substr($nombre, 0, 255),
                    'ruta' => $guardado['ruta'],
                    'disco' => $guardado['disco'],
                    'tipo' => mime_content_type($ruta) ?: 'application/octet-stream',
                    'tamaño' => filesize($ruta),
                    'tiene_caducidad' => false,
                ]);

                $stats['files']++;
            }
        }

        return $stats;
    }

    private function ignorar(string $elemento): bool
    {
        return in_array($elemento, self::ZIP_IGNORAR, true) || str_starts_with($elemento, '._');
    }

    /** Los ZIP de Windows pueden traer nombres en otra codificación. */
    private function nombreSeguro(string $nombre): string
    {
        if (! mb_check_encoding($nombre, 'UTF-8')) {
            $nombre = mb_convert_encoding($nombre, 'UTF-8', 'CP850');
        }

        return trim(str_replace(['/', '\\'], '-', $nombre));
    }

    private function borrarDirectorio(string $directorio): void
    {
        if (! is_dir($directorio)) {
            return;
        }

        foreach (scandir($directorio) ?: [] as $elemento) {
            if ($elemento === '.' || $elemento === '..') {
                continue;
            }

            $ruta = $directorio.DIRECTORY_SEPARATOR.$elemento;

            if (is_dir($ruta) && ! is_link($ruta)) {
                $this->borrarDirectorio($ruta);
            } else {
                @unlink($ruta);
            }
        }

        @rmdir($directorio);
    }
}
