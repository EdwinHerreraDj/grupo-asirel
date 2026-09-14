<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

/**
 * Error 419 "PAGE EXPIRED": con el token CSRF caducado la app devuelve al
 * usuario a un sitio útil con aviso, y "Recordar sesión" funciona.
 *
 * En tests Laravel no verifica CSRF, así que se simula lanzando la misma
 * excepción desde una ruta de prueba con el middleware web.
 */
class SesionCaducadaTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        Route::middleware('web')->post('/_test/token-caducado', function () {
            throw new TokenMismatchException('CSRF token mismatch.');
        });
    }

    private function usuario(): User
    {
        return User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'name' => 'Usuario sesion',
            'email' => 'sesion-'.uniqid().'@t.es',
            'password' => 'password123',
        ]);
    }

    public function test_formulario_con_token_caducado_vuelve_al_login_con_aviso(): void
    {
        $this->post('/_test/token-caducado', ['email' => 'alguien@t.es', 'password' => 'secreto'])
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('login')
            ->assertSessionHasInput('email', 'alguien@t.es')
            ->assertSessionMissing('_old_input.password');
    }

    public function test_usuario_dentro_vuelve_atras_con_aviso(): void
    {
        $this->actingAs($this->usuario())
            ->from('/empresa')
            ->post('/_test/token-caducado')
            ->assertRedirect('/empresa')
            ->assertSessionHas('error');
    }

    public function test_react_y_livewire_reciben_419_en_json(): void
    {
        $this->postJson('/_test/token-caducado')
            ->assertStatus(419)
            ->assertJson(['message' => 'Tu sesión ha caducado. Vuelve a iniciar sesión.']);

        $this->post('/_test/token-caducado', [], ['X-Livewire' => '1'])
            ->assertStatus(419)
            ->assertJsonStructure(['message']);
    }

    public function test_login_muestra_aviso_solo_si_la_sesion_caduco(): void
    {
        $this->get('/login?sesion=caducada')->assertOk()->assertSee('Tu sesión ha caducado');
        $this->get('/login')->assertOk()->assertDontSee('Tu sesión ha caducado');
    }

    public function test_recordar_sesion_crea_la_cookie_de_recordatorio(): void
    {
        $this->get('/login')->assertSee('name="remember"', false);

        $user = $this->usuario();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password123',
            'remember' => '1',
        ])->assertRedirect(route('unidad'))
            ->assertCookie(Auth::guard('web')->getRecallerName());
    }

    public function test_el_layout_usa_la_api_de_livewire_3_y_detecta_la_caducidad(): void
    {
        $html = $this->actingAs($this->usuario())->get('/home')->assertOk()->getContent();

        $this->assertStringContainsString("livewire:init", $html);
        $this->assertStringContainsString("Livewire.hook('request'", $html);
        $this->assertStringContainsString('?sesion=caducada', $html);
        $this->assertStringNotContainsString("livewire:load", $html);
        $this->assertStringNotContainsString("alert('Tu sesión", $html);
    }
}
