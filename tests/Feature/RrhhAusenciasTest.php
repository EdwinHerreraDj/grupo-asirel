<?php

namespace Tests\Feature;

use App\Models\Ausencia;
use App\Models\Empleado;
use App\Models\File;
use App\Models\Festivo;
use App\Models\Folder;
use App\Models\Obra;
use App\Models\RrhhTipoAusencia;
use App\Models\User;
use App\Services\Rrhh\CarpetasEmpleados;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Recursos humanos (fase 2): ausencias, saldo de vacaciones, festivos y
 * calendario. Se usa 2030 para que las cuentas no dependan de la fecha de hoy
 * (1 de julio de 2030 es lunes y el año no es bisiesto).
 */
class RrhhAusenciasTest extends TestCase
{
    use DatabaseTransactions;

    private User $admin;

    private RrhhTipoAusencia $vacaciones;

    private RrhhTipoAusencia $bajaMedica;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
        Storage::fake('public');

        $this->admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'name' => 'Admin ausencias',
            'email' => 'aus-'.uniqid().'@t.es',
            'password' => 'password123',
        ]);

        $this->vacaciones = RrhhTipoAusencia::where('es_vacaciones', true)->firstOrFail();
        $this->vacaciones->update(['dias_anuales' => 30, 'computo' => 'naturales', 'activo' => true]);
        $this->bajaMedica = RrhhTipoAusencia::where('es_baja_medica', true)->where('activo', true)->firstOrFail();
    }

    public function test_solo_administradores(): void
    {
        $user = User::factory()->create([
            'role' => User::ROLE_USER,
            'name' => 'Usuario aus',
            'email' => 'aus-user-'.uniqid().'@t.es',
            'password' => 'password123',
        ]);
        $empleado = $this->empleado('2030-07-01');

        foreach (['/api/rrhh/calendario', "/api/rrhh/empleados/{$empleado->id}/ausencias", '/api/rrhh/tipos-ausencia', '/api/rrhh/festivos'] as $url) {
            $this->actingAs($user)->getJson($url)->assertForbidden();
        }
        $this->actingAs($user)->postJson("/api/rrhh/empleados/{$empleado->id}/ausencias", [])->assertForbidden();
    }

    public function test_vacaciones_cuentan_dias_y_saldo_proporcional(): void
    {
        Festivo::create(['fecha' => '2030-07-03', 'nombre' => 'Festivo local']);
        $empleado = $this->empleado('2030-07-01');

        // Lunes 1 a domingo 14 de julio: 14 naturales, 10 de lunes a viernes, 9 sin el festivo.
        $this->ausencia($empleado, $this->vacaciones, '2030-07-01', '2030-07-14')
            ->assertCreated()
            ->assertJsonPath('ausencia.dias.naturales', 14)
            ->assertJsonPath('ausencia.dias.laborables', 9)
            ->assertJsonPath('aviso', null);

        // De alta 184 de 365 días → 30 × 184 / 365 = 15,1 días.
        $saldo = $this->saldoVacaciones($empleado, 2030);
        $this->assertEquals(15.1, $saldo['devengados']);
        $this->assertEquals(14, $saldo['programados']);
        $this->assertEquals(0, $saldo['disfrutados']);
        $this->assertEquals(1.1, $saldo['pendientes']);
        $this->assertTrue($saldo['proporcional']);

        // Pasarse del saldo avisa, pero no bloquea.
        $this->ausencia($empleado, $this->vacaciones, '2030-08-01', '2030-08-05')
            ->assertCreated()
            ->assertJsonPath('aviso', 'Atención: supera en 3.9 días los de Vacaciones de 2030.');

        // Días propios del empleado: 22 × 184 / 365 = 11,1.
        $empleado->update(['dias_vacaciones_anuales' => 22]);
        $saldo = $this->saldoVacaciones($empleado, 2030);
        $this->assertEquals(11.1, $saldo['devengados']);
        $this->assertTrue($saldo['personalizado']);
    }

    public function test_validaciones_de_fechas(): void
    {
        $empleado = $this->empleado('2030-07-01');

        // Antes del alta.
        $this->ausencia($empleado, $this->vacaciones, '2030-06-20', '2030-07-02')
            ->assertStatus(422)->assertJsonValidationErrors('fecha_inicio');

        // Fin anterior al inicio.
        $this->ausencia($empleado, $this->vacaciones, '2030-07-10', '2030-07-05')
            ->assertStatus(422)->assertJsonValidationErrors('fecha_fin');

        // Solo las bajas médicas pueden quedar abiertas.
        $this->ausencia($empleado, $this->vacaciones, '2030-07-10', null)
            ->assertStatus(422)->assertJsonValidationErrors('fecha_fin');

        $this->ausencia($empleado, $this->vacaciones, '2030-07-10', '2030-07-20')->assertCreated();

        // Solapes.
        $this->ausencia($empleado, $this->vacaciones, '2030-07-20', '2030-07-25')
            ->assertStatus(422)->assertJsonValidationErrors('fecha_inicio');
        $this->ausencia($empleado, $this->bajaMedica, '2030-07-01', null)
            ->assertStatus(422)->assertJsonValidationErrors('fecha_inicio');

        // Baja médica abierta después: sí; y nada puede empezar luego.
        $this->ausencia($empleado, $this->bajaMedica, '2030-08-01', null)
            ->assertCreated()->assertJsonPath('ausencia.abierta', true);
        $this->ausencia($empleado, $this->vacaciones, '2030-12-01', '2030-12-05')
            ->assertStatus(422)->assertJsonValidationErrors('fecha_inicio');

        // Alta médica: se cierra editando la ausencia.
        $baja = Ausencia::where('empleado_id', $empleado->id)->whereNull('fecha_fin')->firstOrFail();
        $this->actingAs($this->admin)->putJson("/api/rrhh/ausencias/{$baja->id}", [
            'rrhh_tipo_ausencia_id' => $this->bajaMedica->id,
            'fecha_inicio' => '2030-08-01',
            'fecha_fin' => '2030-08-20',
        ])->assertOk()->assertJsonPath('ausencia.abierta', false)->assertJsonPath('ausencia.dias.naturales', 20);

        // Tipo desactivado: no para ausencias nuevas.
        $tipo = RrhhTipoAusencia::create(['nombre' => 'Inactivo '.uniqid(), 'color' => 'slate', 'activo' => false]);
        $this->ausencia($empleado, $tipo, '2030-09-01', '2030-09-02')
            ->assertStatus(422)->assertJsonValidationErrors('rrhh_tipo_ausencia_id');
    }

    public function test_dar_de_baja_cierra_ausencias_y_no_deja_ausencias_posteriores(): void
    {
        $empleado = $this->empleado('2030-07-01');

        $this->ausencia($empleado, $this->vacaciones, '2030-10-01', '2030-10-05')->assertCreated();
        $this->baja($empleado, '2030-09-15')->assertStatus(422)->assertJsonValidationErrors('fecha_baja');

        $vacaciones = Ausencia::where('empleado_id', $empleado->id)->firstOrFail();
        $this->actingAs($this->admin)->deleteJson("/api/rrhh/ausencias/{$vacaciones->id}")->assertOk();

        $this->ausencia($empleado, $this->bajaMedica, '2030-09-01', null)->assertCreated();
        $this->baja($empleado, '2030-09-15')->assertOk();

        $this->assertSame('2030-09-15', Ausencia::where('empleado_id', $empleado->id)->firstOrFail()->fecha_fin->toDateString());

        // Ya de baja: fuera de un periodo de alta.
        $this->ausencia($empleado, $this->vacaciones, '2030-09-20', '2030-09-22')
            ->assertStatus(422)->assertJsonValidationErrors('fecha_inicio');
    }

    public function test_el_justificante_se_guarda_en_la_carpeta_protegida_del_empleado(): void
    {
        $empleado = $this->empleado('2030-07-01');

        $respuesta = $this->actingAs($this->admin)->post("/api/rrhh/empleados/{$empleado->id}/ausencias", [
            'rrhh_tipo_ausencia_id' => $this->bajaMedica->id,
            'fecha_inicio' => '2030-07-10',
            'fecha_fin' => '2030-07-12',
            'justificante' => UploadedFile::fake()->create('parte.pdf', 20, 'application/pdf'),
        ], ['Accept' => 'application/json'])->assertCreated();

        $file = File::findOrFail($respuesta->json('ausencia.archivo.id'));
        $carpeta = Folder::findOrFail($file->folder_id);
        $this->assertSame(CarpetasEmpleados::SISTEMA_AUSENCIAS, $carpeta->sistema);
        $this->assertSame((int) $empleado->fresh()->folder_id, (int) $carpeta->parent_id);
        $this->assertStringEndsWith('parte.pdf', $file->nombre);
        Storage::disk($file->disco)->assertExists($file->ruta);

        $this->actingAs($this->admin)->putJson("/api/folders/{$carpeta->id}", ['nombre' => 'Otra'])->assertStatus(422);

        // Borrar la ausencia no borra el justificante.
        $this->actingAs($this->admin)->deleteJson('/api/rrhh/ausencias/'.$respuesta->json('ausencia.id'))->assertOk();
        $this->assertNotNull($file->fresh());
    }

    public function test_calendario_mensual(): void
    {
        Festivo::create(['fecha' => '2030-07-03', 'nombre' => 'Festivo local']);
        $obra = Obra::forceCreate(['nombre' => 'Obra calendario '.uniqid(), 'importe_presupuestado' => 0]);
        $julio = $this->empleado('2030-07-01', [$obra->id]);
        $agosto = $this->empleado('2030-08-01');
        $this->ausencia($julio, $this->vacaciones, '2030-07-08', '2030-07-12')->assertCreated();

        $datos = $this->actingAs($this->admin)->getJson('/api/rrhh/calendario?anio=2030&mes=7')->assertOk();
        $this->assertCount(31, $datos->json('dias'));
        $this->assertSame('Festivo local', $datos->json('dias.2.festivo'));
        $this->assertSame(1, $datos->json('dias.0.semana')); // lunes

        $empleados = collect($datos->json('empleados'))->keyBy('id');
        $this->assertTrue($empleados->has($julio->id));
        $this->assertFalse($empleados->has($agosto->id)); // aún no estaba de alta
        $this->assertSame('2030-07-08', $empleados[$julio->id]['ausencias'][0]['desde']);

        $filtrado = $this->actingAs($this->admin)->getJson("/api/rrhh/calendario?anio=2030&mes=8&obra_id={$obra->id}")->json('empleados');
        $this->assertSame([$julio->id], array_column($filtrado, 'id'));
    }

    public function test_listado_indica_quien_esta_ausente_hoy(): void
    {
        $empleado = $this->empleado(now()->subDays(10)->toDateString());
        $this->ausencia($empleado, $this->vacaciones, now()->subDay()->toDateString(), now()->addDays(2)->toDateString())->assertCreated();

        $fila = collect($this->actingAs($this->admin)->getJson('/api/rrhh/empleados?search='.$empleado->dni)->json('data'))->first();
        $this->assertSame($this->vacaciones->nombre, $fila['ausencia_hoy']['tipo']);
        $this->assertSame(now()->addDays(2)->toDateString(), $fila['ausencia_hoy']['hasta']);
    }

    public function test_festivos_y_festivos_nacionales(): void
    {
        $this->actingAs($this->admin)->postJson('/api/rrhh/festivos', ['fecha' => '2031-06-24', 'nombre' => 'San Juan'])->assertCreated();
        $this->actingAs($this->admin)->postJson('/api/rrhh/festivos', ['fecha' => '2031-06-24', 'nombre' => 'Otro'])
            ->assertStatus(422)->assertJsonValidationErrors('fecha');

        $this->actingAs($this->admin)->postJson('/api/rrhh/festivos/nacionales', ['anio' => 2027])->assertOk();
        $this->assertSame('Viernes Santo', Festivo::whereDate('fecha', '2027-03-26')->value('nombre'));
        $this->assertSame(10, Festivo::whereYear('fecha', 2027)->count());
        $this->actingAs($this->admin)->postJson('/api/rrhh/festivos/nacionales', ['anio' => 2027])->assertJsonPath('creados', 0);

        $sanJuan = Festivo::whereDate('fecha', '2031-06-24')->firstOrFail();
        $this->actingAs($this->admin)->deleteJson("/api/rrhh/festivos/{$sanJuan->id}")->assertOk();
        $this->assertNull($sanJuan->fresh());
    }

    public function test_tipos_de_ausencia(): void
    {
        $base = ['nombre' => 'Formación '.uniqid(), 'color' => 'teal', 'computo' => 'laborables'];

        $this->actingAs($this->admin)->postJson('/api/rrhh/tipos-ausencia', ['color' => 'fucsia'] + $base)
            ->assertStatus(422)->assertJsonValidationErrors('color');
        $this->actingAs($this->admin)->postJson('/api/rrhh/tipos-ausencia', ['es_vacaciones' => true, 'dias_anuales' => 22] + $base)
            ->assertStatus(422)->assertJsonValidationErrors('es_vacaciones');

        $id = $this->actingAs($this->admin)->postJson('/api/rrhh/tipos-ausencia', ['dias_anuales' => 3] + $base)
            ->assertCreated()->json('tipo.id');

        $this->actingAs($this->admin)->putJson("/api/rrhh/tipos-ausencia/{$id}", ['activo' => false] + $base)
            ->assertOk()->assertJsonPath('tipo.activo', false);
    }

    // -------------------------------------------------------------
    // Fixtures
    // -------------------------------------------------------------

    private function empleado(string $fechaAlta, array $obras = []): Empleado
    {
        static $n = 0;
        $n++;
        // DNI válido y distinto en cada llamada.
        $numero = str_pad((string) (50000000 + random_int(1, 9999999)), 8, '0', STR_PAD_LEFT);
        $dni = $numero.'TRWAGMYFPDXBNJZSQVHLCKE'[(int) $numero % 23];

        $id = $this->actingAs($this->admin)->postJson('/api/rrhh/empleados', [
            'nombre' => "Empleado {$n}",
            'apellidos' => 'Prueba Ausencias',
            'dni' => $dni,
            'fecha_alta' => $fechaAlta,
            'obra_ids' => $obras,
        ])->assertCreated()->json('empleado.id');

        return Empleado::findOrFail($id);
    }

    private function ausencia(Empleado $empleado, RrhhTipoAusencia $tipo, string $inicio, ?string $fin)
    {
        return $this->actingAs($this->admin)->postJson("/api/rrhh/empleados/{$empleado->id}/ausencias", [
            'rrhh_tipo_ausencia_id' => $tipo->id,
            'fecha_inicio' => $inicio,
            'fecha_fin' => $fin,
        ]);
    }

    private function baja(Empleado $empleado, string $fecha)
    {
        return $this->actingAs($this->admin)->postJson("/api/rrhh/empleados/{$empleado->id}/baja", [
            'fecha_baja' => $fecha,
            'motivo_baja' => 'fin_contrato',
        ]);
    }

    private function saldoVacaciones(Empleado $empleado, int $anio): array
    {
        return collect($this->actingAs($this->admin)->getJson("/api/rrhh/empleados/{$empleado->id}/ausencias?anio={$anio}")
            ->assertOk()->json('saldos'))->firstWhere('tipo_id', $this->vacaciones->id);
    }
}
