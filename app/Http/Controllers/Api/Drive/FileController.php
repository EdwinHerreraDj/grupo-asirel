<?php

namespace App\Http\Controllers\Api\Drive;

use App\Http\Controllers\Controller;
use App\Models\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FileController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'file' => 'required|file|max:51200', // 50MB máximo
            'folder_id' => 'required|integer|min:0'
        ]);

        $file = $request->file('file');
        $originalName = $file->getClientOriginalName();
        $extension = $file->getClientOriginalExtension();
        $mimeType = $file->getMimeType();
        $size = $file->getSize();

        // Generar nombre único
        $fileName = time() . '_' . str_replace(' ', '_', $originalName);

        // Guardar archivo
        $path = $file->storeAs('uploads', $fileName, 'public');

        // Crear registro en BD
        $fileRecord = File::create([
            'folder_id' => $validated['folder_id'],
            'usuario_id' => auth()->id(),
            'nombre' => $originalName,
            'ruta' => $path,
            'tipo' => $mimeType,
            'tamaño' => $size,
            'tiene_caducidad' => false
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
            \Log::error("File not found: " . $file->ruta);
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
            'target_folder_id' => 'required|integer|min:0'
        ]);

        $file = File::findOrFail($id);
        $targetFolderId = $validated['target_folder_id'];

        // Verificar si ya existe un archivo con el mismo nombre
        $existingFile = File::where('folder_id', $targetFolderId)
            ->where('nombre', $file->nombre)
            ->where('id', '!=', $id)
            ->first();

        if ($existingFile) {
            // Generar nombre único automáticamente
            $uniqueName = $this->getUniqueFileName($file->nombre, $targetFolderId);
            $file->nombre = $uniqueName;
            $wasRenamed = true;
        } else {
            $wasRenamed = false;
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
}
