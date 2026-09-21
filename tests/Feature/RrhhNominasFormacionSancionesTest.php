<?php

namespace Tests\Feature;

use App\Models\Anticipo;
use App\Models\Empleado;
use App\Models\File;
use App\Models\Folder;
use App\Models\Nomina;
use App\Models\User;
use App\Services\Rrhh\CarpetasEmpleados;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/** Recursos humanos (fase 3): nóminas, anticipos y vales, formación y sanciones. */
class RrhhNominasFormacionSancionesTest extends TestCase
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
            'name' => 'Admin fase 3',
            'email' => 'f3-'.uniqid().'@t.es',
            'password' => 'password123',
        ]);
    }

    public function test_solo_administradores(): void
    {
        $user = User::factory()->create([
            'role' => User::ROLE_USER,
            'name' => 'Usuario f3',
            'email' => 'f3-user-'.uniqid().'@t.es',
            'password' => 'password123',
        ]);
        $e = $this->empleado('2030-01-01');

        foreach (['/api/rrhh/nominas', "/api/rrhh/empleados/{$e->id}/nominas", "/api/rrhh/empleados/{$e->id}/anticipos",
            "/api/rrhh/empleados/{$e->id}/cursos", "/api/rrhh/empleados/{$e->id}/sanciones"] as $url) {
            $this->actingAs($user)->getJson($url)->assertForbidden();
        }
    }

    public function test_nomina_con_pdf_y_anticipos_descontados(): void
    {
        $e = $this->empleado('2030-01-01');
        $a1 = $this->anticipo($e, '2030-03-05', 100)->assertCreated()->json('anticipo.id');
        $a2 = $this->anticipo($e, '2030-03-20', 50.5)->assertCreated()->json('anticipo.id');

        $r = $this->actingAs($this->admin)->post("/api/rrhh/empleados/{$e->id}/nominas", $this->datosNomina([
            'anticipo_ids' => [$a1, $a2],
            'neto' => 1249.5 - 150.5,
            'archivo' => UploadedFile::fake()->create('nomina-marzo.pdf', 30, 'application/pdf'),
        ]), ['Accept' => 'application/json'])->assertCreated();

        $r->assertJsonPath('nomina.anticipos', '150.50')
            ->assertJsonCount(2, 'nomina.anticipos_descontados')
            ->assertJsonPath('aviso', null);

        // El PDF va a "Recibos de nómina" (protegida) con nombre legible.
        $file = File::findOrFail($r->json('nomina.archivo.id'));
        $carpeta = Folder::findOrFail($file->folder_id);
        $this->assertSame(CarpetasEmpleados::SISTEMA_NOMINAS, $carpeta->sistema);
        $this->assertStringStartsWith('2030-03 Nómina mensual - ', $file->nombre);
        $this->actingAs($this->admin)->deleteJson("/api/folders/{$carpeta->id}", ['password' => 'password123'])->assertStatus(422);

        // Descontados: no se pueden editar ni borrar.
        $this->actingAs($this->admin)->deleteJson("/api/rrhh/anticipos/{$a1}")->assertStatus(422)->assertJsonValidationErrors('anticipo');

        // Un anticipo ya descontado no puede ir a otra nómina.
        $this->actingAs($this->admin)->postJson("/api/rrhh/empleados/{$e->id}/nominas", $this->datosNomina([
            'tipo' => 'extra', 'anticipo_ids' => [$a1],
        ]))->assertStatus(422)->assertJsonValidationErrors('anticipo_ids');

        // Quitarlo al editar lo deja pendiente otra vez.
        $nominaId = $r->json('nomina.id');
        $this->actingAs($this->admin)->putJson("/api/rrhh/nominas/{$nominaId}", $this->datosNomina(['anticipo_ids' => [$a2], 'neto' => 1199]))
            ->assertOk()->assertJsonPath('nomina.anticipos', '50.50');
        $this->assertNull(Anticipo::find($a1)->nomina_id);

        // Borrar la nómina libera el resto de anticipos y conserva el PDF.
        $this->actingAs($this->admin)->deleteJson("/api/rrhh/nominas/{$nominaId}")->assertOk();
        $this->assertNull(Anticipo::find($a2)->nomina_id);
        $this->assertNotNull($file->fresh());
    }

    public function test_validaciones_de_nomina(): void
    {
        $e = $this->empleado('2030-03-15');

        // Antes del alta.
        $this->nomina($e, ['mes' => 2])->assertStatus(422)->assertJsonValidationErrors('mes');

        // Pagada exige fecha.
        $this->nomina($e, ['estado' => 'pagada'])->assertStatus(422)->assertJsonValidationErrors('fecha_pago');

        $this->nomina($e)->assertCreated();
        $this->nomina($e)->assertStatus(422)->assertJsonValidationErrors('mes'); // repetida
        $this->nomina($e, ['tipo' => 'extra'])->assertCreated(); // otro tipo, mismo mes: sí

        // Neto que no cuadra: avisa, no bloquea.
        $this->nomina($e, ['mes' => 4, 'neto' => 1000])->assertCreated()
            ->assertJsonPath('aviso', fn ($aviso) => str_contains($aviso, 'no coincide'));

        // Anticipo de otro empleado: no.
        $otro = $this->empleado('2030-01-01');
        $ajeno = $this->anticipo($otro, '2030-03-20', 20)->json('anticipo.id');
        $this->nomina($e, ['mes' => 5, 'anticipo_ids' => [$ajeno]])->assertStatus(422)->assertJsonValidationErrors('anticipo_ids');
    }

    public function test_vista_del_mes_y_marcar_pagadas(): void
    {
        $con = $this->empleado('2030-01-01');
        $sin = $this->empleado('2030-01-01');
        $this->empleado('2030-06-01'); // aún no estaba de alta en marzo
        $this->anticipo($sin, '2030-03-10', 75)->assertCreated();

        $id = $this->nomina($con)->assertCreated()->json('nomina.id');

        $mes = $this->actingAs($this->admin)->getJson('/api/rrhh/nominas?anio=2030&mes=3&search=Fase3')->assertOk();
        $filas = collect($mes->json('empleados'))->keyBy('id');
        $this->assertCount(2, $filas);
        $this->assertCount(1, $filas[$con->id]['nominas']);
        $this->assertEquals(75, $filas[$sin->id]['anticipos_pendientes']);
        $this->assertSame(1, $mes->json('totales.sin_nomina'));
        $this->assertEquals(1249.5, $mes->json('totales.neto'));

        $this->actingAs($this->admin)->postJson('/api/rrhh/nominas/marcar-pagadas', ['ids' => [$id], 'fecha_pago' => '2030-03-31'])
            ->assertOk()->assertJsonPath('actualizadas', 1);
        $this->assertSame(Nomina::ESTADO_PAGADA, Nomina::find($id)->estado);
        $this->assertSame('2030-03-31', Nomina::find($id)->fecha_pago->toDateString());
    }

    public function test_formacion_con_caducidad_y_renovacion(): void
    {
        $e = $this->empleado(now()->subYears(3)->toDateString());
        $url = "/api/rrhh/empleados/{$e->id}/cursos";

        $this->actingAs($this->admin)->postJson($url, [
            'nombre' => 'PRL 20h', 'categoria' => 'prl', 'fecha' => '2030-01-10', 'fecha_caducidad' => '2030-01-01',
        ])->assertStatus(422)->assertJsonValidationErrors('fecha_caducidad');

        $viejo = $this->actingAs($this->admin)->postJson($url, [
            'nombre' => 'Carretillero', 'categoria' => 'carnet', 'horas' => 8,
            'fecha' => now()->subYears(2)->toDateString(), 'fecha_caducidad' => now()->addDays(10)->toDateString(),
        ])->assertCreated()->json('curso.id');

        $pendientes = collect($this->actingAs($this->admin)->getJson('/api/rrhh/documentacion-pendiente')->json('formacion'));
        $this->assertSame('proximo', $pendientes->firstWhere('id', $viejo)['estado']);

        // Uno más reciente con el mismo nombre lo renueva y deja de avisar.
        $this->actingAs($this->admin)->post($url, [
            'nombre' => 'carretillero', 'categoria' => 'carnet', 'horas' => 8,
            'fecha' => now()->toDateString(), 'fecha_caducidad' => now()->addYears(5)->toDateString(),
            'archivo' => UploadedFile::fake()->create('certificado.pdf', 10, 'application/pdf'),
        ], ['Accept' => 'application/json'])->assertCreated();

        $lista = collect($this->actingAs($this->admin)->getJson($url)->assertOk()->json('cursos'))->keyBy('id');
        $this->assertSame('renovado', $lista[$viejo]['estado']);
        $this->assertEquals(16, $this->actingAs($this->admin)->getJson($url)->json('horas'));
        $pendientes = collect($this->actingAs($this->admin)->getJson('/api/rrhh/documentacion-pendiente')->json('formacion'));
        $this->assertNull($pendientes->firstWhere('id', $viejo));
    }

    public function test_sanciones(): void
    {
        $e = $this->empleado('2030-01-01');
        $url = "/api/rrhh/empleados/{$e->id}/sanciones";
        $base = ['fecha_hechos' => '2030-02-10', 'gravedad' => 'grave', 'descripcion' => 'Retrasos reiterados', 'estado' => 'vigente'];

        $this->actingAs($this->admin)->postJson($url, $base + ['tipo' => 'suspension'])
            ->assertStatus(422)->assertJsonValidationErrors('dias_suspension');
        $this->actingAs($this->admin)->postJson($url, ['fecha_hechos' => '2029-12-01', 'tipo' => 'amonestacion_escrita'] + $base)
            ->assertStatus(422)->assertJsonValidationErrors('fecha_hechos');

        $id = $this->actingAs($this->admin)->post($url, $base + [
            'tipo' => 'suspension', 'dias_suspension' => 3, 'fecha_inicio_suspension' => '2030-02-20',
            'archivo' => UploadedFile::fake()->create('carta.pdf', 10, 'application/pdf'),
        ], ['Accept' => 'application/json'])->assertCreated()->json('sancion.id');

        // Si deja de ser suspensión se limpian los días.
        $this->actingAs($this->admin)->putJson("/api/rrhh/sanciones/{$id}", $base + ['tipo' => 'amonestacion_escrita', 'dias_suspension' => 3])
            ->assertOk()->assertJsonPath('sancion.dias_suspension', null);

        $this->actingAs($this->admin)->getJson($url)->assertOk()->assertJsonCount(1, 'sanciones')
            ->assertJsonPath('prescripcion.muy_grave', 60);
        $this->actingAs($this->admin)->deleteJson("/api/rrhh/sanciones/{$id}")->assertOk();
    }

    // -------------------------------------------------------------
    // Fixtures
    // -------------------------------------------------------------

    private function empleado(string $fechaAlta): Empleado
    {
        $numero = (string) (60000000 + random_int(1, 9999999));
        $dni = $numero.'TRWAGMYFPDXBNJZSQVHLCKE'[(int) $numero % 23];

        $id = $this->actingAs($this->admin)->postJson('/api/rrhh/empleados', [
            'nombre' => 'Empleado',
            'apellidos' => 'Fase3 '.uniqid(),
            'dni' => $dni,
            'fecha_alta' => $fechaAlta,
        ])->assertCreated()->json('empleado.id');

        return Empleado::findOrFail($id);
    }

    private function datosNomina(array $cambios = []): array
    {
        return array_merge([
            'anio' => 2030, 'mes' => 3, 'tipo' => 'mensual',
            'bruto' => 1600, 'irpf' => 200, 'seguridad_social' => 102, 'otras_deducciones' => 48.5,
            'neto' => 1249.5, 'estado' => 'pendiente',
        ], $cambios);
    }

    private function nomina(Empleado $e, array $cambios = [])
    {
        return $this->actingAs($this->admin)->postJson("/api/rrhh/empleados/{$e->id}/nominas", $this->datosNomina($cambios));
    }

    private function anticipo(Empleado $e, string $fecha, float $importe)
    {
        return $this->actingAs($this->admin)->postJson("/api/rrhh/empleados/{$e->id}/anticipos", [
            'tipo' => 'anticipo', 'fecha' => $fecha, 'importe' => $importe, 'forma_pago' => 'efectivo',
        ]);
    }
}
