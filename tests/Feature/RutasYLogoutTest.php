<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * Sin rutas catch-all: las plantillas Blade ya no se abren por URL y el cierre
 * de sesión solo se hace por POST con CSRF.
 */
class RutasYLogoutTest extends TestCase
{
    use DatabaseTransactions;

    private function usuario(string $role = User::ROLE_ADMIN): User
    {
        return User::factory()->create([
            'role' => $role,
            'email' => $role.'-'.uniqid().'@t.es',
            'password' => 'password123',
        ]);
    }

    public function test_logout_por_post_cierra_la_sesion(): void
    {
        $this->actingAs($this->usuario())
            ->post('/logout')
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }

    public function test_logout_sigue_registrando_la_salida_en_login_logs(): void
    {
        $user = $this->usuario();
        $log = \App\Models\LoginLog::create([
            'user_id' => $user->id,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'test',
            'logged_in_at' => now(),
        ]);

        $this->actingAs($user)->post('/logout')->assertRedirect(route('login'));

        $this->assertNotNull($log->fresh()->logged_out_at);
    }

    public function test_get_logout_ya_no_cierra_la_sesion(): void
    {
        $user = $this->usuario();

        $status = $this->actingAs($user)->get('/logout')->getStatusCode();

        $this->assertContains($status, [404, 405]);
        $this->assertAuthenticatedAs($user);
    }

    public function test_plantillas_blade_no_se_abren_por_url(): void
    {
        $user = $this->usuario();

        // 404 (no hay ruta) o 405 (coincide con una ruta de otro método, p. ej.
        // PUT users/{user}): en ningún caso se pinta la plantilla.
        foreach (['/index', '/auth/login', '/pdf/factura-venta', '/users/unidad', '/layouts/shared/topbar'] as $url) {
            $status = $this->actingAs($user)->get($url)->getStatusCode();
            $this->assertContains($status, [404, 405], "{$url} devuelve {$status}");
        }
    }

    public function test_control_de_presencia_retirado(): void
    {
        $user = $this->usuario();

        // Rutas de fichajes (dependían de la BD de Presencia, ya desconectada).
        foreach (['/fichajes/1', '/obra/1/fichajes/informes/excel'] as $url) {
            $this->actingAs($user)->get($url)->assertNotFound();
        }
        $this->actingAs($user)->put('/resumen/1')->assertNotFound();

        $this->assertNull(config('database.connections.presencia'));
    }

    public function test_paginas_con_layout_y_login_se_pintan(): void
    {
        $this->get('/login')
            ->assertOk()
            ->assertDontSee('recoverpw');

        $html = $this->actingAs($this->usuario())
            ->get('/home')
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('action="'.route('logout').'"', $html);
        $this->assertStringNotContainsString('Lock Screen', $html);

        $this->actingAs($this->usuario())->get('/empresa')->assertOk();
        $this->actingAs($this->usuario(User::ROLE_USER))->get('/unidad')->assertOk();
    }
}
