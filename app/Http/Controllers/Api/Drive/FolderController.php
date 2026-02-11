<?php

namespace App\Http\Controllers\Api\Drive;

use App\Http\Controllers\Controller;
use App\Models\Folder;
use App\Models\File;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use ZipArchive;
use Illuminate\Http\Request;

class FolderController extends Controller
{
    public function getContent($id)
    {
        $id = (int) $id;


        // Obtener carpetas hijas
        $folders = Folder::where('parent_id', $id)
            ->orderBy('nombre', 'asc')
            ->get();

        // Obtener archivos de esta carpeta
        $files = File::where('folder_id', $id)
            ->orderBy('created_at', 'desc')
            ->get();

        // Construir breadcrumbs
        $breadcrumbs = $this->buildBreadcrumbs($id);

        return response()->json([
            'folders' => $folders,
            'files' => $files,
            'breadcrumbs' => $breadcrumbs,
            'current_folder_id' => $id
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'parent_id' => 'required|integer|min:0',
            'tipo' => 'nullable|string'
        ]);

        $folder = Folder::create([
            'nombre' => $validated['nombre'],
            'parent_id' => $validated['parent_id'],
            'tipo' => $validated['tipo'] ?? 'general',
            'usuario_id' => auth()->id()
        ]);

        return response()->json([
            'message' => 'Carpeta creada exitosamente',
            'folder' => $folder
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $folder = Folder::findOrFail($id);

        $validated = $request->validate([
            'nombre' => 'required|string|max:255'
        ]);

        $folder->update($validated);

        return response()->json([
            'message' => 'Carpeta actualizada exitosamente',
            'folder' => $folder
        ]);
    }

    public function destroy($id)
    {
        $folder = Folder::findOrFail($id);

        // Verificar que no tenga carpetas hijas
        if ($folder->children()->count() > 0) {
            return response()->json([
                'message' => 'No se puede eliminar una carpeta que contiene otras carpetas'
            ], 422);
        }

        // Verificar que no tenga archivos
        if ($folder->files()->count() > 0) {
            return response()->json([
                'message' => 'No se puede eliminar una carpeta que contiene archivos'
            ], 422);
        }

        $folder->delete();

        return response()->json([
            'message' => 'Carpeta eliminada exitosamente'
        ]);
    }

    private function buildBreadcrumbs($folderId)
    {
        $breadcrumbs = [];
        $current = $folderId > 0 ? Folder::find($folderId) : null;

        // Construir cadena desde el nodo actual hasta la raíz
        while ($current) {
            array_unshift($breadcrumbs, [
                'id' => $current->id,
                'nombre' => $current->nombre
            ]);

            $current = $current->parent_id > 0 ? Folder::find($current->parent_id) : null;
        }

        // Agregar "Inicio" al principio
        array_unshift($breadcrumbs, [
            'id' => 0,
            'nombre' => 'Inicio'
        ]);

        return $breadcrumbs;
    }


    public function copy(Request $request, $id)
    {
        $validated = $request->validate([
            'target_folder_id' => 'required|integer|min:0'
        ]);

        $sourceFolder = Folder::findOrFail($id);
        $targetFolderId = $validated['target_folder_id'];

        // Verificar que no se copie dentro de sí misma
        if ($this->isDescendant($targetFolderId, $id)) {
            return response()->json([
                'message' => 'No se puede copiar una carpeta dentro de sí misma'
            ], 422);
        }

        DB::beginTransaction();
        try {
            $newFolder = $this->copyFolderRecursive($sourceFolder, $targetFolderId);
            DB::commit();

            return response()->json([
                'message' => 'Carpeta copiada exitosamente',
                'folder' => $newFolder
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error copying folder: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error al copiar la carpeta',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function move(Request $request, $id)
    {
        $validated = $request->validate([
            'target_folder_id' => 'required|integer|min:0'
        ]);

        $folder = Folder::findOrFail($id);
        $targetFolderId = $validated['target_folder_id'];

        // Verificar que no se mueva dentro de sí misma
        if ($this->isDescendant($targetFolderId, $id)) {
            return response()->json([
                'message' => 'No se puede mover una carpeta dentro de sí misma'
            ], 422);
        }

        // Verificar si ya existe una carpeta con el mismo nombre
        $existingFolder = Folder::where('parent_id', $targetFolderId)
            ->where('nombre', $folder->nombre)
            ->where('id', '!=', $id)
            ->first();

        if ($existingFolder) {
            // Generar nombre único automáticamente
            $uniqueName = $this->getUniqueFolderName($folder->nombre, $targetFolderId);
            $folder->nombre = $uniqueName;
            $wasRenamed = true;
        } else {
            $wasRenamed = false;
        }

        $folder->parent_id = $targetFolderId;
        $folder->save();

        return response()->json([
            'message' => 'Carpeta movida exitosamente',
            'folder' => $folder,
            'was_renamed' => $wasRenamed
        ]);
    }

    private function copyFolderRecursive(Folder $sourceFolder, $targetParentId)
    {
        // Generar nombre único si ya existe
        $newName = $this->getUniqueFolderName($sourceFolder->nombre, $targetParentId);

        // Crear la nueva carpeta
        $newFolder = Folder::create([
            'nombre' => $newName,
            'parent_id' => $targetParentId,
            'tipo' => $sourceFolder->tipo,
            'usuario_id' => auth()->id()
        ]);

        // Copiar archivos de la carpeta
        foreach ($sourceFolder->files as $file) {
            $this->copyFile($file, $newFolder->id);
        }

        // Copiar subcarpetas recursivamente
        foreach ($sourceFolder->children as $childFolder) {
            $this->copyFolderRecursive($childFolder, $newFolder->id);
        }

        return $newFolder;
    }


    private function copyFile($sourceFile, $targetFolderId)
    {
        $storage = Storage::disk('public');

        // Generar nuevo nombre de archivo físico único
        $fileName = time() . '_' . uniqid() . '_' . basename($sourceFile->ruta);
        $newPath = 'uploads/' . $fileName;

        // Copiar el archivo físico
        if ($storage->exists($sourceFile->ruta)) {
            $storage->copy($sourceFile->ruta, $newPath);
        }

        // Generar nombre único para la BD si ya existe
        $uniqueName = $this->getUniqueFileName($sourceFile->nombre, $targetFolderId);

        // Crear registro en BD
        return \App\Models\File::create([
            'folder_id' => $targetFolderId,
            'usuario_id' => auth()->id(),
            'nombre' => $uniqueName,
            'ruta' => $newPath,
            'tipo' => $sourceFile->tipo,
            'tamaño' => $sourceFile->tamaño,
            'tiene_caducidad' => $sourceFile->tiene_caducidad,
            'fecha_caducidad' => $sourceFile->fecha_caducidad
        ]);
    }

    private function getUniqueFolderName($baseName, $parentId)
    {
        $name = $baseName;
        $counter = 1;

        while ($this->folderNameExists($name, $parentId)) {
            // Formato: "Carpeta (1)", "Carpeta (2)", etc.
            $name = $baseName . " ({$counter})";
            $counter++;
        }

        return $name;
    }

    private function getUniqueFileName($baseName, $folderId)
    {
        $name = $baseName;
        $counter = 1;

        while ($this->fileNameExists($name, $folderId)) {
            // Extraer extensión si es archivo
            $pathInfo = pathinfo($baseName);
            $nameWithoutExt = $pathInfo['filename'];
            $extension = isset($pathInfo['extension']) ? '.' . $pathInfo['extension'] : '';

            $name = $nameWithoutExt . " ({$counter})" . $extension;
            $counter++;
        }

        return $name;
    }

    private function nameExists($name, $folderId, $type)
    {
        if ($type === 'folder') {
            return Folder::where('parent_id', $folderId)
                ->where('nombre', $name)
                ->exists();
        } else {
            return \App\Models\File::where('folder_id', $folderId)
                ->where('nombre', $name)
                ->exists();
        }
    }

    private function folderNameExists($name, $parentId)
    {
        return Folder::where('parent_id', $parentId)
            ->where('nombre', $name)
            ->exists();
    }

    private function fileNameExists($name, $folderId)
    {
        return \App\Models\File::where('folder_id', $folderId)
            ->where('nombre', $name)
            ->exists();
    }

    private function isDescendant($potentialDescendantId, $ancestorId)
    {
        if ($potentialDescendantId == $ancestorId) {
            return true;
        }

        $current = Folder::find($potentialDescendantId);

        while ($current && $current->parent_id > 0) {
            if ($current->parent_id == $ancestorId) {
                return true;
            }
            $current = Folder::find($current->parent_id);
        }

        return false;
    }



    public function download($id)
    {
        $folder = Folder::with(['files', 'children'])->findOrFail($id);

        $zipFileName = preg_replace('/[^A-Za-z0-9_\-\s]/', '_', $folder->nombre) . '.zip';

        $tempDir = storage_path('app' . DIRECTORY_SEPARATOR . 'temp');
        $zipPath = $tempDir . DIRECTORY_SEPARATOR . uniqid() . '_' . time() . '.zip';

        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $zip = new ZipArchive;

        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE) {
            return response()->json(['message' => 'No se pudo crear el archivo ZIP'], 500);
        }

        try {

            $disk = Storage::disk('public');

            $this->addFolderRecursively($folder, $zip, '', $disk);

            $zip->close();

            if (!file_exists($zipPath)) {
                return response()->json(['message' => 'No se pudo generar el ZIP'], 500);
            }

            return response()->download($zipPath, $zipFileName, [
                'Content-Type' => 'application/zip',
            ])->deleteFileAfterSend(true);
        } catch (\Exception $e) {

            if (isset($zip)) {
                @$zip->close();
            }

            if (file_exists($zipPath)) {
                @unlink($zipPath);
            }

            Log::error('Error creando ZIP', [
                'folder_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Error al crear el archivo ZIP'
            ], 500);
        }
    }
    private function addFolderRecursively($folder, $zip, $parentPath, $disk)
    {
        $currentPath = $parentPath . $folder->nombre . '/';

        // Crear carpeta aunque esté vacía
        $zip->addEmptyDir($currentPath);

        // Añadir archivos
        foreach ($folder->files as $file) {

            $filePath = $disk->path($file->ruta);

            if (file_exists($filePath) && is_readable($filePath)) {
                $zip->addFile($filePath, $currentPath . $file->nombre);
            }
        }

        // Cargar hijos dinámicamente
        $children = $folder->children()->with(['files', 'children'])->get();

        foreach ($children as $child) {
            $this->addFolderRecursively($child, $zip, $currentPath, $disk);
        }
    }
}
