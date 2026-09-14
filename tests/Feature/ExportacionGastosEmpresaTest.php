<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * Exportación de gastos generales de empresa (PDF y Excel).
 * Filtraban y ordenaban por `fecha_gasto`, columna inexistente: la tabla usa
 * `fecha_factura`, y ambas exportaciones respondían con error 500.
 */
class ExportacionGastosEmpresaTest extends TestCase
{
    use DatabaseTransactions;

    public function test_exportar_gastos_de_empresa_a_pdf_y_excel(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'name' => 'Admin gastos',
            'email' => 'gastos-'.uniqid().'@t.es',
            'password' => 'password123',
        ]);

        $categoria = \App\Models\CategoriaGastoEmpresa::forceCreate(['nombre' => 'Categoria export '.uniqid()]);
        \App\Models\GastoGeneralEmpresa::forceCreate([
            'concepto' => 'Gasto export test',
            'importe' => 123.45,
            'fecha_factura' => '2026-06-15',
            'categoria_id' => $categoria->id,
        ]);

        $filtros = [
            '',
            '?inicio=2026-01-01',
            '?fin=2026-12-31',
            '?inicio=2026-01-01&fin=2026-12-31',
        ];

        foreach (['pdf', 'excel'] as $formato) {
            foreach ($filtros as $query) {
                $url = "/empresa/gastos/export/{$formato}{$query}";
                $this->actingAs($admin)->get($url)->assertOk();
            }
        }

        // Rango sin gastos: el PDF vuelve atrás con aviso (comportamiento previsto).
        $this->actingAs($admin)
            ->from('/empresa/gastos-empresa')
            ->get('/empresa/gastos/export/pdf?inicio=1990-01-01&fin=1990-01-31')
            ->assertRedirect('/empresa/gastos-empresa')
            ->assertSessionHas('error');
    }
}
