<?php

namespace Tests\Feature;

use App\Livewire\Empresa\FacturasVentas\Detalle as FacturaDetalle;
use App\Models\Certificacion;
use App\Models\CertificacionDetalle;
use App\Models\Cliente;
use App\Models\FacturaSerie;
use App\Models\FacturaVenta;
use App\Models\Obra;
use App\Models\ObraGastoCategoria;
use App\Models\ObraPresupuestoVenta;
use App\Models\PresupuestoVentaPartida;
use App\Models\User;
use App\Services\CertificacionCalculator;
use App\Services\FacturaVentaGenerator;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Facturación desde certificaciones: orden de líneas, comentarios y
 * agrupación por capítulo en PDF y vista web. Los totales no cambian.
 * Usa DatabaseTransactions y disco public falso.
 */
class FacturaDesgloseCapitulosTest extends TestCase
{
    use DatabaseTransactions;

    private Obra $obra;
    private ObraGastoCategoria $oficioA;
    private ObraGastoCategoria $oficioB;
    private Certificacion $certA;
    private Certificacion $certB;
    private string $numero;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

        $this->actingAs(User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'email' => 'admin-'.uniqid().'@t.es',
            'password' => 'password123',
        ]));

        $this->obra = Obra::forceCreate(['nombre' => 'Obra desglose '.uniqid(), 'importe_presupuestado' => 0]);
        $this->oficioA = ObraGastoCategoria::forceCreate(['obra_id' => $this->obra->id, 'nombre' => 'Albanileria Test']);
        $this->oficioB = ObraGastoCategoria::forceCreate(['obra_id' => $this->obra->id, 'nombre' => 'Fontaneria Test']);
        $cliente = Cliente::forceCreate(['nombre' => 'Cliente desglose']);
        $this->numero = 'D-'.uniqid();

        $this->certA = $this->certificacion($this->oficioA, $cliente);
        $this->certB = $this->certificacion($this->oficioB, $cliente);

        $partidaA = $this->partida($this->oficioA);
        $partidaB = $this->partida($this->oficioB);

        $this->linea($this->certA, $partidaA, 'A1 tabique', 2, 10);
        $this->linea($this->certA, $partidaA, 'A2 enfoscado', 3, 10, 'Comentario de la A2');
        $this->linea($this->certB, $partidaB, 'B1 tuberia', 1, 50);

        foreach ([$this->certA, $this->certB] as $cert) {
            app(CertificacionCalculator::class)->recalcular($cert->fresh());
            $cert->refresh()->update(['estado_certificacion' => 'aceptada']);
        }
    }

    public function test_modo_lineas_con_comentarios_ordena_y_agrupa_por_capitulo(): void
    {
        $factura = $this->emitir(FacturaVentaGenerator::MODO_LINEAS_COMENTARIOS);
        $detalles = $factura->fresh()->detalles;

        $this->assertSame(['A1 tabique', 'A2 enfoscado', 'B1 tuberia'], $detalles->pluck('concepto')->all());
        $this->assertSame([1, 2, 3], $detalles->pluck('orden')->map(fn ($o) => (int) $o)->all());
        $this->assertSame(
            [$this->certA->id, $this->certA->id, $this->certB->id],
            $detalles->pluck('certificacion_id')->map(fn ($id) => (int) $id)->all()
        );
        $this->assertSame([null, 'Comentario de la A2', null], $detalles->pluck('comentario')->all());

        // Solo presentación: la suma de líneas cuadra con la base imponible.
        $this->assertEquals(100.0, $factura->base_imponible);
        $this->assertEquals((float) $factura->base_imponible, round($detalles->sum('importe_linea'), 2));

        Storage::disk('public')->assertExists($factura->pdf_url);

        // PDF: cabecera por capítulo, en orden, con subtotal y comentario.
        $html = $this->pdfHtml($factura);
        $this->assertStringContainsString('Subtotal capítulo', $html);
        $this->assertStringContainsString('Comentario de la A2', $html);
        $this->assertLessThan(strpos($html, 'Fontaneria Test'), strpos($html, 'Albanileria Test'));
        $this->assertLessThan(strpos($html, 'B1 tuberia'), strpos($html, 'Fontaneria Test'));
        $this->assertStringContainsString('50,00 €', $html); // subtotal A (20 + 30)

        // Vista web: igual agrupación y comentario visible.
        Livewire::test(FacturaDetalle::class, ['factura' => $factura->fresh()])
            ->assertSeeInOrder(['Albanileria Test', 'A1 tabique', 'A2 enfoscado', 'Comentario de la A2', 'Subtotal capítulo', 'Fontaneria Test', 'B1 tuberia']);
    }

    public function test_modo_resumen_no_agrupa_ni_muestra_subtotales(): void
    {
        $factura = $this->emitir(FacturaVentaGenerator::MODO_RESUMEN);
        $detalles = $factura->fresh()->detalles;

        $this->assertCount(2, $detalles);
        $this->assertSame([1, 2], $detalles->pluck('orden')->map(fn ($o) => (int) $o)->all());
        $this->assertEquals((float) $factura->base_imponible, round($detalles->sum('importe_linea'), 2));

        $this->assertStringNotContainsString('Subtotal capítulo', $this->pdfHtml($factura));

        Livewire::test(FacturaDetalle::class, ['factura' => $factura->fresh()])
            ->assertDontSee('Subtotal capítulo');
    }

    public function test_facturas_antiguas_con_orden_uno_mantienen_el_orden_por_id(): void
    {
        $factura = $this->emitir(FacturaVentaGenerator::MODO_LINEAS);
        $factura->detalles()->update(['orden' => 1]);

        $ids = $factura->fresh()->detalles->pluck('id')->all();
        $ordenados = $ids;
        sort($ordenados);

        $this->assertSame($ordenados, $ids);
    }

    // -------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------

    private function emitir(string $modo): FacturaVenta
    {
        $serie = FacturaSerie::forceCreate([
            'serie' => 'T'.substr(uniqid(), -6),
            'ultimo_numero' => random_int(800000, 899999),
            'activa' => true,
        ]);

        return app(FacturaVentaGenerator::class)
            ->emitirDesdeCertificaciones($this->obra, $this->numero, $serie, $modo)
            ->fresh();
    }

    private function pdfHtml(FacturaVenta $factura): string
    {
        return view('pdf.factura-venta', [
            'factura' => $factura->fresh()->load(['cliente', 'detalles.certificacion.oficio']),
            'empresa' => null,
        ])->render();
    }

    private function certificacion(ObraGastoCategoria $oficio, Cliente $cliente): Certificacion
    {
        return Certificacion::forceCreate([
            'obra_id' => $this->obra->id,
            'cliente_id' => $cliente->id,
            'obra_gasto_categoria_id' => $oficio->id,
            'fecha_ingreso' => now()->toDateString(),
            'numero_certificacion' => $this->numero,
            'iva_porcentaje' => 21,
            'retencion_porcentaje' => 0,
            'base_imponible' => 0,
            'iva_importe' => 0,
            'retencion_importe' => 0,
            'total' => 0,
            'estado_certificacion' => 'pendiente',
            'estado_factura' => 'pendiente',
        ]);
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
            'descripcion' => 'Partida '.$oficio->nombre,
            'unidad' => 'm2',
            'medicion' => 100,
            'precio_unitario' => 10,
        ]);
    }

    private function linea(Certificacion $cert, PresupuestoVentaPartida $partida, string $concepto, float $cantidad, float $precio, ?string $comentario = null): void
    {
        CertificacionDetalle::forceCreate([
            'certificacion_id' => $cert->id,
            'presupuesto_venta_partida_id' => $partida->id,
            'concepto' => $concepto,
            'unidad' => 'm2',
            'cantidad' => $cantidad,
            'precio_unitario' => $precio,
            'importe_linea' => round($cantidad * $precio, 2),
            'comentario' => $comentario,
        ]);
    }
}
