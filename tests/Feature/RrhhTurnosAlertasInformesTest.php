<?php

namespace Tests\Feature;

use App\Exports\Rrhh\TablaExport;
use App\Mail\AlertasRrhhMail;
use App\Models\AsignacionTurno;
use App\Models\Ausencia;
use App\Models\Empleado;
use App\Models\Festivo;
use App\Models\Nomina;
use App\Models\Obra;
use App\Models\RrhhTipoAusencia;
use App\Models\Turno;
use App\Models\User;
use App\Services\Rrhh\AlertasRrhh;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;

/**
 * Recursos humanos (fase 4): turnos y cuadrante, alertas e informes.
 * Enero de 2030: el día 7 es lunes.
 */
class RrhhTurnosAlertasInformesTest extends TestCase
{
    use DatabaseTransactions;

    private User $admin;

    private Turno $manana;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
        Storage::fake('public');

        $this->admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'name' => 'Admin fase 4',
            'email' => 'f4-'.uniqid().'@t.es',
            'password' => 'password123',
        ]);

        $this->manana = Turno::create(['nombre' => 'Mañana test '.uniqid(), 'color' => 'amber', 'hora_inicio' => '07:00', 'hora_fin' => '15:00']);
    }

    public function test_solo_administradores(): void
    {
        $user = User::factory()->create([
            'role' => User::ROLE_USER,
            'name' => 'Usuario f4',
            'email' => 'f4-user-'.uniqid().'@t.es',
            'password' => 'password123',
        ]);

        foreach (['/api/rrhh/turnos', '/api/rrhh/cuadrante', '/api/rrhh/alertas', '/api/rrhh/informes/resumen', '/api/rrhh/informes/exportar/plantilla'] as $url) {
            $this->actingAs($user)->getJson($url)->assertForbidden();
        }
        $this->actingAs($user)->postJson('/api/rrhh/cuadrante/asignar', [])->assertForbidden();
    }

    public function test_tipos_de_turno_y_horas(): void
    {
        $r = $this->actingAs($this->admin)->postJson('/api/rrhh/turnos', [
            'nombre' => 'Partida '.uniqid(), 'color' => 'cyan',
            'hora_inicio' => '08:00', 'hora_fin' => '13:00', 'hora_inicio_2' => '14:00', 'hora_fin_2' => '17:30',
        ])->assertCreated();
        $this->assertEquals(8.5, $r->json('turno.horas'));
        $this->assertSame('08:00–13:00 / 14:00–17:30', $r->json('turno.horario'));

        // Noche: pasa de medianoche, con 30 min de descanso.
        $noche = $this->actingAs($this->admin)->postJson('/api/rrhh/turnos', [
            'nombre' => 'Noche '.uniqid(), 'color' => 'indigo', 'hora_inicio' => '22:00', 'hora_fin' => '06:00', 'descanso_minutos' => 30,
        ])->assertCreated();
        $this->assertEquals(7.5, $noche->json('turno.horas'));

        $this->actingAs($this->admin)->postJson('/api/rrhh/turnos', [
            'nombre' => 'Mal '.uniqid(), 'color' => 'cyan', 'hora_inicio' => '08:00', 'hora_fin' => '13:00', 'hora_inicio_2' => '14:00',
        ])->assertStatus(422)->assertJsonValidationErrors('hora_fin_2');
    }

    public function test_asignar_en_bloque_salta_festivos_ausencias_y_dias_sin_alta(): void
    {
        Festivo::create(['fecha' => '2030-01-08', 'nombre' => 'Festivo test']);
        $obra = Obra::forceCreate(['nombre' => 'Obra cuadrante '.uniqid(), 'importe_presupuestado' => 0]);
        $a = $this->empleado('2030-01-01');
        $b = $this->empleado('2030-01-09');
        $vacaciones = RrhhTipoAusencia::where('es_vacaciones', true)->firstOrFail();
        Ausencia::create(['empleado_id' => $a->id, 'rrhh_tipo_ausencia_id' => $vacaciones->id, 'fecha_inicio' => '2030-01-10', 'fecha_fin' => '2030-01-11']);

        $datos = [
            'empleado_ids' => [$a->id, $b->id], 'desde' => '2030-01-07', 'hasta' => '2030-01-13',
            'dias_semana' => [1, 2, 3, 4, 5], 'rrhh_turno_id' => $this->manana->id, 'obra_id' => $obra->id,
        ];

        // A: lunes y miércoles (martes festivo, jueves-viernes vacaciones). B: de miércoles a viernes.
        $this->actingAs($this->admin)->postJson('/api/rrhh/cuadrante/asignar', $datos)->assertOk()
            ->assertJsonPath('creadas', 5)
            ->assertJsonPath('omitidas.festivo', 1)
            ->assertJsonPath('omitidas.ausencia', 2)
            ->assertJsonPath('omitidas.no_alta', 2);

        // Sin sobrescribir no toca lo que ya hay; con sobrescribir, sí.
        $this->actingAs($this->admin)->postJson('/api/rrhh/cuadrante/asignar', $datos)->assertJsonPath('omitidas.ya_asignado', 5);
        $otro = Turno::create(['nombre' => 'Tarde test '.uniqid(), 'color' => 'indigo', 'hora_inicio' => '14:00', 'hora_fin' => '20:00']);
        $this->actingAs($this->admin)->postJson('/api/rrhh/cuadrante/asignar', ['rrhh_turno_id' => $otro->id, 'sobrescribir' => true] + $datos)
            ->assertJsonPath('actualizadas', 5);

        // Vista de la semana.
        $vista = $this->actingAs($this->admin)->getJson("/api/rrhh/cuadrante?desde=2030-01-07&hasta=2030-01-13&obra_id={$obra->id}")->assertOk();
        $this->assertCount(7, $vista->json('dias'));
        $this->assertSame('Festivo test', $vista->json('dias.1.festivo'));
        $filas = collect($vista->json('empleados'))->keyBy('id');
        $this->assertCount(2, $filas[$a->id]['asignaciones']);
        $this->assertCount(3, $filas[$b->id]['asignaciones']);
        $this->assertCount(1, $filas[$a->id]['ausencias']);

        // Copiar la semana a la siguiente y quitar los de A.
        $this->actingAs($this->admin)->postJson('/api/rrhh/cuadrante/copiar', ['desde' => '2030-01-07', 'hasta' => '2030-01-13', 'destino' => '2030-01-14'])
            ->assertOk()->assertJsonPath('creadas', 5);
        $this->assertSame(2, AsignacionTurno::where('empleado_id', $a->id)->whereBetween('fecha', ['2030-01-14', '2030-01-20'])->count());

        $this->actingAs($this->admin)->postJson('/api/rrhh/cuadrante/eliminar', ['empleado_ids' => [$a->id], 'desde' => '2030-01-14', 'hasta' => '2030-01-20'])
            ->assertOk()->assertJsonPath('eliminadas', 2);
    }

    public function test_alertas(): void
    {
        $hoy = CarbonImmutable::parse('2030-10-15');
        $c = $this->empleado('2030-01-01', ['fecha_fin_contrato' => '2030-10-20', 'fecha_nacimiento' => '1990-10-18']);
        $d = $this->empleado('2030-01-01', ['fecha_fin_contrato' => '2030-10-01']);

        $baja = RrhhTipoAusencia::where('es_baja_medica', true)->where('activo', true)->firstOrFail();
        Ausencia::create(['empleado_id' => $c->id, 'rrhh_tipo_ausencia_id' => $baja->id, 'fecha_inicio' => '2030-09-01']);
        Nomina::create(['empleado_id' => $c->id, 'anio' => 2030, 'mes' => 8, 'tipo' => 'mensual', 'bruto' => 1000, 'neto' => 900, 'estado' => 'pendiente']);
        $this->actingAs($this->admin)->postJson("/api/rrhh/empleados/{$c->id}/anticipos", ['tipo' => 'anticipo', 'fecha' => '2030-06-01', 'importe' => 100])->assertCreated();

        $alertas = collect(app(AlertasRrhh::class)->calcular($hoy)['alertas']);
        $de = fn ($empleado, $titulo) => $alertas->first(fn ($x) => ($x['empleado']['id'] ?? null) === $empleado->id && $x['titulo'] === $titulo);

        $this->assertSame('aviso', $de($c, 'Fin de contrato próximo')['nivel']);
        $this->assertSame('critico', $de($d, 'Contrato vencido y sigue de alta')['nivel']);
        $this->assertSame('aviso', $de($c, "{$baja->nombre} abierta")['nivel']); // 45 días
        $this->assertNotNull($de($c, 'Anticipos sin descontar'));
        $this->assertNotNull($de($c, 'Cumpleaños esta semana'));
        $this->assertNotNull($de($d, 'Vacaciones sin disfrutar'));
        $this->assertNotNull($alertas->firstWhere('titulo', 'Nóminas pendientes de pago'));
        $this->assertNotNull($alertas->firstWhere('titulo', 'Nóminas de septiembre 2030 sin registrar'));

        // Orden: primero las urgentes.
        $this->assertSame('critico', $alertas->first()['nivel']);

        $this->actingAs($this->admin)->getJson('/api/rrhh/alertas')->assertOk()->assertJsonStructure(['alertas', 'totales' => ['critico', 'aviso', 'info', 'total'], 'categorias']);
    }

    public function test_comando_de_alertas_envia_email_a_los_administradores(): void
    {
        Mail::fake();
        $this->empleado(now()->subYear()->toDateString(), ['fecha_fin_contrato' => now()->addDays(5)->toDateString()]);

        $this->artisan('rrhh:alertas')->assertSuccessful();
        Mail::assertNothingSent();

        $this->artisan('rrhh:alertas', ['--enviar' => true])->assertSuccessful();
        Mail::assertSent(AlertasRrhhMail::class, fn ($m) => $m->hasTo($this->admin->email));
    }

    public function test_fin_de_contrato_en_alta_y_edicion(): void
    {
        $this->actingAs($this->admin)->postJson('/api/rrhh/empleados', $this->datosEmpleado('2030-03-01', ['fecha_fin_contrato' => '2030-02-01']))
            ->assertStatus(422)->assertJsonValidationErrors('fecha_fin_contrato');

        $e = $this->empleado('2030-03-01', ['fecha_fin_contrato' => '2030-08-31']);
        $this->assertSame('2030-08-31', $e->periodoActual->fecha_fin_contrato->toDateString());

        $this->actingAs($this->admin)->putJson("/api/rrhh/empleados/{$e->id}", $this->datosEmpleado('2030-03-01', [
            'dni' => $e->dni, 'fecha_fin_contrato' => '2030-12-31',
        ]))->assertOk();
        $this->assertSame('2030-12-31', $e->fresh()->periodoActual->fecha_fin_contrato->toDateString());
    }

    public function test_informes(): void
    {
        $e = $this->empleado('2030-01-01');
        $obra = Obra::forceCreate(['nombre' => 'Obra informe '.uniqid(), 'importe_presupuestado' => 0]);
        AsignacionTurno::create(['empleado_id' => $e->id, 'fecha' => '2030-01-07', 'rrhh_turno_id' => $this->manana->id, 'obra_id' => $obra->id]);
        AsignacionTurno::create(['empleado_id' => $e->id, 'fecha' => '2030-01-08', 'rrhh_turno_id' => $this->manana->id, 'obra_id' => $obra->id]);
        Nomina::create(['empleado_id' => $e->id, 'anio' => 2030, 'mes' => 1, 'tipo' => 'mensual', 'bruto' => 1500, 'neto' => 1200, 'estado' => 'pagada', 'fecha_pago' => '2030-01-31']);

        $r = $this->actingAs($this->admin)->getJson('/api/rrhh/informes/resumen?anio=2030')->assertOk();
        $this->assertEquals(16, collect($r->json('horas_obra'))->firstWhere('obra', $obra->nombre)['horas']);
        $this->assertEquals(1500, $r->json('nominas.por_mes.0.bruto'));
        $this->assertGreaterThanOrEqual(1, $r->json('altas'));

        Excel::fake();

        $this->actingAs($this->admin)->get('/api/rrhh/informes/exportar/horas-obra?desde=2030-01-01&hasta=2030-01-31')->assertOk();
        Excel::assertDownloaded('rrhh_horas-obra_2030-01-01_2030-01-31.xlsx', function (TablaExport $x) use ($obra) {
            $fila = collect($x->array())->first(fn ($f) => $f[0] === $obra->nombre);

            return $fila && $fila[3] === 2 && $fila[4] == 16;
        });

        $this->actingAs($this->admin)->get('/api/rrhh/informes/exportar/nominas?anio=2030')->assertOk();
        Excel::assertDownloaded('rrhh_nominas_2030.xlsx', fn (TablaExport $x) => collect($x->array())->contains(fn ($f) => $f[2] === $e->dni && $f[4] == 1500));

        foreach (['plantilla' => '', 'formacion' => '', 'vacaciones' => '?anio=2030', 'ausencias' => '?desde=2030-01-01&hasta=2030-12-31', 'altas-bajas' => '?desde=2030-01-01&hasta=2030-12-31'] as $tipo => $q) {
            $this->actingAs($this->admin)->get("/api/rrhh/informes/exportar/{$tipo}{$q}")->assertOk();
        }

        $this->actingAs($this->admin)->getJson('/api/rrhh/informes/exportar/ausencias')->assertStatus(422)->assertJsonValidationErrors(['desde', 'hasta']);
        $this->actingAs($this->admin)->getJson('/api/rrhh/informes/exportar/inventado')->assertNotFound();
    }

    // -------------------------------------------------------------

    private function datosEmpleado(string $fechaAlta, array $extra = []): array
    {
        $numero = (string) (70000000 + random_int(1, 9999999));

        return array_merge([
            'nombre' => 'Empleado',
            'apellidos' => 'Fase4 '.uniqid(),
            'dni' => $numero.'TRWAGMYFPDXBNJZSQVHLCKE'[(int) $numero % 23],
            'fecha_alta' => $fechaAlta,
        ], $extra);
    }

    private function empleado(string $fechaAlta, array $extra = []): Empleado
    {
        $id = $this->actingAs($this->admin)->postJson('/api/rrhh/empleados', $this->datosEmpleado($fechaAlta, $extra))
            ->assertCreated()->json('empleado.id');

        return Empleado::findOrFail($id);
    }
}
