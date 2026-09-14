<?php

namespace Tests\Feature;

use App\Livewire\Documentos\Index as DocumentosIndex;
use App\Models\Certificacion;
use App\Models\CertificacionDetalle;
use App\Models\Cliente;
use App\Models\Documento;
use App\Models\GastoInicialPartida;
use App\Models\Obra;
use App\Models\ObraGastoCategoria;
use App\Models\ObraPresupuestoVenta;
use App\Models\PresupuestoVentaPartida;
use App\Models\Tarea;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Comprueba que los recursos anidados en la URL pertenecen a su padre
 * (línea → certificación, partida/capítulo/documento → obra, tarea → usuario)
 * y que el uso legítimo sigue funcionando.
 * Usa DatabaseTransactions: todo lo creado se revierte al terminar cada test.
 */
class IntegridadPertenenciaTest extends TestCase
{
    use DatabaseTransactions;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = $this->usuario(User::ROLE_ADMIN);
        Sanctum::actingAs($this->admin);
    }

    // -------------------------------------------------------------
    // Certificaciones: líneas
    // -------------------------------------------------------------

    public function test_no_se_edita_ni_borra_una_linea_de_otra_certificacion(): void
    {
        $obra = $this->obra();
        $oficio = $this->oficio($obra);
        $partida = $this->partidaVenta($obra, $oficio);

        $pendiente = $this->certificacion($obra, $oficio, 'pendiente');
        $aceptada = $this->certificacion($obra, $oficio, 'aceptada');
        $lineaAceptada = $this->linea($aceptada, $partida);

        $this->putJson("/api/certificaciones/{$pendiente->id}/lineas/{$lineaAceptada->id}", [
            'concepto' => 'Cambiado',
            'unidad' => 'm2',
            'cantidad' => 99,
            'precio_unitario' => 10,
            'forzar' => true,
        ])->assertStatus(422);

        $this->deleteJson("/api/certificaciones/{$pendiente->id}/lineas/{$lineaAceptada->id}")
            ->assertStatus(422);

        $lineaAceptada->refresh();
        $this->assertSame('Linea', $lineaAceptada->concepto);
        $this->assertEquals(5, $lineaAceptada->cantidad);
    }

    public function test_se_edita_y_borra_una_linea_propia(): void
    {
        $obra = $this->obra();
        $oficio = $this->oficio($obra);
        $partida = $this->partidaVenta($obra, $oficio);
        $cert = $this->certificacion($obra, $oficio, 'pendiente');
        $linea = $this->linea($cert, $partida);

        $this->putJson("/api/certificaciones/{$cert->id}/lineas/{$linea->id}", [
            'concepto' => 'Cambiado',
            'unidad' => 'm2',
            'cantidad' => 8,
            'precio_unitario' => 10,
        ])->assertOk();

        $this->assertEquals(8, $linea->fresh()->cantidad);
        $this->assertEquals(80, $cert->fresh()->base_imponible);

        $this->deleteJson("/api/certificaciones/{$cert->id}/lineas/{$linea->id}")->assertOk();
        $this->assertNull($linea->fresh());
        $this->assertEquals(0, $cert->fresh()->base_imponible);
    }

    public function test_no_se_certifica_una_partida_de_otra_obra_u_oficio(): void
    {
        $obra = $this->obra();
        $oficio = $this->oficio($obra);
        $cert = $this->certificacion($obra, $oficio, 'pendiente');

        $otraObra = $this->obra();
        $partidaOtraObra = $this->partidaVenta($otraObra, $this->oficio($otraObra));
        $partidaOtroOficio = $this->partidaVenta($obra, $this->oficio($obra));

        foreach ([$partidaOtraObra, $partidaOtroOficio] as $partida) {
            $this->postJson("/api/certificaciones/{$cert->id}/lineas", [
                'presupuesto_venta_partida_id' => $partida->id,
                'cantidad' => 1,
            ])->assertStatus(422);
        }

        $this->assertSame(0, $cert->detalles()->count());
    }

    public function test_se_certifica_una_partida_propia(): void
    {
        $obra = $this->obra();
        $oficio = $this->oficio($obra);
        $cert = $this->certificacion($obra, $oficio, 'pendiente');
        $partida = $this->partidaVenta($obra, $oficio);

        $this->postJson("/api/certificaciones/{$cert->id}/lineas", [
            'presupuesto_venta_partida_id' => $partida->id,
            'cantidad' => 3,
        ])->assertOk();

        $this->assertSame(1, $cert->detalles()->count());
        $this->assertEquals(30, $cert->fresh()->base_imponible);
    }

    // -------------------------------------------------------------
    // Certificaciones: creación y capítulos
    // -------------------------------------------------------------

    public function test_no_se_crea_certificacion_con_oficio_de_otra_obra(): void
    {
        $obra = $this->obra();
        $oficioAjeno = $this->oficio($this->obra());

        $this->postJson("/api/obras/{$obra->id}/certificaciones", [
            'cliente_id' => $this->cliente()->id,
            'obra_gasto_categoria_id' => $oficioAjeno->id,
            'fecha_ingreso' => now()->toDateString(),
        ])->assertStatus(422)->assertJsonValidationErrors('obra_gasto_categoria_id');

        $this->assertSame(0, Certificacion::where('obra_id', $obra->id)->count());
    }

    public function test_se_crea_certificacion_con_oficio_propio(): void
    {
        $obra = $this->obra();

        $this->postJson("/api/obras/{$obra->id}/certificaciones", [
            'cliente_id' => $this->cliente()->id,
            'obra_gasto_categoria_id' => $this->oficio($obra)->id,
            'fecha_ingreso' => now()->toDateString(),
            'numero_certificacion' => 'T-1',
        ])->assertCreated();

        $this->assertSame(1, Certificacion::where('obra_id', $obra->id)->count());
    }

    public function test_capitulos_solo_dentro_de_la_misma_obra(): void
    {
        $obra = $this->obra();
        $base = $this->certificacion($obra, $this->oficio($obra), 'pendiente', 'N-1');

        $otraObra = $this->obra();
        $baseAjena = $this->certificacion($otraObra, $this->oficio($otraObra), 'pendiente', 'N-9');

        // Certificación base de otra obra
        $this->postJson("/api/obras/{$obra->id}/certificaciones/capitulo", [
            'certificacion_id' => $baseAjena->id,
            'obra_gasto_categoria_id' => $this->oficio($obra)->id,
        ])->assertStatus(422)->assertJsonValidationErrors('certificacion_id');

        // Oficio de otra obra
        $this->postJson("/api/obras/{$obra->id}/certificaciones/capitulo", [
            'certificacion_id' => $base->id,
            'obra_gasto_categoria_id' => $this->oficio($otraObra)->id,
        ])->assertStatus(422)->assertJsonValidationErrors('obra_gasto_categoria_id');

        // Uso legítimo
        $this->postJson("/api/obras/{$obra->id}/certificaciones/capitulo", [
            'certificacion_id' => $base->id,
            'obra_gasto_categoria_id' => $this->oficio($obra)->id,
        ])->assertCreated();

        $this->assertSame(2, Certificacion::where('obra_id', $obra->id)->where('numero_certificacion', 'N-1')->count());
        $this->assertSame(1, Certificacion::where('obra_id', $otraObra->id)->count());
    }

    // -------------------------------------------------------------
    // Presupuesto de venta y coste teórico
    // -------------------------------------------------------------

    public function test_partida_de_venta_de_otra_obra_devuelve_404(): void
    {
        $obra = $this->obra();
        $otraObra = $this->obra();
        $partidaAjena = $this->partidaVenta($otraObra, $this->oficio($otraObra));

        $this->putJson("/api/obras/{$obra->id}/presupuesto-venta/partidas/{$partidaAjena->id}", [
            'descripcion' => 'Cambiada',
            'medicion' => 1,
            'precio_unitario' => 1,
        ])->assertNotFound();

        $this->deleteJson("/api/obras/{$obra->id}/presupuesto-venta/partidas/{$partidaAjena->id}")
            ->assertNotFound();

        $this->assertSame('Partida', $partidaAjena->fresh()->descripcion);
    }

    public function test_partida_de_venta_propia_se_edita_y_borra(): void
    {
        $obra = $this->obra();
        $partida = $this->partidaVenta($obra, $this->oficio($obra));

        $this->putJson("/api/obras/{$obra->id}/presupuesto-venta/partidas/{$partida->id}", [
            'descripcion' => 'Cambiada',
            'medicion' => 2,
            'precio_unitario' => 3,
        ])->assertOk();
        $this->assertSame('Cambiada', $partida->fresh()->descripcion);

        $this->deleteJson("/api/obras/{$obra->id}/presupuesto-venta/partidas/{$partida->id}")->assertOk();
        $this->assertNull($partida->fresh());
    }

    public function test_coste_teorico_de_otra_obra_devuelve_404(): void
    {
        $obra = $this->obra();
        $otraObra = $this->obra();
        $oficioAjeno = $this->oficio($otraObra);
        $partidaAjena = GastoInicialPartida::forceCreate([
            'obra_id' => $otraObra->id,
            'obra_gasto_categoria_id' => $oficioAjeno->id,
            'descripcion' => 'Coste',
            'medicion' => 1,
            'precio_unitario' => 1,
        ]);

        $payloadPartida = ['descripcion' => 'X', 'medicion' => 1, 'precio_unitario' => 1];

        $this->putJson("/api/obras/{$obra->id}/gastos-iniciales/partidas/{$partidaAjena->id}", $payloadPartida)
            ->assertNotFound();
        $this->deleteJson("/api/obras/{$obra->id}/gastos-iniciales/partidas/{$partidaAjena->id}")
            ->assertNotFound();

        $oficioVacioAjeno = $this->oficio($otraObra);
        $this->putJson("/api/obras/{$obra->id}/capitulos/{$oficioVacioAjeno->id}", ['nombre' => 'Hack'])
            ->assertNotFound();
        $this->deleteJson("/api/obras/{$obra->id}/capitulos/{$oficioVacioAjeno->id}")
            ->assertNotFound();

        $this->assertSame('Coste', $partidaAjena->fresh()->descripcion);
        $this->assertNotSame('Hack', $oficioVacioAjeno->fresh()->nombre);
    }

    public function test_coste_teorico_propio_se_edita_y_borra(): void
    {
        $obra = $this->obra();
        $oficio = $this->oficio($obra);
        $partida = GastoInicialPartida::forceCreate([
            'obra_id' => $obra->id,
            'obra_gasto_categoria_id' => $oficio->id,
            'descripcion' => 'Coste',
            'medicion' => 1,
            'precio_unitario' => 1,
        ]);

        $this->putJson("/api/obras/{$obra->id}/gastos-iniciales/partidas/{$partida->id}", [
            'descripcion' => 'Coste 2', 'medicion' => 1, 'precio_unitario' => 1,
        ])->assertOk();
        $this->deleteJson("/api/obras/{$obra->id}/gastos-iniciales/partidas/{$partida->id}")->assertOk();
        $this->assertNull($partida->fresh());

        $this->putJson("/api/obras/{$obra->id}/capitulos/{$oficio->id}", ['nombre' => 'Renombrado'])->assertOk();
        $this->deleteJson("/api/obras/{$obra->id}/capitulos/{$oficio->id}")->assertOk();
        $this->assertNull($oficio->fresh());
    }

    // -------------------------------------------------------------
    // Tareas
    // -------------------------------------------------------------

    public function test_usuario_normal_solo_accede_a_sus_tareas(): void
    {
        $normal = $this->usuario(User::ROLE_USER);
        $otro = $this->usuario(User::ROLE_USER);

        $ajena = Tarea::forceCreate(['titulo' => 'Ajena', 'asignado_a' => $otro->id, 'creado_por' => $otro->id]);
        $asignada = Tarea::forceCreate(['titulo' => 'Mía', 'asignado_a' => $normal->id, 'creado_por' => $otro->id]);

        $payload = fn (Tarea $t) => [
            'titulo' => 'Editada', 'prioridad' => 'alta', 'estado' => 'en_curso', 'asignado_a' => $t->asignado_a,
        ];

        Sanctum::actingAs($normal);

        $this->getJson("/api/tareas/{$ajena->id}")->assertForbidden();
        $this->putJson("/api/tareas/{$ajena->id}", $payload($ajena))->assertForbidden();
        $this->patchJson("/api/tareas/{$ajena->id}/estado", ['estado' => 'completada'])->assertForbidden();
        $this->assertSame('Ajena', $ajena->fresh()->titulo);
        $this->assertSame('pendiente', $ajena->fresh()->estado);

        $this->getJson("/api/tareas/{$asignada->id}")->assertOk();
        $this->putJson("/api/tareas/{$asignada->id}", $payload($asignada))->assertOk();
        $this->patchJson("/api/tareas/{$asignada->id}/estado", ['estado' => 'completada'])->assertOk();

        Sanctum::actingAs($this->admin);
        $this->getJson("/api/tareas/{$ajena->id}")->assertOk();
        $this->patchJson("/api/tareas/{$ajena->id}/estado", ['estado' => 'en_curso'])->assertOk();
    }

    // -------------------------------------------------------------
    // Documentos de obra (Livewire)
    // -------------------------------------------------------------

    public function test_no_se_borra_un_documento_de_otra_obra(): void
    {
        Storage::fake('public');
        $this->actingAs($this->admin);

        $obra = $this->obra();
        $docAjeno = Documento::forceCreate([
            'obra_id' => $this->obra()->id,
            'tipo' => 'CONTRATO',
            'archivo' => 'documentos/ajeno.pdf',
        ]);
        $docPropio = Documento::forceCreate([
            'obra_id' => $obra->id,
            'tipo' => 'CONTRATO',
            'archivo' => 'documentos/propio.pdf',
        ]);

        try {
            Livewire::test(DocumentosIndex::class, ['obra' => $obra])
                ->set('documentoAEliminarId', $docAjeno->id)
                ->call('eliminar');
        } catch (\Throwable) {
            // findOrFail lanza 404: lo importante es que no se borre nada.
        }
        $this->assertNotNull($docAjeno->fresh());

        Livewire::test(DocumentosIndex::class, ['obra' => $obra])
            ->set('documentoAEliminarId', $docPropio->id)
            ->call('eliminar');
        $this->assertNull($docPropio->fresh());
    }

    // -------------------------------------------------------------
    // Fixtures
    // -------------------------------------------------------------

    private function usuario(string $role): User
    {
        return User::factory()->create([
            'role' => $role,
            'email' => $role.'-'.uniqid().'@t.es',
            'password' => 'password123',
        ]);
    }

    private function obra(): Obra
    {
        return Obra::forceCreate(['nombre' => 'Obra test '.uniqid(), 'importe_presupuestado' => 0]);
    }

    private function oficio(Obra $obra): ObraGastoCategoria
    {
        return ObraGastoCategoria::forceCreate(['obra_id' => $obra->id, 'nombre' => 'Oficio '.uniqid()]);
    }

    private function cliente(): Cliente
    {
        return Cliente::forceCreate(['nombre' => 'Cliente test '.uniqid()]);
    }

    private function certificacion(Obra $obra, ObraGastoCategoria $oficio, string $estado, ?string $numero = null): Certificacion
    {
        return Certificacion::forceCreate([
            'obra_id' => $obra->id,
            'cliente_id' => $this->cliente()->id,
            'obra_gasto_categoria_id' => $oficio->id,
            'fecha_ingreso' => now()->toDateString(),
            'numero_certificacion' => $numero ?? 'C-'.uniqid(),
            'iva_porcentaje' => 21,
            'retencion_porcentaje' => 0,
            'base_imponible' => 0,
            'iva_importe' => 0,
            'retencion_importe' => 0,
            'total' => 0,
            'estado_certificacion' => $estado,
            'estado_factura' => 'pendiente',
        ]);
    }

    private function partidaVenta(Obra $obra, ObraGastoCategoria $oficio): PresupuestoVentaPartida
    {
        $capitulo = ObraPresupuestoVenta::firstOrCreate([
            'obra_id' => $obra->id,
            'obra_gasto_categoria_id' => $oficio->id,
        ]);

        return PresupuestoVentaPartida::forceCreate([
            'obra_presupuesto_venta_id' => $capitulo->id,
            'obra_id' => $obra->id,
            'descripcion' => 'Partida',
            'unidad' => 'm2',
            'medicion' => 100,
            'precio_unitario' => 10,
        ]);
    }

    private function linea(Certificacion $cert, PresupuestoVentaPartida $partida): CertificacionDetalle
    {
        return CertificacionDetalle::forceCreate([
            'certificacion_id' => $cert->id,
            'presupuesto_venta_partida_id' => $partida->id,
            'concepto' => 'Linea',
            'unidad' => 'm2',
            'cantidad' => 5,
            'precio_unitario' => 10,
            'importe_linea' => 50,
        ]);
    }
}
