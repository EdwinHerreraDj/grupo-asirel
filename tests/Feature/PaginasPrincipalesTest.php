<?php

namespace Tests\Feature;

use App\Models\Obra;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * Humo: las páginas del panel se pintan sin errores. Sirve para detectar
 * plantillas rotas al tocar vistas o cabeceras.
 */
class PaginasPrincipalesTest extends TestCase
{
    use DatabaseTransactions;

    /** Páginas que ve un administrador. */
    public static function paginas(): array
    {
        return [
            'inicio' => ['/'],
            'mi unidad' => ['/unidad'],
            'obras' => ['/empresa'],
            'tareas' => ['/tareas'],
            'clientes' => ['/clientes'],
            'proveedores' => ['/proveedores'],
            'coste teórico' => ['/coste-teorico'],
            'presupuesto de venta' => ['/presupuesto-venta'],
            'facturas recibidas' => ['/facturas-recibidas'],
            'facturas de venta' => ['/empresa/facturas-ventas'],
            'series de facturas' => ['/empresa/facturas-series'],
            'gastos de empresa' => ['/empresa/gastos-empresa'],
            'categorías de gastos' => ['/empresa/categorias-gastos'],
            'informes' => ['/informes'],
            'recursos humanos' => ['/rrhh'],
            'drive' => ['/empresa/drive-app'],
            'configuración de empresa' => ['/empresa/configuracion'],
            'apariencia del panel' => ['/configuracion/apariencia'],
            'mi perfil' => ['/mi-perfil'],
            'usuarios' => ['/users'],
        ];
    }

    /** @dataProvider paginas */
    public function test_la_pagina_se_pinta_sin_errores(string $url): void
    {
        $this->actingAs($this->admin())->get($url)->assertOk();
    }

    /** Páginas de una obra que todavía usan DataTables. */
    public function test_las_paginas_de_una_obra_se_pintan_sin_errores(): void
    {
        $obra = Obra::forceCreate([
            'nombre' => 'Obra humo '.uniqid(),
            'importe_presupuestado' => 0,
            'estado' => 'ejecucion',
        ]);
        $admin = $this->admin();

        $paginas = [
            "/obras/{$obra->id}/ventas",
            "/obras/{$obra->id}/gastos-varios",
            "/obras/{$obra->id}/gastos/materiales",
            "/obras/{$obra->id}/gastos/alquileres",
            "/obras/{$obra->id}/gastos/subcontratas",
            "/obras/{$obra->id}/documentos",
            "/obras/{$obra->id}/certificaciones",
            "/obras/{$obra->id}/facturas-recibidas",
        ];

        foreach ($paginas as $url) {
            $html = $this->actingAs($admin)->get($url)->assertOk()->getContent();

            // Donde hay tabla de DataTables deben venir su CSS y su JS.
            if (str_contains($html, 'id="table-docs"')) {
                $this->assertStringContainsString('datatables.min.css', $html, "Falta el CSS en {$url}");
                $this->assertStringContainsString('datatables.min.js', $html, "Falta el JS en {$url}");
            }
        }
    }

    private function admin(): User
    {
        return User::factory()->create([
            'role' => User::ROLE_SUPER_ADMIN,
            'name' => 'Admin humo',
            'email' => 'humo-'.uniqid().'@t.es',
            'password' => 'password123',
        ]);
    }
}
