<?php

namespace Tests\Feature;

use App\Models\Certificacion;
use App\Models\CertificacionDetalle;
use App\Models\CertificacionEvento;
use App\Models\Cliente;
use App\Models\Obra;
use App\Models\ObraGastoCategoria;
use App\Models\ObraPresupuestoVenta;
use App\Models\PresupuestoVentaPartida;
use App\Models\User;
use App\Services\CertificacionCalculator;
use App\Services\CertificacionDetalleService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use RuntimeException;
use Tests\TestCase;

/**
 * Invariantes fiscales y de estado de las certificaciones:
 * fórmulas de CertificacionCalculator, aceptar/anular e impuestos por número.
 */
class CertificacionFiscalTest extends TestCase
{
    use DatabaseTransactions;

    private Obra $obra;
    private ObraGastoCategoria $oficio;
    private Cliente $cliente;
    private PresupuestoVentaPartida $partida;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'email' => 'fiscal-'.uniqid().'@t.es',
            'password' => 'password123',
        ]));

        $this->obra = Obra::forceCreate(['nombre' => 'Obra fiscal '.uniqid(), 'importe_presupuestado' => 0]);
        $this->oficio = $this->oficio();
        $this->cliente = Cliente::forceCreate(['nombre' => 'Cliente fiscal']);
        $this->partida = $this->partida($this->oficio);
    }

    // -------------------------------------------------------------
    // CertificacionCalculator
    // -------------------------------------------------------------

    public function test_recalcular_aplica_formulas_y_redondea_a_dos_decimales(): void
    {
        $cert = $this->certificacion(iva: 21, retencion: 15);
        $this->linea($cert, 3, 33.33);   // 99.99

        $this->calculator()->recalcular($cert->fresh());
        $cert->refresh();

        // iva 20.9979 → 21.00 · retención 14.9985 → 15.00 · total 105.9894 → 105.99
        $this->assertEquals(99.99, (float) $cert->base_imponible);
        $this->assertEquals(21.00, (float) $cert->iva_importe);
        $this->assertEquals(15.00, (float) $cert->retencion_importe);
        $this->assertEquals(105.99, (float) $cert->total);
    }

    public function test_la_base_es_la_suma_exacta_de_las_lineas(): void
    {
        $cert = $this->certificacion(iva: 10, retencion: 0);
        $this->linea($cert, 1, 12.34);
        $this->linea($cert, 1, 0.01);
        $this->linea($cert, 2.5, 4);     // 10.00

        $this->calculator()->recalcular($cert->fresh());
        $cert->refresh();

        $this->assertEquals(22.35, (float) $cert->base_imponible);
        $this->assertEquals(2.24, (float) $cert->iva_importe);   // 2.235 → 2.24
        $this->assertEquals(24.59, (float) $cert->total);
    }

    public function test_sin_lineas_todo_queda_a_cero(): void
    {
        $cert = $this->certificacion(iva: 21, retencion: 15);
        $cert->update(['base_imponible' => 50, 'iva_importe' => 10.5, 'total' => 60.5]);

        $this->calculator()->recalcular($cert->fresh());
        $cert->refresh();

        $this->assertEquals(0, (float) $cert->base_imponible);
        $this->assertEquals(0, (float) $cert->iva_importe);
        $this->assertEquals(0, (float) $cert->retencion_importe);
        $this->assertEquals(0, (float) $cert->total);
    }

    public function test_no_se_recalcula_una_certificacion_facturada(): void
    {
        $cert = $this->certificacion(estado: 'aceptada', estadoFactura: 'facturada');
        $cert->update(['base_imponible' => 50, 'total' => 60.5]);
        $this->linea($cert, 10, 10);

        try {
            $this->calculator()->recalcular($cert->fresh());
            $this->fail('Debió lanzar RuntimeException');
        } catch (RuntimeException) {
        }

        $this->assertEquals(50, (float) $cert->fresh()->base_imponible);
        $this->assertEquals(60.5, (float) $cert->fresh()->total);
    }

    // -------------------------------------------------------------
    // Estados
    // -------------------------------------------------------------

    public function test_aceptar_exige_lineas_y_registra_evento(): void
    {
        $cert = $this->certificacion();

        $this->assertLanza(fn () => $this->service()->aceptar($cert));
        $this->assertSame('pendiente', $cert->fresh()->estado_certificacion);
        $this->assertSame(0, CertificacionEvento::where('certificacion_id', $cert->id)->where('tipo', 'aceptada')->count());

        $this->linea($cert, 1, 10);
        $this->service()->aceptar($cert);

        $this->assertSame('aceptada', $cert->fresh()->estado_certificacion);
        $this->assertSame(1, CertificacionEvento::where('certificacion_id', $cert->id)->where('tipo', 'aceptada')->count());

        // Aceptar dos veces no es válido.
        $this->assertLanza(fn () => $this->service()->aceptar($cert));
    }

    public function test_una_certificacion_aceptada_no_admite_cambios(): void
    {
        $cert = $this->certificacion(estado: 'aceptada');
        $linea = $this->linea($cert, 2, 10);

        $this->assertLanza(fn () => $this->service()->crear($cert, [
            'presupuesto_venta_partida_id' => $this->partida->id,
            'cantidad' => 1,
        ]));
        $this->assertLanza(fn () => $this->service()->actualizar($cert, $linea, [
            'concepto' => 'X', 'unidad' => 'm2', 'cantidad' => 99, 'precio_unitario' => 1,
        ], true));
        $this->assertLanza(fn () => $this->service()->eliminar($cert, $linea));
        $this->assertLanza(fn () => $this->service()->aplicarImpuestos($cert, 10, 0));

        $this->assertSame(1, $cert->detalles()->count());
        $this->assertEquals(2, (float) $linea->fresh()->cantidad);
        $this->assertEquals(21, (float) $cert->fresh()->iva_porcentaje);
    }

    public function test_anular_devuelve_a_pendiente_con_motivo_y_no_si_esta_facturada(): void
    {
        $cert = $this->certificacion(estado: 'aceptada');

        $this->service()->anular($cert, 'Error de medición');

        $this->assertSame('pendiente', $cert->fresh()->estado_certificacion);
        $evento = CertificacionEvento::where('certificacion_id', $cert->id)->where('tipo', 'anulada')->first();
        $this->assertNotNull($evento);
        $this->assertSame('Error de medición', $evento->motivo);

        // Pendiente o facturada: no se puede anular.
        $this->assertLanza(fn () => $this->service()->anular($cert->fresh()));

        $facturada = $this->certificacion(estado: 'aceptada', estadoFactura: 'facturada');
        $this->assertLanza(fn () => $this->service()->anular($facturada));
        $this->assertSame('aceptada', $facturada->fresh()->estado_certificacion);
    }

    // -------------------------------------------------------------
    // Impuestos por número de certificación
    // -------------------------------------------------------------

    public function test_impuestos_se_aplican_a_todos_los_capitulos_del_numero(): void
    {
        $numero = 'IMP-'.uniqid();
        $capA = $this->certificacion(numero: $numero);
        $capB = $this->certificacion(numero: $numero, oficio: $this->oficio());
        $otroNumero = $this->certificacion(numero: 'OTRO-'.uniqid());

        $this->linea($capA, 1, 100);
        $this->linea($capB, 1, 200);

        $this->service()->aplicarImpuestos($capA, 10, 5);

        foreach ([[$capA, 100], [$capB, 200]] as [$cap, $base]) {
            $cap->refresh();
            $this->assertEquals(10, (float) $cap->iva_porcentaje);
            $this->assertEquals(5, (float) $cap->retencion_porcentaje);
            $this->assertEquals($base, (float) $cap->base_imponible);
            $this->assertEquals(round($base * 1.05, 2), (float) $cap->total);
        }

        $this->assertEquals(21, (float) $otroNumero->fresh()->iva_porcentaje);
    }

    public function test_impuestos_no_cambian_nada_si_algun_capitulo_no_es_editable(): void
    {
        $numero = 'IMP-'.uniqid();
        $pendiente = $this->certificacion(numero: $numero);
        $aceptada = $this->certificacion(numero: $numero, oficio: $this->oficio(), estado: 'aceptada');

        $this->assertLanza(fn () => $this->service()->aplicarImpuestos($pendiente, 4, 1));

        $this->assertEquals(21, (float) $pendiente->fresh()->iva_porcentaje);
        $this->assertEquals(21, (float) $aceptada->fresh()->iva_porcentaje);
    }

    // -------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------

    private function calculator(): CertificacionCalculator
    {
        return app(CertificacionCalculator::class);
    }

    private function service(): CertificacionDetalleService
    {
        return app(CertificacionDetalleService::class);
    }

    private function assertLanza(callable $accion): void
    {
        try {
            $accion();
        } catch (RuntimeException) {
            $this->addToAssertionCount(1);

            return;
        }

        $this->fail('Se esperaba RuntimeException');
    }

    private function oficio(): ObraGastoCategoria
    {
        return ObraGastoCategoria::forceCreate(['obra_id' => $this->obra->id, 'nombre' => 'Oficio '.uniqid()]);
    }

    private function partida(ObraGastoCategoria $oficio): PresupuestoVentaPartida
    {
        $capitulo = ObraPresupuestoVenta::firstOrCreate([
            'obra_id' => $this->obra->id,
            'obra_gasto_categoria_id' => $oficio->id,
        ]);

        return PresupuestoVentaPartida::forceCreate([
            'obra_presupuesto_venta_id' => $capitulo->id,
            'obra_id' => $this->obra->id,
            'descripcion' => 'Partida fiscal',
            'unidad' => 'm2',
            'medicion' => 1000,
            'precio_unitario' => 10,
        ]);
    }

    private function certificacion(
        string $estado = 'pendiente',
        string $estadoFactura = 'pendiente',
        float $iva = 21,
        float $retencion = 0,
        ?string $numero = null,
        ?ObraGastoCategoria $oficio = null,
    ): Certificacion {
        return Certificacion::forceCreate([
            'obra_id' => $this->obra->id,
            'cliente_id' => $this->cliente->id,
            'obra_gasto_categoria_id' => ($oficio ?? $this->oficio)->id,
            'fecha_ingreso' => now()->toDateString(),
            'numero_certificacion' => $numero ?? 'C-'.uniqid(),
            'iva_porcentaje' => $iva,
            'retencion_porcentaje' => $retencion,
            'base_imponible' => 0,
            'iva_importe' => 0,
            'retencion_importe' => 0,
            'total' => 0,
            'estado_certificacion' => $estado,
            'estado_factura' => $estadoFactura,
        ]);
    }

    private function linea(Certificacion $cert, float $cantidad, float $precio): CertificacionDetalle
    {
        return CertificacionDetalle::forceCreate([
            'certificacion_id' => $cert->id,
            'presupuesto_venta_partida_id' => $this->partida->id,
            'concepto' => 'Linea fiscal',
            'unidad' => 'm2',
            'cantidad' => $cantidad,
            'precio_unitario' => $precio,
            'importe_linea' => round($cantidad * $precio, 2),
        ]);
    }
}
