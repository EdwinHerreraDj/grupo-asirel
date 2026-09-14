<?php

namespace Tests\Feature;

use App\Models\Certificacion;
use App\Models\CertificacionDetalle;
use App\Models\CertificacionEvento;
use App\Models\Cliente;
use App\Models\FacturaSerie;
use App\Models\FacturaVenta;
use App\Models\Obra;
use App\Models\ObraGastoCategoria;
use App\Models\ObraPresupuestoVenta;
use App\Models\PresupuestoVentaPartida;
use App\Models\User;
use App\Services\CertificacionCalculator;
use App\Services\CertificacionDetalleService;
use App\Services\FacturaVentaGenerator;
use App\Services\FacturaVentaService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Tests\TestCase;

/**
 * Facturación de venta: emisión desde certificaciones (totales, numeración,
 * invariantes), facturas manuales, pagos y anulación.
 * Usa DatabaseTransactions y disco public falso.
 */
class FacturacionFiscalTest extends TestCase
{
    use DatabaseTransactions;

    private Obra $obra;
    private Cliente $cliente;
    private Certificacion $capA;
    private Certificacion $capB;
    private string $numero;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

        $this->actingAs(User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'email' => 'facturacion-'.uniqid().'@t.es',
            'password' => 'password123',
        ]));

        $this->obra = Obra::forceCreate(['nombre' => 'Obra facturacion '.uniqid(), 'importe_presupuestado' => 0]);
        $this->cliente = Cliente::forceCreate(['nombre' => 'Cliente facturacion']);
        $this->numero = 'F-'.uniqid();

        // Capítulo A: 99.99 · Capítulo B: 250.00 · IVA 21 % · retención 15 %
        $this->capA = $this->capituloAceptado([[3, 33.33]]);
        $this->capB = $this->capituloAceptado([[5, 40], [1, 50]]);
    }

    // -------------------------------------------------------------
    // Emisión desde certificaciones
    // -------------------------------------------------------------

    public function test_emitir_suma_totales_de_capitulos_numera_y_marca_facturadas(): void
    {
        $serie = $this->serie();
        $ultimo = $serie->ultimo_numero;

        $factura = $this->emitirDesdeCertificaciones($serie);

        $caps = collect([$this->capA->fresh(), $this->capB->fresh()]);

        $this->assertSame(FacturaVenta::ESTADO_EMITIDA, $factura->estado);
        $this->assertSame('certificacion', $factura->origen);
        $this->assertSame($this->numero, $factura->codigo_certificacion);
        $this->assertSame($ultimo + 1, (int) $factura->numero_factura);
        $this->assertSame($ultimo + 1, (int) $serie->fresh()->ultimo_numero);

        $this->assertEquals(349.99, $factura->base_imponible);
        foreach (['base_imponible', 'iva_importe', 'retencion_importe', 'total'] as $campo) {
            $this->assertEquals(round($caps->sum($campo), 2), round($factura->{$campo}, 2), $campo);
        }
        $this->assertEquals(
            round($factura->base_imponible + $factura->iva_importe - $factura->retencion_importe, 2),
            round($factura->total, 2)
        );

        foreach ($caps as $cap) {
            $this->assertSame('facturada', $cap->estado_factura);
            $this->assertSame(1, CertificacionEvento::where('certificacion_id', $cap->id)->where('tipo', 'facturada')->count());
        }

        $this->assertNotNull($factura->pdf_original_generado_at);
        Storage::disk('public')->assertExists($factura->pdf_url);
    }

    public function test_no_factura_si_algun_capitulo_no_esta_aceptado(): void
    {
        $this->capB->update(['estado_certificacion' => 'pendiente']);

        $this->assertNoSeFactura();
    }

    public function test_no_factura_con_clientes_distintos(): void
    {
        $this->capB->update(['cliente_id' => Cliente::forceCreate(['nombre' => 'Otro cliente'])->id]);

        $this->assertNoSeFactura();
    }

    public function test_no_factura_con_iva_distinto(): void
    {
        $this->capB->update(['iva_porcentaje' => 10]);

        $this->assertNoSeFactura();
    }

    public function test_no_factura_con_retencion_distinta(): void
    {
        $this->capB->update(['retencion_porcentaje' => 7]);

        $this->assertNoSeFactura();
    }

    public function test_no_se_factura_dos_veces_el_mismo_numero(): void
    {
        $this->emitirDesdeCertificaciones($this->serie());

        $this->assertNoSeFactura(yaHayUna: true);
    }

    // -------------------------------------------------------------
    // Facturas manuales
    // -------------------------------------------------------------

    public function test_factura_manual_calcula_totales_emite_y_queda_bloqueada(): void
    {
        $serie = $this->serie();
        $service = app(FacturaVentaService::class);

        $factura = $service->crearBorrador([
            'serie' => $serie->serie,
            'fecha_emision' => now()->toDateString(),
            'obra_id' => $this->obra->id,
            'cliente_id' => $this->cliente->id,
            'iva_porcentaje' => 21,
            'retencion_porcentaje' => 15,
        ]);

        // Sin líneas no se emite.
        $this->assertLanza(fn () => $service->emitir($factura->fresh()));

        $service->agregarLinea($factura, ['concepto' => 'Trabajos', 'cantidad' => 3, 'precio_unitario' => 33.33]);
        $service->agregarLinea($factura, ['concepto' => 'Ajuste', 'cantidad' => 1, 'precio_unitario' => 0.01]);
        $factura->refresh();

        $this->assertEquals(100.00, $factura->base_imponible);
        $this->assertEquals(21.00, $factura->iva_importe);
        $this->assertEquals(15.00, $factura->retencion_importe);
        $this->assertEquals(106.00, $factura->total);
        $this->assertSame(FacturaVenta::ESTADO_BORRADOR, $factura->estado);

        $emitida = $service->emitir($factura);

        $this->assertSame(FacturaVenta::ESTADO_EMITIDA, $emitida->estado);
        $this->assertSame($serie->ultimo_numero + 1, (int) $emitida->numero_factura);
        $this->assertNotNull($emitida->pdf_original_generado_at);
        Storage::disk('public')->assertExists($emitida->pdf_url);

        // Emitida: no admite cambios ni una segunda emisión.
        $this->assertLanza(fn () => $service->agregarLinea($emitida, ['concepto' => 'Extra', 'cantidad' => 1, 'precio_unitario' => 500]));
        $this->assertLanza(fn () => $service->eliminarLinea($emitida, $emitida->detalles->first()));
        $this->assertLanza(fn () => $service->emitir($emitida->fresh()));

        $this->assertEquals(106.00, $emitida->fresh()->total);
        $this->assertCount(2, $emitida->fresh()->detalles);
    }

    public function test_pagos_marcan_pagada_impiden_anular_y_al_borrarlos_vuelve_a_emitida(): void
    {
        $factura = $this->emitirDesdeCertificaciones($this->serie());
        $service = app(FacturaVentaService::class);

        $pago = $service->registrarPago($factura, [
            'fecha_pago' => now()->toDateString(),
            'importe' => $factura->total,
            'metodo' => 'transferencia',
        ]);

        $this->assertSame(FacturaVenta::ESTADO_PAGADA, $factura->fresh()->estado);
        $this->assertLanza(fn () => $service->anular($factura->fresh(), 'No debería'));
        $this->assertSame('facturada', $this->capA->fresh()->estado_factura);

        // Editar el pago por debajo del total la devuelve a emitida; al total, pagada.
        $service->actualizarPago($pago, [
            'fecha_pago' => now()->toDateString(),
            'importe' => 10,
            'metodo' => 'transferencia',
        ]);
        $this->assertSame(FacturaVenta::ESTADO_EMITIDA, $factura->fresh()->estado);
        $this->assertEquals(10, (float) $pago->fresh()->importe);

        $service->actualizarPago($pago->fresh(), [
            'fecha_pago' => now()->toDateString(),
            'importe' => $factura->total,
            'metodo' => 'transferencia',
        ]);
        $this->assertSame(FacturaVenta::ESTADO_PAGADA, $factura->fresh()->estado);

        $service->eliminarPago($pago->fresh());
        $this->assertNull($pago->fresh());

        $this->assertSame(FacturaVenta::ESTADO_EMITIDA, $factura->fresh()->estado);
    }

    // -------------------------------------------------------------
    // Anulación
    // -------------------------------------------------------------

    public function test_anular_factura_de_certificaciones_libera_sus_capitulos(): void
    {
        $factura = $this->emitirDesdeCertificaciones($this->serie());

        app(FacturaVentaService::class)->anular($factura, 'Cliente equivocado');

        $factura->refresh();
        $this->assertSame(FacturaVenta::ESTADO_ANULADA, $factura->estado);
        $this->assertSame('Cliente equivocado', $factura->motivo_anulacion);

        foreach ([$this->capA, $this->capB] as $cap) {
            $cap->refresh();
            $this->assertSame('pendiente', $cap->estado_factura);
            $this->assertSame('pendiente', $cap->estado_certificacion);
            $this->assertSame(1, CertificacionEvento::where('certificacion_id', $cap->id)->where('tipo', 'factura_anulada')->count());
        }
    }

    public function test_anular_no_libera_si_otra_factura_viva_mantiene_el_grupo(): void
    {
        $factura = $this->emitirDesdeCertificaciones($this->serie());

        FacturaVenta::forceCreate([
            'serie' => $this->serie()->serie,
            'numero_factura' => '1',
            'fecha_emision' => now()->toDateString(),
            'obra_id' => $this->obra->id,
            'cliente_id' => $this->cliente->id,
            'origen' => 'certificacion',
            'codigo_certificacion' => $this->numero,
            'estado' => FacturaVenta::ESTADO_EMITIDA,
        ]);

        app(FacturaVentaService::class)->anular($factura, 'Duplicada');

        $this->assertSame(FacturaVenta::ESTADO_ANULADA, $factura->fresh()->estado);
        $this->assertSame('facturada', $this->capA->fresh()->estado_factura);
        $this->assertSame('facturada', $this->capB->fresh()->estado_factura);
    }

    public function test_tras_anular_se_puede_corregir_y_volver_a_facturar(): void
    {
        $primera = $this->emitirDesdeCertificaciones($this->serie());
        app(FacturaVentaService::class)->anular($primera, 'Rehacer');

        $certService = app(CertificacionDetalleService::class);
        $certService->aceptar($this->capA->fresh());
        $certService->aceptar($this->capB->fresh());

        $segunda = $this->emitirDesdeCertificaciones($this->serie());

        $this->assertNotSame($primera->id, $segunda->id);
        $this->assertSame(FacturaVenta::ESTADO_EMITIDA, $segunda->estado);
        $this->assertEquals($primera->total, $segunda->total);
        $this->assertSame('facturada', $this->capA->fresh()->estado_factura);
        $this->assertSame(FacturaVenta::ESTADO_ANULADA, $primera->fresh()->estado);
    }

    // -------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------

    private function assertNoSeFactura(bool $yaHayUna = false): void
    {
        $serie = $this->serie();
        $ultimo = $serie->ultimo_numero;
        $facturasAntes = FacturaVenta::where('obra_id', $this->obra->id)->count();
        $estadosAntes = [$this->capA->fresh()->estado_factura, $this->capB->fresh()->estado_factura];

        $this->assertLanza(fn () => $this->emitirDesdeCertificaciones($serie));

        $this->assertSame($ultimo, (int) $serie->fresh()->ultimo_numero, 'No debe consumir número de serie');
        $this->assertSame($facturasAntes, FacturaVenta::where('obra_id', $this->obra->id)->count());
        $this->assertSame($estadosAntes, [$this->capA->fresh()->estado_factura, $this->capB->fresh()->estado_factura]);

        if (! $yaHayUna) {
            $this->assertSame(['pendiente', 'pendiente'], $estadosAntes);
        }
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

    private function emitirDesdeCertificaciones(FacturaSerie $serie): FacturaVenta
    {
        return app(FacturaVentaGenerator::class)
            ->emitirDesdeCertificaciones($this->obra, $this->numero, $serie)
            ->fresh();
    }

    private function serie(): FacturaSerie
    {
        return FacturaSerie::forceCreate([
            'serie' => 'T'.substr(uniqid(), -6),
            'ultimo_numero' => random_int(700000, 799999),
            'activa' => true,
        ]);
    }

    /** @param array<int, array{0: float, 1: float}> $lineas [cantidad, precio] */
    private function capituloAceptado(array $lineas): Certificacion
    {
        $oficio = ObraGastoCategoria::forceCreate(['obra_id' => $this->obra->id, 'nombre' => 'Oficio '.uniqid()]);
        $capitulo = ObraPresupuestoVenta::firstOrCreate([
            'obra_id' => $this->obra->id,
            'obra_gasto_categoria_id' => $oficio->id,
        ]);
        $partida = PresupuestoVentaPartida::forceCreate([
            'obra_presupuesto_venta_id' => $capitulo->id,
            'obra_id' => $this->obra->id,
            'descripcion' => 'Partida facturacion',
            'unidad' => 'm2',
            'medicion' => 1000,
            'precio_unitario' => 10,
        ]);

        $cert = Certificacion::forceCreate([
            'obra_id' => $this->obra->id,
            'cliente_id' => $this->cliente->id,
            'obra_gasto_categoria_id' => $oficio->id,
            'fecha_ingreso' => now()->toDateString(),
            'numero_certificacion' => $this->numero,
            'iva_porcentaje' => 21,
            'retencion_porcentaje' => 15,
            'base_imponible' => 0,
            'iva_importe' => 0,
            'retencion_importe' => 0,
            'total' => 0,
            'estado_certificacion' => 'pendiente',
            'estado_factura' => 'pendiente',
        ]);

        foreach ($lineas as [$cantidad, $precio]) {
            CertificacionDetalle::forceCreate([
                'certificacion_id' => $cert->id,
                'presupuesto_venta_partida_id' => $partida->id,
                'concepto' => 'Linea facturacion',
                'unidad' => 'm2',
                'cantidad' => $cantidad,
                'precio_unitario' => $precio,
                'importe_linea' => round($cantidad * $precio, 2),
            ]);
        }

        app(CertificacionCalculator::class)->recalcular($cert->fresh());
        $cert->refresh()->update(['estado_certificacion' => 'aceptada']);

        return $cert->fresh();
    }
}
