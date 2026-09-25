<?php

namespace Tests\Feature;

use App\Models\Empresa;
use App\Models\PanelApariencia;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Apariencia del panel: logo del menú, icono del menú plegado y favicon.
 * Solo admin y super_admin. El logo de la empresa sigue siendo el de los PDFs.
 */
class AparienciaPanelTest extends TestCase
{
    use DatabaseTransactions;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
        PanelApariencia::olvidar();

        $this->admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'name' => 'Admin apariencia',
            'email' => 'apariencia-'.uniqid().'@t.es',
            'password' => 'password123',
        ]);
    }

    protected function tearDown(): void
    {
        PanelApariencia::olvidar();

        parent::tearDown();
    }

    public function test_solo_administradores(): void
    {
        $user = User::factory()->create([
            'role' => User::ROLE_USER,
            'name' => 'Usuario apariencia',
            'email' => 'apariencia-user-'.uniqid().'@t.es',
            'password' => 'password123',
        ]);

        $this->actingAs($user)->get('/configuracion/apariencia')->assertForbidden();
        $this->actingAs($user)->post('/configuracion/apariencia', [
            'logo' => UploadedFile::fake()->image('logo.png', 320, 80),
        ])->assertForbidden();
        $this->actingAs($user)->delete('/configuracion/apariencia/logo')->assertForbidden();

        $this->actingAs($this->admin)->get('/configuracion/apariencia')->assertOk()->assertSee('Apariencia del panel');
    }

    public function test_subir_las_tres_imagenes_y_sustituirlas(): void
    {
        $this->actingAs($this->admin)->post('/configuracion/apariencia', [
            'logo' => UploadedFile::fake()->image('logo.png', 320, 80),
            'logo_pequeno' => UploadedFile::fake()->image('icono.png', 128, 128),
            'favicon' => UploadedFile::fake()->image('favicon.png', 48, 48),
        ])->assertRedirect(route('configuracion.apariencia'))->assertSessionHas('apariencia_ok');

        $apariencia = PanelApariencia::firstOrFail();
        foreach (PanelApariencia::CAMPOS as $campo) {
            $this->assertNotNull($apariencia->{$campo}, "Falta {$campo}");
            Storage::disk('public')->assertExists($apariencia->{$campo});
        }

        // Al sustituir una imagen, la anterior se borra del disco.
        $anterior = $apariencia->logo;
        $this->actingAs($this->admin)->post('/configuracion/apariencia', [
            'logo' => UploadedFile::fake()->image('otro-logo.png', 320, 80),
        ])->assertRedirect();

        $this->assertNotSame($anterior, PanelApariencia::firstOrFail()->logo);
        Storage::disk('public')->assertMissing($anterior);

        // Sin archivos no cambia nada.
        $logoActual = PanelApariencia::firstOrFail()->logo;
        $this->actingAs($this->admin)->post('/configuracion/apariencia', [])->assertRedirect();
        $this->assertSame($logoActual, PanelApariencia::firstOrFail()->logo);
    }

    public function test_validaciones(): void
    {
        $antes = PanelApariencia::first()?->logo;

        $this->actingAs($this->admin)->post('/configuracion/apariencia', [
            'logo' => UploadedFile::fake()->create('logo.pdf', 100, 'application/pdf'),
        ])->assertSessionHasErrors('logo');

        $this->actingAs($this->admin)->post('/configuracion/apariencia', [
            'favicon' => UploadedFile::fake()->create('favicon.png', 900, 'image/png'),
        ])->assertSessionHasErrors('favicon');

        // Un archivo rechazado no cambia nada.
        $this->assertSame($antes, PanelApariencia::first()?->logo);
    }

    public function test_quitar_una_imagen_vuelve_a_la_de_por_defecto(): void
    {
        $this->actingAs($this->admin)->post('/configuracion/apariencia', [
            'logo' => UploadedFile::fake()->image('logo.png', 320, 80),
        ])->assertRedirect();

        $ruta = PanelApariencia::firstOrFail()->logo;

        $this->actingAs($this->admin)->delete('/configuracion/apariencia/logo')
            ->assertRedirect(route('configuracion.apariencia'))->assertSessionHas('apariencia_ok');

        $this->assertNull(PanelApariencia::firstOrFail()->logo);
        Storage::disk('public')->assertMissing($ruta);

        $this->actingAs($this->admin)->delete('/configuracion/apariencia/inventado')->assertNotFound();
    }

    public function test_el_panel_usa_estas_imagenes_y_no_el_logo_de_la_empresa(): void
    {
        // Logo de empresa: solo para los PDFs e informes.
        $empresa = Empresa::first() ?? Empresa::forceCreate(['nombre' => 'Empresa test']);
        $empresa->update(['logo' => 'empresa/logo-de-la-empresa.png']);

        $apariencia = PanelApariencia::actual();
        $apariencia->update([
            'logo' => 'panel/logo-del-panel.png',
            'logo_pequeno' => 'panel/icono-del-panel.png',
        ]);
        PanelApariencia::olvidar();

        $html = $this->actingAs($this->admin)->get('/rrhh')->assertOk()->getContent();

        $this->assertStringContainsString('panel/logo-del-panel.png', $html);
        $this->assertStringContainsString('panel/icono-del-panel.png', $html);
        $this->assertStringNotContainsString('empresa/logo-de-la-empresa.png', $html);

        // Sin favicon propio se usa el icono del menú plegado.
        $this->assertStringContainsString('rel="shortcut icon" href="'.$apariencia->url('logo_pequeno').'"', $html);

        // Y el login también usa el logo del panel.
        $this->post('/logout');
        $login = $this->get('/login')->assertOk()->getContent();
        $this->assertStringContainsString('panel/logo-del-panel.png', $login);
    }
}
