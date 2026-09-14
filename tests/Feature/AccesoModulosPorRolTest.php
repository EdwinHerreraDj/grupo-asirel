<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * El servidor bloquea al rol "user" las mismas secciones que el menú lateral
 * le oculta (solo admin y super_admin), y le deja el resto.
 */
class AccesoModulosPorRolTest extends TestCase
{
    use DatabaseTransactions;

    private const SOLO_ADMIN = [
        '/empresa/configuracion',
        '/empresa/gastos-empresa',
        '/empresa/categorias-gastos',
        '/empresa/gastos/export/pdf',
        '/empresa/gastos/export/excel',
        '/informes',
        '/informes/exportar/liquidacion-iva',
        '/informes/exportar/analisis-bruto-obras',
        '/informes/exportar/retenciones-obra',
        '/clientes',
        '/proveedores',
        '/empresa/facturas-series',
        '/empresa/facturas-ventas',
    ];

    private const PARA_TODOS = [
        '/empresa',
        '/empresa/drive-app',
        '/facturas-recibidas',
        '/tareas',
    ];

    private function usuario(string $role): User
    {
        return User::factory()->create([
            'role' => $role,
            'email' => $role.'-'.uniqid().'@t.es',
            'password' => 'password123',
        ]);
    }

    public function test_usuario_normal_no_accede_a_secciones_de_admin(): void
    {
        $user = $this->usuario(User::ROLE_USER);

        foreach (self::SOLO_ADMIN as $url) {
            $this->actingAs($user)->get($url)->assertForbidden();
        }
    }

    public function test_admin_y_super_admin_acceden_a_secciones_de_admin(): void
    {
        foreach ([User::ROLE_ADMIN, User::ROLE_SUPER_ADMIN] as $role) {
            $user = $this->usuario($role);

            foreach (self::SOLO_ADMIN as $url) {
                $status = $this->actingAs($user)->get($url)->getStatusCode();
                $this->assertNotSame(403, $status, "{$role} recibe 403 en {$url}");
                $this->assertLessThan(500, $status, "{$role} recibe error {$status} en {$url}");
            }
        }
    }

    public function test_usuario_normal_sigue_accediendo_a_secciones_comunes(): void
    {
        $user = $this->usuario(User::ROLE_USER);

        foreach (self::PARA_TODOS as $url) {
            $status = $this->actingAs($user)->get($url)->getStatusCode();
            $this->assertNotSame(403, $status, "user recibe 403 en {$url}");
            $this->assertLessThan(500, $status, "user recibe error {$status} en {$url}");
        }
    }

    public function test_mi_unidad_no_muestra_enlaces_de_admin_al_usuario_normal(): void
    {
        $html = $this->actingAs($this->usuario(User::ROLE_USER))->get('/empresa')->getContent();
        $this->assertStringNotContainsString(route('empresa.gastosEmpresa'), $html);
        $this->assertStringNotContainsString(route('empresa.facturas-ventas'), $html);

        $html = $this->actingAs($this->usuario(User::ROLE_ADMIN))->get('/empresa')->getContent();
        $this->assertStringContainsString(route('empresa.gastosEmpresa'), $html);
        $this->assertStringContainsString(route('empresa.facturas-ventas'), $html);
    }

    public function test_api_clientes_y_proveedores_lectura_para_todos_escritura_solo_admin(): void
    {
        $user = $this->usuario(User::ROLE_USER);

        $this->actingAs($user)->getJson('/api/clientes')->assertOk();
        $this->actingAs($user)->getJson('/api/proveedores')->assertOk();

        // Registros reales: DELETE clientes/{cliente} usa model binding y con un id
        // inexistente respondería 404 antes de comprobar el rol.
        $cliente = \App\Models\Cliente::forceCreate(['nombre' => 'Cliente rol test']);
        $proveedor = \App\Models\Proveedor::forceCreate(['nombre' => 'Proveedor rol test']);

        $this->actingAs($user)->postJson('/api/clientes', ['nombre' => 'X'])->assertForbidden();
        $this->actingAs($user)->putJson("/api/clientes/{$cliente->id}", ['nombre' => 'X'])->assertForbidden();
        $this->actingAs($user)->deleteJson("/api/clientes/{$cliente->id}")->assertForbidden();
        $this->actingAs($user)->postJson('/api/proveedores', ['nombre' => 'X'])->assertForbidden();
        $this->actingAs($user)->putJson("/api/proveedores/{$proveedor->id}", ['nombre' => 'X'])->assertForbidden();
        $this->actingAs($user)->deleteJson("/api/proveedores/{$proveedor->id}")->assertForbidden();

        $this->assertSame('Cliente rol test', $cliente->fresh()->nombre);
        $this->assertSame('Proveedor rol test', $proveedor->fresh()->nombre);

        $admin = $this->usuario(User::ROLE_ADMIN);
        $status = $this->actingAs($admin)->postJson('/api/clientes', [])->getStatusCode();
        $this->assertNotSame(403, $status);
    }
}
