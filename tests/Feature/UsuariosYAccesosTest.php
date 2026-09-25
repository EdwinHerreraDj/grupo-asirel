<?php

namespace Tests\Feature;

use App\Models\LoginLog;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/** Listado de usuarios y registro de accesos: filtros, estados y limpieza. */
class UsuariosYAccesosTest extends TestCase
{
    use DatabaseTransactions;

    private User $super;

    protected function setUp(): void
    {
        parent::setUp();

        $this->super = $this->usuario(User::ROLE_SUPER_ADMIN, 'Zulema Super');
    }

    private function usuario(string $rol, string $nombre): User
    {
        return User::factory()->create([
            'role' => $rol,
            'name' => $nombre,
            'email' => strtolower(str_replace(' ', '.', $nombre)).'-'.uniqid().'@t.es',
            'password' => 'password123',
        ]);
    }

    // -------------------------------------------------------------
    // Usuarios
    // -------------------------------------------------------------

    public function test_el_listado_busca_y_filtra_por_rol(): void
    {
        $marca = uniqid();
        $admin = $this->usuario(User::ROLE_ADMIN, "Ana Admin {$marca}");
        $normal = $this->usuario(User::ROLE_USER, "Nino Normal {$marca}");

        // Búsqueda por nombre.
        $this->actingAs($this->super)->get('/users?buscar=Ana+Admin+'.$marca)->assertOk()
            ->assertSee($admin->name)->assertDontSee($normal->name);

        // Búsqueda por correo.
        $this->actingAs($this->super)->get('/users?buscar='.$normal->email)->assertOk()
            ->assertSee($normal->name)->assertDontSee($admin->name);

        // Filtro por rol.
        $html = $this->actingAs($this->super)->get('/users?rol='.User::ROLE_USER)->assertOk()->getContent();
        $this->assertStringContainsString($normal->name, $html);
        $this->assertStringNotContainsString($admin->name, $html);

        // Un rol que no existe no se acepta.
        $this->actingAs($this->super)->get('/users?rol=inventado')->assertSessionHasErrors('rol');
    }

    public function test_el_listado_pagina_y_no_usa_datatables(): void
    {
        User::factory()->count(20)->create(['role' => User::ROLE_USER, 'password' => 'password123']);

        $html = $this->actingAs($this->super)->get('/users')->assertOk()->getContent();

        $this->assertStringNotContainsString('datatables', strtolower($html));
        $this->assertStringContainsString('page=2', $html);
    }

    public function test_muestra_el_ultimo_acceso_de_cada_usuario(): void
    {
        $admin = $this->usuario(User::ROLE_ADMIN, 'Ana Accesos');

        LoginLog::create(['user_id' => $admin->id, 'ip_address' => '10.0.0.1', 'logged_in_at' => now()->subDays(5)]);
        LoginLog::create(['user_id' => $admin->id, 'ip_address' => '10.0.0.1', 'logged_in_at' => now()->subHour()]);

        $this->actingAs($this->super)->get('/users?buscar=Ana+Accesos')->assertOk()
            ->assertSee(now()->subHour()->format('d/m/Y H:i'))
            ->assertDontSee(now()->subDays(5)->format('d/m/Y H:i'));

        $nuevo = $this->usuario(User::ROLE_USER, 'Sin Accesos');
        $this->actingAs($this->super)->get('/users?buscar=Sin+Accesos')->assertOk()->assertSee('Nunca ha entrado');
    }

    public function test_admite_correos_largos(): void
    {
        $email = 'departamento.administracion.obras@construcciones-ejemplo.es'; // 59 caracteres

        $this->actingAs($this->super)->post('/users', [
            'name' => 'Correo Largo',
            'email' => $email,
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => User::ROLE_USER,
        ])->assertRedirect(route('users.index'))->assertSessionHasNoErrors();

        $this->assertNotNull(User::where('email', $email)->first());
    }

    public function test_borrar_desde_el_formulario_redirige_con_aviso(): void
    {
        $victima = $this->usuario(User::ROLE_USER, 'Para Borrar');

        $this->actingAs($this->super)->delete("/users/{$victima->id}")
            ->assertRedirect(route('users.index'))
            ->assertSessionHas('success');
        $this->assertNull($victima->fresh());

        // Y si no se puede, avisa en vez de romper.
        $this->actingAs($this->super)->delete("/users/{$this->super->id}")
            ->assertRedirect(route('users.index'))
            ->assertSessionHas('error');
        $this->assertNotNull($this->super->fresh());
    }

    // -------------------------------------------------------------
    // Accesos
    // -------------------------------------------------------------

    public function test_estado_de_cada_sesion(): void
    {
        $u = $this->usuario(User::ROLE_USER, 'Estados Sesion');

        $abierta = LoginLog::create(['user_id' => $u->id, 'logged_in_at' => now()->subMinutes(5)]);
        $cerrada = LoginLog::create(['user_id' => $u->id, 'logged_in_at' => now()->subHours(3), 'logged_out_at' => now()->subHours(2)]);
        $caducada = LoginLog::create(['user_id' => $u->id, 'logged_in_at' => now()->subDays(3)]);

        $this->assertSame('abierta', $abierta->fresh()->estado);
        $this->assertSame('cerrada', $cerrada->fresh()->estado);
        $this->assertSame('caducada', $caducada->fresh()->estado);
        $this->assertSame('1 h', $cerrada->fresh()->duracion);
        $this->assertNull($abierta->fresh()->duracion);
    }

    public function test_los_accesos_se_filtran_y_paginan(): void
    {
        $u = $this->usuario(User::ROLE_USER, 'Filtra Accesos');
        $otro = $this->usuario(User::ROLE_USER, 'Otro Usuario');

        LoginLog::create(['user_id' => $u->id, 'ip_address' => '192.168.50.7', 'logged_in_at' => now()->subMinutes(10)]);
        LoginLog::create(['user_id' => $otro->id, 'ip_address' => '10.20.30.40', 'logged_in_at' => now()->subDays(40)]);

        // Por usuario.
        $this->actingAs($this->super)->get('/login-logs?usuario='.$u->id)->assertOk()
            ->assertSee('192.168.50.7')->assertDontSee('10.20.30.40');

        // Por texto (IP).
        $this->actingAs($this->super)->get('/login-logs?buscar=10.20.30.40')->assertOk()
            ->assertSee('10.20.30.40')->assertDontSee('192.168.50.7');

        // Por estado.
        $this->actingAs($this->super)->get('/login-logs?estado=abierta')->assertOk()
            ->assertSee('192.168.50.7')->assertDontSee('10.20.30.40');

        // Por fechas.
        $this->actingAs($this->super)->get('/login-logs?desde='.now()->subDay()->toDateString())->assertOk()
            ->assertSee('192.168.50.7')->assertDontSee('10.20.30.40');

        // Sin DataTables y con paginación cuando hay muchos.
        foreach (range(1, 30) as $i) {
            LoginLog::create(['user_id' => $u->id, 'ip_address' => "172.16.0.{$i}", 'logged_in_at' => now()->subMinutes($i)]);
        }
        $html = $this->actingAs($this->super)->get('/login-logs')->assertOk()->getContent();
        $this->assertStringNotContainsString('datatables', strtolower($html));
        $this->assertStringContainsString('page=2', $html);
    }

    public function test_limpiar_accesos_antiguos_solo_lo_hace_un_super_admin(): void
    {
        $u = $this->usuario(User::ROLE_USER, 'Purga Accesos');
        $viejo = LoginLog::create(['user_id' => $u->id, 'ip_address' => '1.1.1.1', 'logged_in_at' => now()->subMonths(18)]);
        $reciente = LoginLog::create(['user_id' => $u->id, 'ip_address' => '2.2.2.2', 'logged_in_at' => now()->subMonths(2)]);

        $admin = $this->usuario(User::ROLE_ADMIN, 'Ana NoPurga');
        $this->actingAs($admin)->post('/login-logs/purgar', ['meses' => 12])->assertForbidden();
        $this->assertNotNull($viejo->fresh());

        $this->actingAs($this->super)->post('/login-logs/purgar', ['meses' => 12])
            ->assertRedirect(route('login.logs'))->assertSessionHas('success');

        $this->assertNull($viejo->fresh());
        $this->assertNotNull($reciente->fresh());

        // El plazo se valida.
        $this->actingAs($this->super)->post('/login-logs/purgar', ['meses' => 0])->assertSessionHasErrors('meses');
    }
}
