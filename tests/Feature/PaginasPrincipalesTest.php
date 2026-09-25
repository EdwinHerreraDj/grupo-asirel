<?php

namespace Tests\Feature;

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
        $admin = User::factory()->create([
            'role' => User::ROLE_SUPER_ADMIN,
            'name' => 'Admin humo',
            'email' => 'humo-'.uniqid().'@t.es',
            'password' => 'password123',
        ]);

        $this->actingAs($admin)->get($url)->assertOk();
    }
}
