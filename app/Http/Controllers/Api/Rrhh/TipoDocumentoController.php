<?php

namespace App\Http\Controllers\Api\Rrhh;

use App\Http\Controllers\Controller;
use App\Models\RrhhTipoDocumento;
use App\Services\Rrhh\CarpetasEmpleados;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * Tipos de documento configurables (apartados de la carpeta de cada
 * empleado). No se borran: se desactivan, y sus carpetas y archivos se
 * conservan.
 */
class TipoDocumentoController extends Controller
{
    public function __construct(
        private readonly CarpetasEmpleados $carpetas,
    ) {}

    public function index()
    {
        return response()->json([
            'tipos' => RrhhTipoDocumento::orderBy('orden')->orderBy('nombre')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $datos = $request->validate($this->reglas(), $this->mensajes());

        $tipo = DB::transaction(function () use ($datos) {
            $tipo = RrhhTipoDocumento::create($datos + [
                'orden' => (int) RrhhTipoDocumento::max('orden') + 1,
            ])->refresh();

            if ($tipo->activo) {
                $this->carpetas->sincronizarTipoEnTodos($tipo);
            }

            return $tipo;
        });

        return response()->json(['message' => 'Tipo de documento creado', 'tipo' => $tipo], 201);
    }

    public function update(Request $request, RrhhTipoDocumento $tipo)
    {
        $datos = $request->validate($this->reglas($tipo), $this->mensajes());

        DB::transaction(function () use ($tipo, $datos) {
            $estabaActivo = $tipo->activo;

            $tipo->update($datos);
            $this->carpetas->renombrarApartados($tipo);

            if ($tipo->activo && ! $estabaActivo) {
                $this->carpetas->sincronizarTipoEnTodos($tipo);
            }
        });

        return response()->json(['message' => 'Tipo de documento actualizado', 'tipo' => $tipo->fresh()]);
    }

    private function reglas(?RrhhTipoDocumento $tipo = null): array
    {
        return [
            'nombre' => ['required', 'string', 'max:150', Rule::unique('rrhh_tipos_documento', 'nombre')->ignore($tipo?->id)],
            'obligatorio' => ['boolean'],
            'requiere_caducidad' => ['boolean'],
            'dias_aviso' => ['integer', 'min:0', 'max:365'],
            'activo' => ['boolean'],
        ];
    }

    private function mensajes(): array
    {
        return [
            'nombre.unique' => 'Ya existe un tipo de documento con ese nombre.',
        ];
    }
}
