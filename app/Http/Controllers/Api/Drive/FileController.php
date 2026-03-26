<?php

namespace App\Http\Controllers\Api\Drive;

use App\Http\Controllers\Controller;
use App\Models\File;
use App\Models\Folder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class FileController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'file' => 'required|file|max:51200', // 50MB
            'folder_id' => 'required|integer|min:0',
            'tiene_caducidad' => 'boolean',
            'fecha_caducidad' => 'nullable|date|after:today'
        ]);

        $file = $request->file('file');
        $originalName = $file->getClientOriginalName();
        $mimeType = $file->getMimeType();
        $size = $file->getSize();

        // Convertir 0 a null
        $folderId = $validated['folder_id'] == 0 ? null : $validated['folder_id'];

        $fileName = time() . '_' . uniqid() . '_' . str_replace(' ', '_', $originalName);
        $path = $file->storeAs('uploads', $fileName, 'public');

        $fileRecord = File::create([
            'folder_id' => $folderId,
            'usuario_id' => auth()->id(),
            'nombre' => $originalName,
            'ruta' => $path,
            'tipo' => $mimeType,
            'tamaño' => $size,
            'tiene_caducidad' => $validated['tiene_caducidad'] ?? false,
            'fecha_caducidad' => $validated['fecha_caducidad'] ?? null
        ]);

        return response()->json([
            'message' => 'Archivo subido exitosamente',
            'file' => $fileRecord
        ], 201);
    }


    public function update(Request $request, $id)
    {
        $file = File::findOrFail($id);

        $validated = $request->validate([
            'nombre' => 'required|string|max:255'
        ]);

        $file->update([
            'nombre' => $validated['nombre']
        ]);

        return response()->json([
            'message' => 'Archivo renombrado exitosamente',
            'file' => $file
        ]);
    }

    public function destroy($id)
    {
        $file = File::findOrFail($id);

        // Eliminar archivo físico
        if (Storage::disk('public')->exists($file->ruta)) {
            Storage::disk('public')->delete($file->ruta);
        }

        // Eliminar registro
        $file->delete();

        return response()->json([
            'message' => 'Archivo eliminado exitosamente'
        ]);
    }

    public function download($id)
    {
        $file = File::findOrFail($id);

        $storage = Storage::disk('public');

        if (!$storage->exists($file->ruta)) {
            Log::error("File not found: " . $file->ruta);
            return response()->json([
                'message' => 'Archivo no encontrado en el servidor'
            ], 404);
        }

        return response()->download(
            $storage->path($file->ruta),
            $file->nombre
        );
    }

    public function copy(Request $request, $id)
    {
        $validated = $request->validate([
            'target_folder_id' => 'required|integer|min:0'
        ]);

        $sourceFile = File::findOrFail($id);
        $targetFolderId = $validated['target_folder_id'];

        $storage = Storage::disk('public');

        // Generar nuevo nombre de archivo físico único
        $fileName = time() . '_' . uniqid() . '_' . basename($sourceFile->ruta);
        $newPath = 'uploads/' . $fileName;

        // Copiar el archivo físico
        if ($storage->exists($sourceFile->ruta)) {
            $storage->copy($sourceFile->ruta, $newPath);
        } else {
            return response()->json([
                'message' => 'Archivo físico no encontrado'
            ], 404);
        }

        // Generar nombre único para mostrar al usuario si ya existe
        $uniqueName = $this->getUniqueFileName($sourceFile->nombre, $targetFolderId);

        // Crear nuevo registro en BD
        $newFile = File::create([
            'folder_id' => $targetFolderId,
            'usuario_id' => auth()->id(),
            'nombre' => $uniqueName,
            'ruta' => $newPath,
            'tipo' => $sourceFile->tipo,
            'tamaño' => $sourceFile->tamaño,
            'tiene_caducidad' => $sourceFile->tiene_caducidad,
            'fecha_caducidad' => $sourceFile->fecha_caducidad
        ]);

        return response()->json([
            'message' => 'Archivo copiado exitosamente',
            'file' => $newFile,
            'was_renamed' => $uniqueName !== $sourceFile->nombre
        ]);
    }

    public function move(Request $request, $id)
    {
        $validated = $request->validate([
            'target_folder_id' => 'required|integer|exists:folders,id'
        ]);

        $file = File::findOrFail($id);

        $targetFolderId = (int) $validated['target_folder_id'];

        // Bloquear raíz explícitamente (doble seguridad)
        if ($targetFolderId === 0) {
            return response()->json([
                'message' => 'No se pueden mover archivos a la carpeta raíz.'
            ], 422);
        }

        // Seguridad: verificar que el archivo pertenece al usuario
        if ($file->usuario_id !== auth()->id()) {
            return response()->json([
                'message' => 'No tienes permiso para mover este archivo.'
            ], 403);
        }

        // Verificar que no esté intentando mover al mismo lugar
        if ($file->folder_id == $targetFolderId) {
            return response()->json([
                'message' => 'El archivo ya se encuentra en esta carpeta.'
            ], 422);
        }

        // Verificar si ya existe un archivo con el mismo nombre en destino
        $existingFile = File::where('folder_id', $targetFolderId)
            ->where('nombre', $file->nombre)
            ->where('id', '!=', $id)
            ->first();

        $wasRenamed = false;

        if ($existingFile) {
            $uniqueName = $this->getUniqueFileName($file->nombre, $targetFolderId);
            $file->nombre = $uniqueName;
            $wasRenamed = true;
        }

        $file->folder_id = $targetFolderId;
        $file->save();

        return response()->json([
            'message' => 'Archivo movido exitosamente',
            'file' => $file,
            'was_renamed' => $wasRenamed
        ]);
    }


    private function getUniqueFileName($baseName, $folderId)
    {
        $name = $baseName;
        $counter = 1;

        while ($this->nameExists($name, $folderId)) {
            // Extraer nombre y extensión
            $pathInfo = pathinfo($baseName);
            $nameWithoutExt = $pathInfo['filename'];
            $extension = isset($pathInfo['extension']) ? '.' . $pathInfo['extension'] : '';

            // Formato: "documento (1).pdf", "documento (2).pdf", etc.
            $name = $nameWithoutExt . " ({$counter})" . $extension;
            $counter++;
        }

        return $name;
    }

    private function nameExists($name, $folderId)
    {
        return File::where('folder_id', $folderId)
            ->where('nombre', $name)
            ->exists();
    }

    public function extract(Request $request, $id)
    {
        $file = File::findOrFail($id);

        // Verificar que sea un ZIP
        $extension = strtolower(pathinfo($file->nombre, PATHINFO_EXTENSION));
        if (!in_array($extension, ['zip'])) {
            return response()->json([
                'message' => 'Solo se pueden extraer archivos ZIP'
            ], 422);
        }

        $disk = Storage::disk('public');
        $zipPath = $disk->path($file->ruta);

        if (!file_exists($zipPath)) {
            return response()->json([
                'message' => 'Archivo ZIP no encontrado'
            ], 404);
        }

        $zip = new ZipArchive;

        if ($zip->open($zipPath) !== TRUE) {
            return response()->json([
                'message' => 'No se pudo abrir el archivo ZIP'
            ], 500);
        }

        DB::beginTransaction();
        try {
            $targetFolderId = $file->folder_id;
            $baseFolderName = pathinfo($file->nombre, PATHINFO_FILENAME);

            // Crear carpeta base para el contenido extraído
            $baseFolder = Folder::create([
                'nombre' => $baseFolderName,
                'parent_id' => $targetFolderId,
                'tipo' => 1,
                'usuario_id' => auth()->id()
            ]);


            // Extraer contenido
            $tempExtractPath = storage_path('app' . DIRECTORY_SEPARATOR . 'temp' . DIRECTORY_SEPARATOR . uniqid());
            mkdir($tempExtractPath, 0755, true);

            $zip->extractTo($tempExtractPath);
            $zip->close();

            // Procesar estructura de carpetas y archivos
            $stats = $this->processExtractedContent($tempExtractPath, $baseFolder->id);

            // Limpiar carpeta temporal
            $this->deleteDirectory($tempExtractPath);

            DB::commit();

            Log::info("ZIP extraído exitosamente", [
                'file' => $file->nombre,
                'carpetas_creadas' => $stats['folders'],
                'archivos_creados' => $stats['files']
            ]);

            return response()->json([
                'message' => 'ZIP extraído exitosamente',
                'stats' => $stats,
                'base_folder_id' => $baseFolder->id
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            if (isset($tempExtractPath) && is_dir($tempExtractPath)) {
                $this->deleteDirectory($tempExtractPath);
            }

            Log::error('Error extrayendo ZIP', [
                'file' => $file->nombre,
                'error' => $e->getMessage(),
                'line' => $e->getLine()
            ]);

            return response()->json([
                'message' => 'Error al extraer el archivo ZIP: ' . $e->getMessage()
            ], 500);
        }
    }

    private function processExtractedContent($sourcePath, $parentFolderId)
    {
        $stats = ['folders' => 0, 'files' => 0];
        $disk = Storage::disk('public');

        $items = scandir($sourcePath);

        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            $itemPath = $sourcePath . DIRECTORY_SEPARATOR . $item;

            if (is_dir($itemPath)) {
                // Crear carpeta en BD
                $folder = Folder::create([
                    'nombre' => $item,
                    'parent_id' => $parentFolderId,
                    'tipo' => 1,
                    'usuario_id' => auth()->id()
                ]);

                $stats['folders']++;

                // Procesar contenido de la subcarpeta recursivamente
                $subStats = $this->processExtractedContent($itemPath, $folder->id);
                $stats['folders'] += $subStats['folders'];
                $stats['files'] += $subStats['files'];
            } else {
                // Es un archivo
                $fileName = time() . '_' . uniqid() . '_' . basename($item);
                $destinationPath = 'uploads' . DIRECTORY_SEPARATOR . $fileName;

                // Copiar archivo a storage/app/public/uploads
                $fullDestPath = $disk->path($destinationPath);

                if (copy($itemPath, $fullDestPath)) {
                    // Crear registro en BD
                    File::create([
                        'folder_id' => $parentFolderId,
                        'usuario_id' => auth()->id(),
                        'nombre' => basename($item),
                        'ruta' => 'uploads/' . $fileName,
                        'tipo' => mime_content_type($itemPath) ?: 'application/octet-stream',
                        'tamaño' => filesize($itemPath),
                        'tiene_caducidad' => false
                    ]);

                    $stats['files']++;
                }
            }
        }

        return $stats;
    }

    private function deleteDirectory($dir)
    {
        if (!is_dir($dir)) {
            return;
        }

        $items = scandir($dir);

        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            $path = $dir . DIRECTORY_SEPARATOR . $item;

            if (is_dir($path)) {
                $this->deleteDirectory($path);
            } else {
                unlink($path);
            }
        }

        rmdir($dir);
    }

    public function expiringFiles(Request $request)
    {
        $days = $request->get('days', 30);

        $now = now()->startOfDay();
        $futureLimit = now()->addDays($days)->endOfDay();

        $files = File::where('usuario_id', auth()->id())
            ->where('tiene_caducidad', true)
            ->whereNotNull('fecha_caducidad')
            ->whereBetween('fecha_caducidad', [
                now()->subYears(1), // límite inferior razonable
                $futureLimit
            ])
            ->with('folder')
            ->orderBy('fecha_caducidad', 'asc')
            ->get()
            ->map(function ($file) use ($now) {
                $file->estado_caducidad = $file->fecha_caducidad < $now
                    ? 'vencido'
                    : 'proximo';

                return $file;
            });

        return response()->json([
            'files' => $files,
            'total' => $files->count()
        ]);
    }
}
