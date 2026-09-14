<?php

namespace Tests\Feature;

use App\Models\Certificacion;
use App\Models\CertificacionDetalle;
use App\Models\Cliente;
use App\Models\Obra;
use App\Models\ObraGastoCategoria;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * Incidencias reportadas por el cliente (septiembre 2026).
 */
class IncidenciasClienteTest extends TestCase
{
    use DatabaseTransactions;

    private function admin(): User
    {
        return User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'name' => 'Admin incidencias',
            'email' => 'inc-'.uniqid().'@t.es',
            'password' => 'password123',
        ]);
    }

    /** Presupuestos: el selector de cliente solo mostraba los 10 primeros. */
    public function test_la_api_de_clientes_respeta_per_page(): void
    {
        for ($i = 0; $i < 15; $i++) {
            Cliente::forceCreate(['nombre' => 'Cliente per_page '.$i]);
        }
        $total = Cliente::count();
        $admin = $this->admin();

        // Por defecto sigue paginando de 10 en 10 (pantalla de Clientes).
        $this->actingAs($admin)->getJson('/api/clientes')
            ->assertOk()
            ->assertJsonCount(10, 'data');

        // El selector del PDF de presupuesto pide per_page=500.
        $this->actingAs($admin)->getJson('/api/clientes?per_page=500')
            ->assertOk()
            ->assertJsonCount(min($total, 500), 'data');

        // Límite superior para no devolver tablas enormes.
        $this->assertSame(1000, $this->actingAs($admin)->getJson('/api/clientes?per_page=999999')->json('per_page'));
    }

    /** Certificaciones: el comentario de cada partida no salía en el PDF. */
    public function test_el_pdf_de_certificacion_incluye_el_comentario_de_cada_linea(): void
    {
        $obra = Obra::forceCreate(['nombre' => 'Obra comentario '.uniqid(), 'importe_presupuestado' => 0]);
        $oficio = ObraGastoCategoria::forceCreate(['obra_id' => $obra->id, 'nombre' => 'Oficio comentario']);
        $cert = Certificacion::forceCreate([
            'obra_id' => $obra->id,
            'cliente_id' => Cliente::forceCreate(['nombre' => 'Cliente comentario'])->id,
            'obra_gasto_categoria_id' => $oficio->id,
            'fecha_ingreso' => now()->toDateString(),
            'numero_certificacion' => 'COM-'.uniqid(),
            'iva_porcentaje' => 21,
            'retencion_porcentaje' => 0,
            'base_imponible' => 20,
            'iva_importe' => 4.2,
            'retencion_importe' => 0,
            'total' => 24.2,
            'estado_certificacion' => 'pendiente',
            'estado_factura' => 'pendiente',
        ]);
        CertificacionDetalle::forceCreate([
            'certificacion_id' => $cert->id,
            'concepto' => 'Tabique de ladrillo',
            'unidad' => 'm2',
            'cantidad' => 2,
            'precio_unitario' => 10,
            'importe_linea' => 20,
            'comentario' => 'Planta baja, zona cocina',
        ]);

        // El PDF se genera de verdad; capturamos los datos que recibe la plantilla.
        $datos = null;
        view()->composer('pdf.certificacion', function ($vista) use (&$datos) {
            $datos = $vista->getData();
        });

        $this->actingAs($this->admin())
            ->postJson('/api/certificaciones/informe-pdf', ['certificacion_ids' => [$cert->id]])
            ->assertOk();

        $this->assertNotNull($datos, 'La plantilla del PDF no llegó a renderizarse');
        $this->assertSame('Planta baja, zona cocina', $datos['capitulos'][0]['lineas'][0]['comentario']);

        $html = view('pdf.certificacion', $datos)->render();
        $this->assertStringContainsString('Tabique de ladrillo', $html);
        $this->assertStringContainsString('Planta baja, zona cocina', $html);
    }
}
