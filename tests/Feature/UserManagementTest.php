<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * Acceso a la gestión de usuarios y cierre del registro público.
 * Usa DatabaseTransactions: todo lo creado se revierte al terminar cada test.
 */
class UserManagementTest extends TestCase
{
    use DatabaseTransactions;

    private function crearUsuario(string $role): User
    {
        return User::factory()->create([
            'role' => $role,
            'email' => $role.'-'.uniqid().'@t.es',
            // En claro: el cast 'hashed' lo cifra con la configuración de tests.
            'password' => 'password123',
        ]);
    }

    public function test_registro_publico_deshabilitado(): void
    {
        $this->get('/register')->assertRedirect(route('login'));
        $this->post('/register', [
            'name' => 'X',
            'email' => 'x@t.es',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertStatus(405);
        $this->assertDatabaseMissing('users', ['email' => 'x@t.es']);
    }

    public function test_login_sigue_funcionando(): void
    {
        $this->get('/login')->assertOk();
    }

    public function test_usuario_normal_no_accede_a_usuarios_ni_logs(): void
    {
        $user = $this->crearUsuario(User::ROLE_USER);

        $this->actingAs($user)->get('/users')->assertForbidden();
        $this->actingAs($user)->get('/login-logs')->assertForbidden();
        $this->actingAs($user)->post('/users', [
            'name' => 'Hack',
            'email' => 'hack@t.es',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => User::ROLE_SUPER_ADMIN,
        ])->assertForbidden();
        $this->actingAs($user)->putJson("/users/{$user->id}", [
            'name' => $user->name,
            'email' => $user->email,
            'role' => User::ROLE_SUPER_ADMIN,
        ])->assertForbidden();

        $this->assertSame(User::ROLE_USER, $user->fresh()->role);
        $this->assertDatabaseMissing('users', ['email' => 'hack@t.es']);
    }

    public function test_admin_accede_a_usuarios_pero_no_a_logs(): void
    {
        $admin = $this->crearUsuario(User::ROLE_ADMIN);

        $this->actingAs($admin)->get('/users')->assertOk();
        $this->actingAs($admin)->get('/login-logs')->assertForbidden();
    }

    public function test_super_admin_accede_a_logs(): void
    {
        $super = $this->crearUsuario(User::ROLE_SUPER_ADMIN);

        $this->actingAs($super)->get('/users')->assertOk();
        $this->actingAs($super)->get('/login-logs')->assertOk();
    }

    public function test_admin_crea_usuarios_pero_no_super_admins(): void
    {
        $admin = $this->crearUsuario(User::ROLE_ADMIN);

        $this->actingAs($admin)->post('/users', [
            'name' => 'Nuevo',
            'email' => 'nuevo@t.es',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => User::ROLE_USER,
        ])->assertRedirect(route('users.index'));
        $this->assertDatabaseHas('users', ['email' => 'nuevo@t.es', 'role' => User::ROLE_USER]);

        $this->actingAs($admin)->post('/users', [
            'name' => 'Nuevo super',
            'email' => 'nuevosuper@t.es',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => User::ROLE_SUPER_ADMIN,
        ])->assertSessionHasErrors('role');
        $this->assertDatabaseMissing('users', ['email' => 'nuevosuper@t.es']);
    }

    public function test_admin_no_puede_ascenderse_ni_tocar_super_admins(): void
    {
        $admin = $this->crearUsuario(User::ROLE_ADMIN);
        $super = $this->crearUsuario(User::ROLE_SUPER_ADMIN);

        $this->actingAs($admin)->put("/users/{$admin->id}", [
            'name' => $admin->name,
            'email' => $admin->email,
            'role' => User::ROLE_SUPER_ADMIN,
        ])->assertSessionHasErrors('role');
        $this->assertSame(User::ROLE_ADMIN, $admin->fresh()->role);

        $this->actingAs($admin)->put("/users/{$super->id}", [
            'name' => 'Cambiado',
            'email' => $super->email,
            'role' => User::ROLE_USER,
        ])->assertSessionHas('error');
        $this->assertSame(User::ROLE_SUPER_ADMIN, $super->fresh()->role);

        $this->actingAs($admin)->deleteJson("/users/{$super->id}")->assertForbidden();
        $this->assertNotNull($super->fresh());
    }

    public function test_admin_edita_y_borra_usuarios_normales(): void
    {
        $admin = $this->crearUsuario(User::ROLE_ADMIN);
        $user = $this->crearUsuario(User::ROLE_USER);

        $this->actingAs($admin)->put("/users/{$user->id}", [
            'name' => 'Editado',
            'email' => $user->email,
            'role' => User::ROLE_ADMIN,
        ])->assertSessionHas('success');
        $this->assertSame('Editado', $user->fresh()->name);

        $this->actingAs($admin)->deleteJson("/users/{$user->id}")
            ->assertOk()
            ->assertJson(['success' => true]);
        $this->assertNull($user->fresh());
    }

    public function test_nadie_puede_borrarse_a_si_mismo(): void
    {
        $super = $this->crearUsuario(User::ROLE_SUPER_ADMIN);
        $this->crearUsuario(User::ROLE_SUPER_ADMIN);

        $this->actingAs($super)->deleteJson("/users/{$super->id}")->assertForbidden();
        $this->assertNotNull($super->fresh());
    }

    public function test_super_admin_gestiona_otros_super_admins(): void
    {
        $super = $this->crearUsuario(User::ROLE_SUPER_ADMIN);
        $otro = $this->crearUsuario(User::ROLE_SUPER_ADMIN);

        $this->actingAs($super)->deleteJson("/users/{$otro->id}")->assertOk();
        $this->assertNull($otro->fresh());
    }

    public function test_el_unico_super_admin_no_puede_quitarse_el_rol(): void
    {
        // Aislar el escenario: dentro de la transacción no queda ningún otro super_admin.
        User::where('role', User::ROLE_SUPER_ADMIN)->update(['role' => User::ROLE_ADMIN]);
        $super = $this->crearUsuario(User::ROLE_SUPER_ADMIN);

        $this->actingAs($super)->put("/users/{$super->id}", [
            'name' => $super->name,
            'email' => $super->email,
            'role' => User::ROLE_ADMIN,
        ])->assertSessionHas('error');
        $this->assertSame(User::ROLE_SUPER_ADMIN, $super->fresh()->role);
    }

    public function test_nombre_con_comillas_no_rompe_la_tabla(): void
    {
        $super = $this->crearUsuario(User::ROLE_SUPER_ADMIN);
        $this->crearUsuario(User::ROLE_USER)->update(['name' => "O'Brien');alert(1);//"]);

        $html = $this->actingAs($super)->get('/users')->assertOk()->getContent();

        $this->assertStringNotContainsString("'O'Brien');alert(1);//'", $html);
    }
}
