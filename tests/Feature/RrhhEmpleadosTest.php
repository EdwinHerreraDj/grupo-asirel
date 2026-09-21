<?php

namespace Tests\Feature;

use App\Models\Empleado;
use App\Models\File;
use App\Models\Folder;
use App\Models\RrhhTipoDocumento;
use App\Models\User;
use App\Services\Rrhh\CarpetasEmpleados;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Recursos humanos (fase 1): empleados, altas/bajas/reingresos, carpetas en
 * el Drive con apartados por tipo de documento y estado de la documentación.
 */
class RrhhEmpleadosTest extends TestCase
{
    use DatabaseTransactions;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
        Storage::fake('public');

        $this->admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'name' => 'Admin rrhh',
            'email' => 'rrhh-'.uniqid().'@t.es',
            'password' => 'password123',
        ]);
    }

    // -------------------------------------------------------------
    // Acceso y validación
    // -------------------------------------------------------------

    public function test_solo_administradores_acceden_a_recursos_humanos(): void
    {
        $user = User::factory()->create([
            'role' => User::ROLE_USER,
            'name' => 'Usuario rrhh',
            'email' => 'rrhh-user-'.uniqid().'@t.es',
            'password' => 'password123',
        ]);

        $this->actingAs($user)->get('/rrhh')->assertForbidden();
        $this->actingAs($user)->getJson('/api/rrhh/empleados')->assertForbidden();
        $this->actingAs($user)->postJson('/api/rrhh/empleados', $this->datos())->assertForbidden();
        $this->actingAs($user)->getJson('/api/rrhh/tipos-documento')->assertForbidden();

        $this->actingAs($this->admin)->get('/rrhh')->assertOk()->assertSee('react-rrhh', false);
    }

    public function test_valida_dni_nie_iban_y_nss(): void
    {
        $this->alta(['dni' => '12345678A'])->assertStatus(422)->assertJsonValidationErrors('dni');
        $this->alta(['dni' => 'X1234567Z'])->assertStatus(422)->assertJsonValidationErrors('dni');
        $this->alta(['iban' => 'ES9121000418450200051333'])->assertStatus(422)->assertJsonValidationErrors('iban');
        $this->alta(['nss' => '1234'])->assertStatus(422)->assertJsonValidationErrors('nss');

        // Con espacios, guiones y minúsculas se normaliza y es válido.
        $this->alta(['dni' => '12.345.678-z', 'iban' => 'es91 2100 0418 4502 0005 1332', 'nss' => '28/12345678/40'])
            ->assertCreated();

        $empleado = Empleado::where('dni', '12345678Z')->firstOrFail();
        $this->assertSame('ES9121000418450200051332', $empleado->iban);
        $this->assertSame('281234567840', $empleado->nss);

        // NIE válido y DNI repetido.
        $this->alta(['dni' => 'X1234567L'])->assertCreated();
        $this->alta(['dni' => '12345678Z'])->assertStatus(422)->assertJsonValidationErrors('dni');
    }

    public function test_el_iban_se_guarda_cifrado(): void
    {
        $this->alta()->assertCreated();

        $enBd = DB::table('empleados')->where('dni', '12345678Z')->value('iban');

        $this->assertNotEmpty($enBd);
        $this->assertStringNotContainsString('ES9121000418450200051332', $enBd);
        $this->assertSame('ES9121000418450200051332', Empleado::where('dni', '12345678Z')->first()->iban);
    }

    // -------------------------------------------------------------
    // Alta, baja y reingreso
    // -------------------------------------------------------------

    public function test_el_alta_crea_ficha_periodo_y_carpeta_con_apartados(): void
    {
        $dni = $this->tipo('DNI Test', caducidad: true);
        $contrato = $this->tipo('Contrato Test');

        $this->alta()->assertCreated()->assertJsonPath('empleado.estado', 'activo');

        $empleado = Empleado::where('dni', '12345678Z')->firstOrFail();
        $this->assertCount(1, $empleado->periodos);
        $this->assertSame(now()->toDateString(), $empleado->periodos->first()->fecha_alta->toDateString());

        $carpeta = Folder::findOrFail($empleado->folder_id);
        $activos = Folder::where('sistema', CarpetasEmpleados::SISTEMA_ACTIVOS)->firstOrFail();
        $this->assertSame($activos->id, (int) $carpeta->parent_id);
        $this->assertSame('García López, Juan (12345678Z)', $carpeta->nombre);

        foreach ([$dni, $contrato] as $tipo) {
            $this->assertTrue(Folder::where('parent_id', $carpeta->id)
                ->where('rrhh_tipo_documento_id', $tipo->id)
                ->where('nombre', $tipo->nombre)
                ->exists());
        }
    }

    public function test_baja_y_reingreso_mueven_la_carpeta_y_guardan_historial(): void
    {
        $this->alta()->assertCreated();
        $empleado = Empleado::where('dni', '12345678Z')->firstOrFail();
        $bajas = fn () => Folder::where('sistema', CarpetasEmpleados::SISTEMA_BAJAS)->first();

        // La baja no puede ser anterior al alta.
        $this->actingAs($this->admin)->postJson("/api/rrhh/empleados/{$empleado->id}/baja", [
            'fecha_baja' => now()->subYear()->toDateString(),
            'motivo_baja' => 'fin_contrato',
        ])->assertStatus(422)->assertJsonValidationErrors('fecha_baja');

        $this->actingAs($this->admin)->postJson("/api/rrhh/empleados/{$empleado->id}/baja", [
            'fecha_baja' => now()->toDateString(),
            'motivo_baja' => 'fin_contrato',
            'observaciones_baja' => 'Fin de obra',
        ])->assertOk()->assertJsonPath('empleado.estado', 'baja');

        $empleado->refresh();
        $this->assertSame('baja', $empleado->estado);
        $this->assertSame((int) $bajas()->id, (int) Folder::find($empleado->folder_id)->parent_id);
        $this->assertSame('fin_contrato', $empleado->periodoActual->motivo_baja);

        // Doble baja: no.
        $this->actingAs($this->admin)->postJson("/api/rrhh/empleados/{$empleado->id}/baja", [
            'fecha_baja' => now()->toDateString(), 'motivo_baja' => 'otro',
        ])->assertStatus(422);

        // Reingreso: debe ser posterior a la baja.
        $this->actingAs($this->admin)->postJson("/api/rrhh/empleados/{$empleado->id}/reingreso", [
            'fecha_alta' => now()->toDateString(),
        ])->assertStatus(422)->assertJsonValidationErrors('fecha_alta');

        $this->actingAs($this->admin)->postJson("/api/rrhh/empleados/{$empleado->id}/reingreso", [
            'fecha_alta' => now()->addDay()->toDateString(),
            'tipo_contrato' => 'indefinido',
        ])->assertOk()->assertJsonPath('empleado.estado', 'activo');

        $empleado->refresh();
        $activos = Folder::where('sistema', CarpetasEmpleados::SISTEMA_ACTIVOS)->firstOrFail();
        $this->assertSame((int) $activos->id, (int) Folder::find($empleado->folder_id)->parent_id);
        $this->assertCount(2, $empleado->periodos);
        $this->assertSame('indefinido', $empleado->tipo_contrato);

        // Reingreso estando de alta: no.
        $this->actingAs($this->admin)->postJson("/api/rrhh/empleados/{$empleado->id}/reingreso", [
            'fecha_alta' => now()->addDays(2)->toDateString(),
        ])->assertStatus(422);
    }

    public function test_editar_nombre_o_dni_renombra_la_carpeta(): void
    {
        $this->alta()->assertCreated();
        $empleado = Empleado::where('dni', '12345678Z')->firstOrFail();

        $this->actingAs($this->admin)->putJson("/api/rrhh/empleados/{$empleado->id}", $this->datos([
            'nombre' => 'Juan Carlos',
            'dni' => 'X1234567L',
        ]))->assertOk();

        $this->assertSame('García López, Juan Carlos (X1234567L)', Folder::find($empleado->folder_id)->nombre);
    }

    // -------------------------------------------------------------
    // Tipos de documento y documentación
    // -------------------------------------------------------------

    public function test_los_tipos_nuevos_o_renombrados_llegan_a_las_carpetas_existentes(): void
    {
        $this->alta()->assertCreated();
        $empleado = Empleado::where('dni', '12345678Z')->firstOrFail();

        $respuesta = $this->actingAs($this->admin)->postJson('/api/rrhh/tipos-documento', [
            'nombre' => 'Reconocimiento médico Test',
            'obligatorio' => true,
            'requiere_caducidad' => true,
            'dias_aviso' => 30,
        ])->assertCreated();
        $tipoId = $respuesta->json('tipo.id');

        $apartado = Folder::where('parent_id', $empleado->folder_id)->where('rrhh_tipo_documento_id', $tipoId)->firstOrFail();
        $this->assertSame('Reconocimiento médico Test', $apartado->nombre);

        $this->actingAs($this->admin)->putJson("/api/rrhh/tipos-documento/{$tipoId}", [
            'nombre' => 'Revisión médica Test',
        ])->assertOk();
        $this->assertSame('Revisión médica Test', $apartado->fresh()->nombre);

        $this->actingAs($this->admin)->postJson('/api/rrhh/tipos-documento', ['nombre' => 'Revisión médica Test'])
            ->assertStatus(422)->assertJsonValidationErrors('nombre');
    }

    public function test_estado_de_la_documentacion_y_pendientes(): void
    {
        // Solo cuentan los tipos de este test (los de la migración se desactivan).
        RrhhTipoDocumento::query()->update(['activo' => false]);

        $dni = $this->tipo('DNI Estado', caducidad: true, diasAviso: 30);
        $contrato = $this->tipo('Contrato Estado');
        $this->tipo('Formación Estado', obligatorio: false);

        $this->alta()->assertCreated();
        $empleado = Empleado::where('dni', '12345678Z')->firstOrFail();
        $apartado = fn ($tipo) => Folder::where('parent_id', $empleado->folder_id)->where('rrhh_tipo_documento_id', $tipo->id)->first();

        // DNI caduca en 10 días → próximo; contrato sin subir → falta; formación opcional → vacío.
        $this->documento($apartado($dni), now()->addDays(10)->toDateString());

        $estados = collect($this->actingAs($this->admin)->getJson("/api/rrhh/empleados/{$empleado->id}")->assertOk()->json('documentacion'))
            ->pluck('estado', 'tipo');
        $this->assertSame('proximo', $estados['DNI Estado']);
        $this->assertSame('falta', $estados['Contrato Estado']);
        $this->assertSame('vacio', $estados['Formación Estado']);

        $pendientes = $this->actingAs($this->admin)->getJson('/api/rrhh/documentacion-pendiente')->assertOk();
        $fila = collect($pendientes->json('empleados'))->firstWhere('empleado.id', $empleado->id);
        $this->assertCount(2, $fila['problemas']);

        // Listado: resumen por empleado.
        $listado = collect($this->actingAs($this->admin)->getJson('/api/rrhh/empleados?search=12345678Z')->json('data'));
        $this->assertSame(['faltan' => 1, 'vencidos' => 0, 'proximos' => 1, 'sin_fecha' => 0, 'total' => 2], $listado->first()['documentacion']);
        $this->assertArrayNotHasKey('iban', $listado->first());

        // Contrato subido y DNI renovado → sin pendientes.
        $this->documento($apartado($contrato));
        $this->documento($apartado($dni), now()->addYears(5)->toDateString());

        $pendientes = $this->actingAs($this->admin)->getJson('/api/rrhh/documentacion-pendiente')->assertOk();
        $this->assertNull(collect($pendientes->json('empleados'))->firstWhere('empleado.id', $empleado->id));
    }

    // -------------------------------------------------------------
    // Protección en el Drive
    // -------------------------------------------------------------

    public function test_las_carpetas_de_rrhh_no_se_tocan_desde_el_drive(): void
    {
        $this->tipo('DNI Drive', caducidad: true);
        $this->alta()->assertCreated();
        $empleado = Empleado::where('dni', '12345678Z')->firstOrFail();
        $activos = Folder::where('sistema', CarpetasEmpleados::SISTEMA_ACTIVOS)->firstOrFail();
        $apartado = Folder::where('parent_id', $empleado->folder_id)->whereNotNull('rrhh_tipo_documento_id')->firstOrFail();
        $otra = Folder::forceCreate(['nombre' => 'Otra '.uniqid(), 'parent_id' => 0, 'tipo' => 1]);

        foreach ([$activos, Folder::find($empleado->folder_id), $apartado] as $carpeta) {
            $this->actingAs($this->admin)->putJson("/api/folders/{$carpeta->id}", ['nombre' => 'Cambiado'])->assertStatus(422);
            $this->actingAs($this->admin)->postJson("/api/folders/{$carpeta->id}/move", ['target_folder_id' => $otra->id])->assertStatus(422);
            $this->actingAs($this->admin)->deleteJson("/api/folders/{$carpeta->id}", ['password' => 'password123'])->assertStatus(422);
            $this->assertNotNull($carpeta->fresh());
        }

        // En el listado del Drive vienen marcadas como protegidas.
        $raiz = collect($this->actingAs($this->admin)->getJson('/api/folders/0/content')->json('folders'))->keyBy('id');
        $this->assertTrue($raiz[$activos->id]['protegida']);
        $this->assertFalse($raiz[$otra->id]['protegida']);

        // Dentro de un apartado sí se suben archivos normalmente.
        $this->actingAs($this->admin)->post('/api/files', [
            'file' => \Illuminate\Http\UploadedFile::fake()->create('dni.pdf', 10, 'application/pdf'),
            'folder_id' => $apartado->id,
            'tiene_caducidad' => '1',
            'fecha_caducidad' => now()->addYear()->toDateString(),
        ], ['Accept' => 'application/json'])->assertCreated();
    }

    // -------------------------------------------------------------
    // Fixtures
    // -------------------------------------------------------------

    private function datos(array $cambios = []): array
    {
        return array_merge([
            'nombre' => 'Juan',
            'apellidos' => 'García López',
            'dni' => '12345678Z',
            'nss' => '281234567840',
            'fecha_nacimiento' => '1985-05-20',
            'telefono' => '600000000',
            'email' => 'juan@example.com',
            'puesto' => 'Oficial de primera',
            'tipo_contrato' => 'temporal',
            'jornada' => 'completa',
            'horas_semanales' => 40,
            'salario_bruto_anual' => 24000,
            'iban' => 'ES9121000418450200051332',
            'contacto_emergencia_nombre' => 'María',
            'contacto_emergencia_relacion' => 'Pareja',
            'contacto_emergencia_telefono' => '611111111',
            'fecha_alta' => now()->toDateString(),
        ], $cambios);
    }

    private function alta(array $cambios = [])
    {
        return $this->actingAs($this->admin)->postJson('/api/rrhh/empleados', $this->datos($cambios));
    }

    private function tipo(string $nombre, bool $caducidad = false, bool $obligatorio = true, int $diasAviso = 30): RrhhTipoDocumento
    {
        return RrhhTipoDocumento::create([
            'nombre' => $nombre,
            'obligatorio' => $obligatorio,
            'requiere_caducidad' => $caducidad,
            'dias_aviso' => $diasAviso,
        ]);
    }

    private function documento(Folder $apartado, ?string $caduca = null): File
    {
        $ruta = 'drive/archivos/t/'.uniqid().'.pdf';
        Storage::disk('local')->put($ruta, 'x');

        return File::forceCreate([
            'folder_id' => $apartado->id,
            'usuario_id' => $this->admin->id,
            'nombre' => 'doc.pdf',
            'ruta' => $ruta,
            'disco' => 'local',
            'tipo' => 'application/pdf',
            'tamaño' => 1,
            'tiene_caducidad' => $caduca !== null,
            'fecha_caducidad' => $caduca,
        ]);
    }
}
