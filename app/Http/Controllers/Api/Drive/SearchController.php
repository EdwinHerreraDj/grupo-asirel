<?php

namespace App\Http\Controllers\Api\Drive;

use App\Http\Controllers\Controller;
use App\Models\Folder;
use App\Models\File;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function search(Request $request)
    {
        $query = $request->get('q', '');

        if (empty($query) || strlen($query) < 2) {
            return response()->json([
                'folders' => [],
                'files' => [],
                'total' => 0,
                'query' => $query
            ]);
        }

        // Drive compartido entre administradores: se busca en todo.
        $like = '%'.addcslashes($query, '%_\\').'%';

        // Buscar carpetas
        $folders = Folder::where('nombre', 'LIKE', $like)
            ->withCount(['files', 'children'])
            ->with('parent')
            ->orderBy('nombre', 'asc')
            ->get()
            ->map(function ($folder) {
                // Construir ruta completa
                $folder->path = $this->buildFolderPath($folder);
                return $folder;
            });

        // Buscar archivos
        $files = File::where('nombre', 'LIKE', $like)
            ->with(['folder', 'usuario:id,name'])
            ->orderBy('nombre', 'asc')
            ->get()
            ->map(function ($file) {
                // Agregar ruta de la carpeta contenedora
                if ($file->folder) {
                    $file->folder_path = $this->buildFolderPath($file->folder);
                } else {
                    $file->folder_path = 'Inicio';
                }
                return $file;
            });

        return response()->json([
            'folders' => $folders,
            'files' => $files,
            'total' => $folders->count() + $files->count(),
            'query' => $query
        ]);
    }

    private function buildFolderPath($folder)
    {
        $path = [];
        $current = $folder;

        while ($current) {
            array_unshift($path, $current->nombre);
            $current = $current->parent_id ? Folder::find($current->parent_id) : null;
        }

        return 'Inicio' . (count($path) > 0 ? ' / ' . implode(' / ', $path) : '');
    }
}
