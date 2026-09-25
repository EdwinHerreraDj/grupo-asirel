<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/** Mi perfil: datos, foto y contraseña del propio usuario. */
class PerfilTest extends TestCase
{
    use DatabaseTransactions;

    private User $usuario;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

        $this->usuario = User::factory()->create([
            'role' => User::ROLE_USER,
            'name' => 'Ana Perez',
            'email' => 'perfil-'.uniqid().'@t.es',
            'password' => 'password123',
        ]);
    }

    public function test_hay_que_tener_sesion(): void
    {
        $this->get('/mi-perfil')->assertRedirect('/login');
        $this->post('/mi-perfil', ['name' => 'X', 'email' => 'x@t.es'])->assertRedirect('/login');
    }

    public function test_cualquier_usuario_ve_su_perfil(): void
    {
        $this->actingAs($this->usuario)->get('/mi-perfil')->assertOk()
            ->assertSee('Mi perfil')
            ->assertSee($this->usuario->name)
            ->assertSee($this->usuario->email)
            ->assertSee('Cambiar la contraseña');
    }

    public function test_actualiza_nombre_email_y_foto(): void
    {
        $email = 'nuevo-'.uniqid().'@t.es';

        $this->actingAs($this->usuario)->post('/mi-perfil', [
            'name' => 'Ana Gomez',
            'email' => $email,
            'avatar' => UploadedFile::fake()->image('yo.png', 256, 256),
        ])->assertRedirect(route('perfil.index'))->assertSessionHas('perfil_ok');

        $this->usuario->refresh();
        $this->assertSame('Ana Gomez', $this->usuario->name);
        $this->assertSame($email, $this->usuario->email);
        $this->assertNotNull($this->usuario->avatar);
        Storage::disk('public')->assertExists($this->usuario->avatar);

        // El menú de arriba lee el nombre de la sesión.
        $this->assertSame('Ana Gomez', session('user_name'));

        // Al cambiar la foto se borra la anterior.
        $anterior = $this->usuario->avatar;
        $this->actingAs($this->usuario)->post('/mi-perfil', [
            'name' => 'Ana Gomez',
            'email' => $email,
            'avatar' => UploadedFile::fake()->image('otra.png', 256, 256),
        ])->assertRedirect();
        Storage::disk('public')->assertMissing($anterior);

        // Y se puede quitar.
        $foto = $this->usuario->fresh()->avatar;
        $this->actingAs($this->usuario)->delete('/mi-perfil/avatar')->assertRedirect(route('perfil.index'));
        $this->assertNull($this->usuario->fresh()->avatar);
        Storage::disk('public')->assertMissing($foto);
    }

    public function test_no_se_puede_repetir_el_email_de_otro_ni_subir_cualquier_archivo(): void
    {
        $otro = User::factory()->create([
            'role' => User::ROLE_USER,
            'name' => 'Otro',
            'email' => 'ocupado-'.uniqid().'@t.es',
            'password' => 'password123',
        ]);

        $this->actingAs($this->usuario)->post('/mi-perfil', [
            'name' => 'Ana Perez',
            'email' => $otro->email,
        ])->assertSessionHasErrors('email');

        $this->actingAs($this->usuario)->post('/mi-perfil', [
            'name' => 'Ana Perez',
            'email' => $this->usuario->email,
            'avatar' => UploadedFile::fake()->create('virus.pdf', 100, 'application/pdf'),
        ])->assertSessionHasErrors('avatar');

        // Guardar el mismo email propio sí vale.
        $this->actingAs($this->usuario)->post('/mi-perfil', [
            'name' => 'Ana Perez',
            'email' => $this->usuario->email,
        ])->assertSessionHasNoErrors();
    }

    public function test_el_rol_no_se_puede_cambiar_desde_el_perfil(): void
    {
        $this->actingAs($this->usuario)->post('/mi-perfil', [
            'name' => 'Ana Perez',
            'email' => $this->usuario->email,
            'role' => User::ROLE_SUPER_ADMIN,
        ])->assertRedirect();

        $this->assertSame(User::ROLE_USER, $this->usuario->fresh()->role);
    }

    public function test_cambio_de_contrasena(): void
    {
        // Sin la actual correcta, no.
        $this->actingAs($this->usuario)->post('/mi-perfil/contrasena', [
            'contrasena_actual' => 'la-que-no-es',
            'password' => 'nuevaClave123',
            'password_confirmation' => 'nuevaClave123',
        ])->assertSessionHasErrors('contrasena_actual');

        // Si la repetición no coincide, tampoco.
        $this->actingAs($this->usuario)->post('/mi-perfil/contrasena', [
            'contrasena_actual' => 'password123',
            'password' => 'nuevaClave123',
            'password_confirmation' => 'otraCosa123',
        ])->assertSessionHasErrors('password');

        // Ni una demasiado corta.
        $this->actingAs($this->usuario)->post('/mi-perfil/contrasena', [
            'contrasena_actual' => 'password123',
            'password' => 'corta',
            'password_confirmation' => 'corta',
        ])->assertSessionHasErrors('password');

        $this->assertTrue(Hash::check('password123', $this->usuario->fresh()->password));

        // Con todo correcto, sí.
        $this->actingAs($this->usuario)->post('/mi-perfil/contrasena', [
            'contrasena_actual' => 'password123',
            'password' => 'nuevaClave123',
            'password_confirmation' => 'nuevaClave123',
        ])->assertRedirect(route('perfil.index'))->assertSessionHas('perfil_ok');

        $this->assertTrue(Hash::check('nuevaClave123', $this->usuario->fresh()->password));

        // Y se puede entrar con la nueva.
        $this->post('/logout');
        $this->post('/login', ['email' => $this->usuario->email, 'password' => 'nuevaClave123'])
            ->assertSessionHasNoErrors();
    }

    public function test_el_menu_de_arriba_lleva_al_perfil_y_muestra_la_foto(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'name' => 'Admin perfil',
            'email' => 'admin-perfil-'.uniqid().'@t.es',
            'password' => 'password123',
        ]);

        $html = $this->actingAs($admin)->get('/mi-perfil')->assertOk()->getContent();
        $this->assertStringContainsString(route('perfil.index'), $html);
        $this->assertStringContainsString('Cerrar sesión', $html);

        $admin->update(['avatar' => 'avatares/foto-admin.png']);
        $html = $this->actingAs($admin)->get('/mi-perfil')->assertOk()->getContent();
        $this->assertStringContainsString('avatares/foto-admin.png', $html);
    }
}
