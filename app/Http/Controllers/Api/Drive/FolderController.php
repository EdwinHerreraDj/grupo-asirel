<?php

namespace App\Http\Controllers\Api\Drive;

use App\Http\Controllers\Controller;
use App\Models\DriveEliminacion;
use App\Models\Empleado;
use App\Models\File;
use App\Models\Folder;
use App\Services\Drive\DriveStorage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\Rule;
use Throwable;
use ZipArchive;

class FolderController extends Controller
{
    /** Intentos de contraseña por minuto al borrar carpetas. */
    private const INTENTOS_CONTRASENA = 5;

    public function __construct(
        private readonly DriveStorage $storage,
    ) {}

    public function getContent($id)
    {
        $id = (int) $id;

        // Contadores y autor: los usa la vista de lista del Drive.
        $folders = Folder::where('parent_id', $id)
            ->withCount(['files', 'children'])
            ->orderBy('nombre', 'asc')
            ->get();

        $files = File::where('folder_id', $id)
            ->with('usuario:id,name')
            ->orderBy('created_at', 'desc')
            ->get();

        // Carpetas de Recursos humanos: la interfaz oculta renombrar/mover/borrar.
        $carpetasDeEmpleado = Empleado::whereIn('folder_id', $folders->pluck('id'))->pluck('folder_id')->all();
        $folders->each(fn (Folder $f) => $f->setAttribute(
            'protegida',
            $f->sistema !== null || $f->rrhh_tipo_documento_id !== null || in_array($f->id, $carpetasDeEmpleado),
        ));

        return response()->json([
            'folders' => $folders,
            'files' => $files,
            'breadcrumbs' => $this->buildBreadcrumbs($id),
            'current_folder_id' => $id,
        ]);
    }

    public function store(Request $request)
    {
        $parentId = (int) $request->input('parent_id', 0);

        $validated = $request->validate([
            'parent_id' => ['required', 'integer', 'min:0'],
            'nombre' => [
                'required', 'string', 'max:150',
                Rule::unique('folders', 'nombre')->where('parent_id', $parentId),
            ],
        ], $this->mensajesNombre());

        if ($parentId > 0 && ! Folder::whereKey($parentId)->exists()) {
            return response()->json(['message' => 'La carpeta de destino no existe.'], 422);
        }

        $folder = Folder::create([
            'nombre' => $validated['nombre'],
            'parent_id' => $parentId,
            'tipo' => 1,
            'usuario_id' => auth()->id(),
        ]);

        return response()->json([
            'message' => 'Carpeta creada exitosamente',
            'folder' => $folder,
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $folder = Folder::findOrFail($id);

        if ($bloqueo = $this->bloqueoRrhh($folder)) {
            return $bloqueo;
        }

        $validated = $request->validate([
            'nombre' => [
                'required', 'string', 'max:150',
                Rule::unique('folders', 'nombre')
                    ->where('parent_id', (int) $folder->parent_id)
                    ->ignore($folder->id),
            ],
        ], $this->mensajesNombre());

        $folder->update(['nombre' => $validated['nombre']]);

        return response()->json([
            'message' => 'Carpeta actualizada exitosamente',
            'folder' => $folder,
        ]);
    }

    /**
     * Borra la carpeta con TODO su contenido (subcarpetas y archivos).
     * Exige la contraseña del usuario para evitar borrados accidentales.
     */
    public function destroy(Request $request, $id)
    {
        $folder = Folder::findOrFail($id);
        $user = $request->user();

        if ($bloqueo = $this->bloqueoRrhh($folder)) {
            return $bloqueo;
        }

        $request->validate(
            ['password' => ['required', 'string']],
            ['password.required' => 'Introduce tu contraseña para confirmar el borrado.'],
        );

        $claveIntentos = 'drive-borrar-carpeta:'.$user->id;

        if (RateLimiter::tooManyAttempts($claveIntentos, self::INTENTOS_CONTRASENA)) {
            return response()->json([
                'message' => 'Demasiados intentos. Espera '.RateLimiter::availableIn($claveIntentos).' segundos.',
            ], 429);
        }

        if (! Hash::check($request->input('password'), $user->password)) {
            RateLimiter::hit($claveIntentos, 60);

            return response()->json([
                'message' => 'La contraseña no es correcta.',
                'errors' => ['password' => ['La contraseña no es correcta.']],
            ], 422);
        }

        RateLimiter::clear($claveIntentos);

        $carpetaIds = $this->idsConDescendientes($folder);
        $archivos = File::whereIn('folder_id', $carpetaIds)->get();
        $ubicacion = $folder->parent_id > 0
            ? optional(Folder::find($folder->parent_id))->rutaCompleta() ?? 'Inicio'
            : 'Inicio';

        DB::transaction(function () use ($folder, $carpetaIds, $archivos, $ubicacion, $user) {
            DriveEliminacion::create([
                'user_id' => $user->id,
                'tipo' => 'carpeta',
                'nombre' => $folder->nombre,
                'ubicacion' => $ubicacion,
                'carpetas' => count($carpetaIds),
                'archivos' => $archivos->count(),
            ]);

            File::whereIn('folder_id', $carpetaIds)->delete();
            Folder::whereIn('id', $carpetaIds)->delete();
        });

        // Ficheros físicos solo después de confirmar el borrado en BD.
        foreach ($archivos as $archivo) {
            try {
                $this->storage->borrar($archivo);
            } catch (Throwable $e) {
                Log::warning('Drive: no se pudo borrar el fichero físico', [
                    'file_id' => $archivo->id,
                    'ruta' => $archivo->ruta,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return response()->json([
            'message' => 'Carpeta eliminada exitosamente',
            'carpetas' => count($carpetaIds),
            'archivos' => $archivos->count(),
        ]);
    }

    public function move(Request $request, $id)
    {
        $validated = $request->validate([
            'target_folder_id' => 'required|integer|min:0',
        ]);

        $folder = Folder::findOrFail($id);
        $targetFolderId = (int) $validated['target_folder_id'];

        if ($bloqueo = $this->bloqueoRrhh($folder)) {
            return $bloqueo;
        }

        if ($targetFolderId > 0 && ! Folder::whereKey($targetFolderId)->exists()) {
            return response()->json(['message' => 'La carpeta de destino no existe.'], 422);
        }

        if ($this->isDescendant($targetFolderId, (int) $id)) {
            return response()->json([
                'message' => 'No se puede mover una carpeta dentro de sí misma',
            ], 422);
        }

        $wasRenamed = Folder::where('parent_id', $targetFolderId)
            ->where('nombre', $folder->nombre)
            ->where('id', '!=', $folder->id)
            ->exists();

        if ($wasRenamed) {
            $folder->nombre = $this->getUniqueFolderName($folder->nombre, $targetFolderId);
        }

        $folder->parent_id = $targetFolderId;
        $folder->save();

        return response()->json([
            'message' => 'Carpeta movida exitosamente',
            'folder' => $folder,
            'was_renamed' => $wasRenamed,
        ]);
    }

    public function download($id)
    {
        $folder = Folder::findOrFail($id);

        $tempDir = storage_path('app'.DIRECTORY_SEPARATOR.'temp');
        $zipPath = $tempDir.DIRECTORY_SEPARATOR.uniqid('carpeta_', true).'.zip';

        if (! is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $zip = new ZipArchive;

        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            return response()->json(['message' => 'No se pudo crear el archivo ZIP'], 500);
        }

        try {
            $this->addFolderRecursively($folder, $zip, '');
            $zip->close();

            if (! file_exists($zipPath)) {
                return response()->json(['message' => 'No se pudo generar el ZIP'], 500);
            }

            return response()->download($zipPath, str_replace(['/', '\\'], '-', $folder->nombre).'.zip', [
                'Content-Type' => 'application/zip',
            ])->deleteFileAfterSend(true);
        } catch (Throwable $e) {
            @$zip->close();

            if (file_exists($zipPath)) {
                @unlink($zipPath);
            }

            Log::error('Error creando ZIP', ['folder_id' => $id, 'error' => $e->getMessage()]);

            return response()->json(['message' => 'Error al crear el archivo ZIP'], 500);
        }
    }

    // -------------------------------------------------------------
    // Internos
    // -------------------------------------------------------------

    private function bloqueoRrhh(Folder $folder)
    {
        if (! $folder->gestionadaPorRrhh()) {
            return null;
        }

        return response()->json([
            'message' => 'Esta carpeta la gestiona Recursos humanos: se renombra, mueve o archiva desde la ficha del empleado.',
        ], 422);
    }

    private function mensajesNombre(): array
    {
        return [
            'nombre.unique' => 'Ya existe una carpeta con ese nombre en esta ubicación.',
            'nombre.max' => 'El nombre de la carpeta no puede superar 150 caracteres.',
        ];
    }

    private function buildBreadcrumbs($folderId): array
    {
        $breadcrumbs = [];
        $visitadas = [];
        $current = $folderId > 0 ? Folder::find($folderId) : null;

        while ($current && ! isset($visitadas[$current->id])) {
            $visitadas[$current->id] = true;
            array_unshift($breadcrumbs, ['id' => $current->id, 'nombre' => $current->nombre]);
            $current = $current->parent_id > 0 ? Folder::find($current->parent_id) : null;
        }

        array_unshift($breadcrumbs, ['id' => 0, 'nombre' => 'Inicio']);

        return $breadcrumbs;
    }

    /** @return int[] ids de la carpeta y todas sus subcarpetas */
    private function idsConDescendientes(Folder $folder): array
    {
        $ids = [(int) $folder->id];
        $pendientes = $ids;

        while ($pendientes) {
            $hijos = Folder::whereIn('parent_id', $pendientes)
                ->whereNotIn('id', $ids)
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->all();

            $ids = array_merge($ids, $hijos);
            $pendientes = $hijos;
        }

        return $ids;
    }

    private function getUniqueFolderName(string $baseName, int $parentId): string
    {
        $name = $baseName;
        $counter = 1;

        while (Folder::where('parent_id', $parentId)->where('nombre', $name)->exists()) {
            $name = mb_substr($baseName, 0, 140)." ({$counter})";
            $counter++;
        }

        return $name;
    }

    private function isDescendant(int $potentialDescendantId, int $ancestorId): bool
    {
        if ($potentialDescendantId === $ancestorId) {
            return true;
        }

        $visitadas = [];
        $current = Folder::find($potentialDescendantId);

        while ($current && $current->parent_id > 0 && ! isset($visitadas[$current->id])) {
            $visitadas[$current->id] = true;

            if ((int) $current->parent_id === $ancestorId) {
                return true;
            }

            $current = Folder::find($current->parent_id);
        }

        return false;
    }

    private function addFolderRecursively(Folder $folder, ZipArchive $zip, string $parentPath): void
    {
        $currentPath = $parentPath.str_replace(['/', '\\'], '-', $folder->nombre).'/';
        $zip->addEmptyDir($currentPath);

        foreach ($folder->files as $file) {
            if ($this->storage->existe($file)) {
                $zip->addFile($this->storage->rutaAbsoluta($file), $currentPath.$file->nombre);
            }
        }

        foreach ($folder->children()->with('files')->get() as $child) {
            $this->addFolderRecursively($child, $zip, $currentPath);
        }
    }
}
