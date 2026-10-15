<?php

namespace Tests\Feature;

use App\Livewire\Empresa\FacturasVentas\Detalle;
use App\Models\Cliente;
use App\Models\FacturaSerie;
use App\Models\FacturaVenta;
use App\Models\FacturaVentaDetalle;
use App\Models\FacturaVentaEliminada;
use App\Models\FacturaVentaPago;
use App\Models\Obra;
use App\Models\User;
use App\Services\FacturaVentaService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use RuntimeException;
use Tests\TestCase;

/**
 * Eliminar una factura de venta: solo administradores, con contraseña, solo
 * la última de la serie, devolviendo su número y dejando rastro.
 */
class EliminarFacturaVentaTest extends TestCase
{
    use DatabaseTransactions;

    private User $admin;

    private Obra $obra;

    private Cliente $cliente;

    private FacturaSerie $serie;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

        $this->admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'name' => 'Admin facturas',
            'email' => 'borrar-factura-'.uniqid().'@t.es',
            'password' => 'password123',
        ]);

        $this->obra = Obra::forceCreate(['nombre' => 'Obra borrado '.uniqid(), 'importe_presupuestado' => 0]);
        $this->cliente = Cliente::forceCreate(['nombre' => 'Cliente borrado '.uniqid()]);
        $this->serie = FacturaSerie::forceCreate([
            'serie' => 'B'.substr(uniqid(), -6),
            'ultimo_numero' => 40,
            'activa' => true,
        ]);
    }

    /** Factura emitida con el siguiente número de la serie. */
    private function factura(array $extra = []): FacturaVenta
    {
        $numero = $this->serie->refresh()->ultimo_numero + 1;
        $this->serie->update(['ultimo_numero' => $numero]);

        $factura = FacturaVenta::forceCreate(array_merge([
            'serie' => $this->serie->serie,
            'numero_factura' => $numero,
            'estado' => FacturaVenta::ESTADO_EMITIDA,
            'origen' => 'manual',
            'fecha_emision' => now(),
            'fecha_contable' => now(),
            'obra_id' => $this->obra->id,
            'cliente_id' => $this->cliente->id,
            'base_imponible' => 1000,
            'iva_porcentaje' => 21,
            'iva_importe' => 210,
            'retencion_porcentaje' => 0,
            'retencion_importe' => 0,
            'total' => 1210,
        ], $extra));

        FacturaVentaDetalle::forceCreate([
            'factura_venta_id' => $factura->id,
            'orden' => 1,
            'concepto' => 'Trabajos de prueba',
            'cantidad' => 1,
            'unidad' => '1',
            'precio_unitario' => 1000,
            'importe_linea' => 1000,
        ]);

        return $factura->fresh();
    }

    private function componente(FacturaVenta $factura, ?User $como = null)
    {
        return Livewire::actingAs($como ?? $this->admin)->test(Detalle::class, ['factura' => $factura]);
    }

    // -------------------------------------------------------------

    public function test_un_usuario_normal_no_puede_eliminar(): void
    {
        $user = User::factory()->create([
            'role' => User::ROLE_USER,
            'name' => 'Usuario raso',
            'email' => 'raso-'.uniqid().'@t.es',
            'password' => 'password123',
        ]);
        $factura = $this->factura();

        $this->componente($factura, $user)
            ->assertSet('puedeEliminarFactura', false)
            ->set('motivoEliminar', 'probando a colarme')
            ->set('passwordEliminar', 'password123')
            ->call('eliminarFactura')
            ->assertForbidden();

        $this->assertNotNull($factura->fresh());
    }

    public function test_hace_falta_la_contrasena_y_un_motivo(): void
    {
        $factura = $this->factura();

        // Sin nada.
        $this->componente($factura)
            ->call('eliminarFactura')
            ->assertHasErrors(['motivoEliminar', 'passwordEliminar']);

        // Contraseña equivocada.
        $this->componente($factura)
            ->set('motivoEliminar', 'factura de prueba creada por error')
            ->set('passwordEliminar', 'la-que-no-es')
            ->call('eliminarFactura')
            ->assertHasErrors('passwordEliminar');

        // Motivo demasiado corto.
        $this->componente($factura)
            ->set('motivoEliminar', 'eh')
            ->set('passwordEliminar', 'password123')
            ->call('eliminarFactura')
            ->assertHasErrors('motivoEliminar');

        $this->assertNotNull($factura->fresh());
        $this->assertEquals($factura->numero_factura, $this->serie->fresh()->ultimo_numero);
    }

    public function test_elimina_la_ultima_y_devuelve_su_numero_a_la_serie(): void
    {
        $factura = $this->factura();
        $numero = $factura->numero_factura;

        FacturaVentaPago::forceCreate([
            'factura_venta_id' => $factura->id,
            'fecha_pago' => now(),
            'importe' => 100,
            'metodo' => 'transferencia',
        ]);

        $this->componente($factura->fresh())
            ->set('motivoEliminar', 'factura de prueba creada por error')
            ->set('passwordEliminar', 'password123')
            ->call('eliminarFactura')
            ->assertHasNoErrors()
            ->assertRedirect(route('empresa.facturas-ventas'));

        // La factura y todo lo suyo desaparecen.
        $this->assertNull(FacturaVenta::find($factura->id));
        $this->assertSame(0, FacturaVentaDetalle::where('factura_venta_id', $factura->id)->count());
        $this->assertSame(0, FacturaVentaPago::where('factura_venta_id', $factura->id)->count());

        // El número vuelve a estar libre.
        $this->assertEquals($numero - 1, $this->serie->fresh()->ultimo_numero);

        // Y queda el rastro de quién y por qué.
        $rastro = FacturaVentaEliminada::where('factura_id', $factura->id)->firstOrFail();
        $this->assertSame($this->serie->serie, $rastro->serie);
        $this->assertEquals($numero, $rastro->numero_factura);
        $this->assertSame($this->admin->id, $rastro->usuario_id);
        $this->assertSame('factura de prueba creada por error', $rastro->motivo);
        $this->assertTrue($rastro->numero_liberado);
        $this->assertEquals(1210, $rastro->total);
        $this->assertSame($this->cliente->nombre, $rastro->cliente_nombre);
    }

    public function test_el_numero_liberado_lo_usa_la_siguiente_factura(): void
    {
        $factura = $this->factura();
        $numero = $factura->numero_factura;

        app(FacturaVentaService::class)->eliminar($factura, 'prueba');

        $siguiente = $this->factura(); // consume el siguiente número de la serie

        $this->assertEquals($numero, $siguiente->numero_factura);
    }

    public function test_solo_se_puede_eliminar_la_ultima_de_la_serie(): void
    {
        $primera = $this->factura();
        $segunda = $this->factura();

        $servicio = app(FacturaVentaService::class);

        $this->assertNotNull($servicio->motivoNoEliminable($primera));
        $this->assertNull($servicio->motivoNoEliminable($segunda));

        try {
            $servicio->eliminar($primera, 'intento de borrar una del medio');
            $this->fail('Debería haber impedido borrar una factura que no es la última.');
        } catch (RuntimeException $e) {
            $this->assertStringContainsString('última factura', $e->getMessage());
        }

        $this->assertNotNull($primera->fresh());
        $this->assertEquals($segunda->numero_factura, $this->serie->fresh()->ultimo_numero);

        // Desde la pantalla tampoco: el botón sale desactivado.
        $this->componente($primera)
            ->assertSet('motivoNoEliminable', $servicio->motivoNoEliminable($primera))
            ->set('motivoEliminar', 'intento desde la pantalla')
            ->set('passwordEliminar', 'password123')
            ->call('eliminarFactura');

        $this->assertNotNull($primera->fresh());
    }

    public function test_el_boton_aparece_en_la_pantalla_de_la_factura(): void
    {
        $ultima = $this->factura();

        // Última de la serie: botón activo, que abre la ventana.
        $this->componente($ultima)
            ->assertSee('Eliminar')
            ->assertSeeHtml('wire:click="confirmarEliminarFactura"')
            ->call('confirmarEliminarFactura')
            ->assertSet('showEliminarModal', true)
            ->assertSee('Tu contraseña');

        // Si se emite otra después, la anterior ya no se puede eliminar.
        $this->factura();
        $this->componente($ultima->fresh())
            ->assertSee('Eliminar')
            ->assertDontSeeHtml('wire:click="confirmarEliminarFactura"')
            ->assertSee('Solo se puede eliminar la última factura emitida');

        // Quien no es administrador no ve el botón.
        $user = User::factory()->create([
            'role' => User::ROLE_USER,
            'name' => 'Sin permiso',
            'email' => 'sinpermiso-'.uniqid().'@t.es',
            'password' => 'password123',
        ]);
        $this->componente($ultima->fresh(), $user)->assertDontSeeHtml('confirmarEliminarFactura');
    }

    public function test_lo_eliminado_queda_a_la_vista_en_las_series(): void
    {
        $factura = $this->factura();
        $numero = $factura->numeroFormateado();

        app(FacturaVentaService::class)->eliminar($factura, 'factura repetida por error');

        $this->actingAs($this->admin)->get('/empresa/facturas-series')->assertOk()
            ->assertSee('Facturas eliminadas')
            ->assertSee($numero)
            ->assertSee('factura repetida por error')
            ->assertSee($this->admin->name);
    }

    public function test_al_eliminar_una_factura_de_certificaciones_quedan_libres(): void
    {
        $factura = $this->factura([
            'origen' => 'certificacion',
            'codigo_certificacion' => 'CERT-'.uniqid(),
        ]);

        $oficio = \App\Models\ObraGastoCategoria::forceCreate([
            'obra_id' => $this->obra->id,
            'nombre' => 'Oficio '.uniqid(),
        ]);

        $cert = \App\Models\Certificacion::forceCreate([
            'obra_id' => $this->obra->id,
            'obra_gasto_categoria_id' => $oficio->id,
            'cliente_id' => $this->cliente->id,
            'numero_certificacion' => $factura->codigo_certificacion,
            'fecha_ingreso' => now(),
            'fecha_certificacion' => now(),
            'estado_certificacion' => 'aceptada',
            'estado_factura' => 'facturada',
            'base_imponible' => 1000,
            'iva_porcentaje' => 21,
            'iva_importe' => 210,
            'retencion_porcentaje' => 0,
            'retencion_importe' => 0,
            'total' => 1210,
        ]);

        app(FacturaVentaService::class)->eliminar($factura, 'factura de prueba');

        $cert->refresh();
        $this->assertSame('pendiente', $cert->estado_factura);
        $this->assertSame('pendiente', $cert->estado_certificacion);
    }
}
