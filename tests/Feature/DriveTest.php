<?php

namespace Tests\Feature;

use App\Models\File;
use App\Models\Folder;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use ZipArchive;

/**
 * Drive (fase 1): acceso solo admin, almacenamiento privado, compatibilidad con
 * los dos formatos de archivo, borrado de carpetas con contraseña, extracción
 * de ZIP y traslado de archivos públicos a privado.
 */
class DriveTest extends TestCase
{
    use DatabaseTransactions;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
        Storage::fake('public');

        $this->admin = $this->usuario(User::ROLE_ADMIN);
    }

    // -------------------------------------------------------------
    // Acceso y almacenamiento
    // -------------------------------------------------------------

    public function test_solo_administradores_acceden_al_drive(): void
    {
        $user = $this->usuario(User::ROLE_USER);
        $file = $this->archivo($this->carpeta('Acceso'), 'a.pdf', 'local', 'drive/archivos/t/a.pdf');

        $this->actingAs($user)->get('/empresa/drive-app')->assertForbidden();
        $this->actingAs($user)->get("/drive/ver/{$file->id}")->assertForbidden();

        foreach (['/api/folders/0/content', '/api/drive/search?q=ab', '/api/files/expiring', "/api/files/{$file->id}/download"] as $url) {
            $this->actingAs($user)->getJson($url)->assertForbidden();
        }

        $this->actingAs($user)->deleteJson("/api/files/{$file->id}")->assertForbidden();
        $this->assertNotNull($file->fresh());

        $this->actingAs($this->admin)->get('/empresa/drive-app')->assertOk();
        $this->actingAs($this->usuario(User::ROLE_SUPER_ADMIN))->getJson('/api/folders/0/content')->assertOk();
    }

    public function test_las_subidas_se_guardan_en_almacenamiento_privado(): void
    {
        $carpeta = $this->carpeta('Subidas');

        $this->actingAs($this->admin)->post('/api/files', [
            'file' => UploadedFile::fake()->create('Nómina enero.pdf', 20, 'application/pdf'),
            'folder_id' => $carpeta->id,
            'tiene_caducidad' => '0',
        ], ['Accept' => 'application/json'])->assertCreated();

        $file = File::where('folder_id', $carpeta->id)->firstOrFail();

        $this->assertSame('local', $file->disco);
        $this->assertStringStartsWith('drive/archivos/', $file->ruta);
        $this->assertSame('Nómina enero.pdf', $file->nombre);
        Storage::disk('local')->assertExists($file->ruta);
        $this->assertSame([], Storage::disk('public')->allFiles());
    }

    public function test_no_se_puede_subir_a_la_raiz_ni_a_una_carpeta_inexistente(): void
    {
        $this->actingAs($this->admin)->postJson('/api/files', [
            'file' => UploadedFile::fake()->create('x.pdf', 1),
            'folder_id' => 0,
        ])->assertStatus(422)->assertJsonValidationErrors('folder_id');

        $this->actingAs($this->admin)->postJson('/api/files', [
            'file' => UploadedFile::fake()->create('x.pdf', 1),
            'folder_id' => 999999999,
        ])->assertStatus(422)->assertJsonValidationErrors('folder_id');

        $this->assertSame([], Storage::disk('local')->allFiles());
    }

    public function test_el_drive_es_compartido_entre_administradores(): void
    {
        $otroAdmin = $this->usuario(User::ROLE_SUPER_ADMIN);
        $carpeta = $this->carpeta('Compartida');
        $destino = $this->carpeta('Destino');
        $nombre = 'compartido-'.uniqid().'.pdf';

        $file = $this->archivo($carpeta, $nombre, 'local', 'drive/archivos/t/c.pdf');
        $file->update(['tiene_caducidad' => true, 'fecha_caducidad' => now()->addDays(5)->toDateString()]);

        $this->actingAs($otroAdmin)->get("/drive/ver/{$file->id}")->assertOk();
        $this->actingAs($otroAdmin)->get("/api/files/{$file->id}/download")->assertOk();

        $busqueda = $this->actingAs($otroAdmin)->getJson('/api/drive/search?q='.urlencode($nombre))->assertOk();
        $this->assertTrue(collect($busqueda->json('files'))->pluck('id')->contains($file->id));

        $caducidades = $this->actingAs($otroAdmin)->getJson('/api/files/expiring')->assertOk();
        $this->assertTrue(collect($caducidades->json('files'))->pluck('id')->contains($file->id));

        $this->actingAs($otroAdmin)->postJson("/api/files/{$file->id}/move", ['target_folder_id' => $destino->id])->assertOk();
        $this->assertSame($destino->id, (int) $file->fresh()->folder_id);
    }

    public function test_se_sirven_archivos_de_los_dos_formatos(): void
    {
        $carpeta = $this->carpeta('Formatos');
        $antiguo = $this->archivo($carpeta, 'antiguo.pdf', 'local', "drive/{$carpeta->id}/".str_repeat('a', 40).'.pdf', 'antiguo');
        $publico = $this->archivo($carpeta, 'publico.pdf', 'public', 'uploads/123_abc_publico.pdf', 'publico');

        foreach ([$antiguo, $publico] as $file) {
            $this->actingAs($this->admin)->get("/api/files/{$file->id}/download")->assertOk();
            $this->actingAs($this->admin)->get("/drive/ver/{$file->id}")->assertOk();
        }

        // Si el registro apunta al disco equivocado, se encuentra en el otro.
        $antiguo->update(['disco' => 'public']);
        $this->actingAs($this->admin)->get("/api/files/{$antiguo->id}/download")->assertOk();
    }

    public function test_nombres_de_carpeta_repetidos_devuelven_aviso_y_no_error(): void
    {
        $padre = $this->carpeta('Padre');

        $this->actingAs($this->admin)->postJson('/api/folders', ['nombre' => 'Contratos', 'parent_id' => $padre->id])->assertCreated();
        $this->actingAs($this->admin)->postJson('/api/folders', ['nombre' => 'Contratos', 'parent_id' => $padre->id])
            ->assertStatus(422)
            ->assertJsonValidationErrors('nombre');

        $otra = Folder::forceCreate(['nombre' => 'Nóminas', 'parent_id' => $padre->id, 'tipo' => 1]);
        $this->actingAs($this->admin)->putJson("/api/folders/{$otra->id}", ['nombre' => 'Contratos'])->assertStatus(422);
        $this->actingAs($this->admin)->putJson("/api/folders/{$otra->id}", ['nombre' => 'Nóminas'])->assertOk();

        $this->actingAs($this->admin)->postJson('/api/folders', ['nombre' => str_repeat('x', 151), 'parent_id' => $padre->id])
            ->assertStatus(422)
            ->assertJsonValidationErrors('nombre');
    }

    public function test_el_listado_trae_autor_y_contadores_para_la_vista_de_lista(): void
    {
        $carpeta = $this->carpeta('Lista');
        $sub = Folder::forceCreate(['nombre' => 'Sub', 'parent_id' => $carpeta->id, 'tipo' => 1]);
        Folder::forceCreate(['nombre' => 'Nieta', 'parent_id' => $sub->id, 'tipo' => 1]);
        $this->archivo($sub, 'dentro.pdf', 'local', 'drive/archivos/t/dentro.pdf');
        $this->archivo($carpeta, 'fuera.pdf', 'local', 'drive/archivos/t/fuera.pdf');

        $json = $this->actingAs($this->admin)->getJson("/api/folders/{$carpeta->id}/content")->assertOk();

        $subcarpeta = collect($json->json('folders'))->firstWhere('id', $sub->id);
        $this->assertSame(1, $subcarpeta['files_count']);
        $this->assertSame(1, $subcarpeta['children_count']);

        $archivo = collect($json->json('files'))->firstWhere('nombre', 'fuera.pdf');
        $this->assertSame('Usuario drive', $archivo['usuario']['name']);
        $this->assertArrayNotHasKey('email', $archivo['usuario']);

        // La búsqueda también trae el autor.
        $busqueda = $this->actingAs($this->admin)->getJson('/api/drive/search?q=fuera.pdf')->assertOk();
        $encontrado = collect($busqueda->json('files'))->firstWhere('nombre', 'fuera.pdf');
        $this->assertSame('Usuario drive', $encontrado['usuario']['name']);
    }

    // -------------------------------------------------------------
    // Borrados
    // -------------------------------------------------------------

    public function test_borrar_una_carpeta_completa_exige_la_contrasena(): void
    {
        $raiz = $this->carpeta('Trabajador');
        $sub = Folder::forceCreate(['nombre' => 'Nóminas', 'parent_id' => $raiz->id, 'tipo' => 1]);
        $subsub = Folder::forceCreate(['nombre' => '2026', 'parent_id' => $sub->id, 'tipo' => 1]);
        $dni = $this->archivo($raiz, 'dni.pdf', 'local', 'drive/archivos/t/dni.pdf');
        $enero = $this->archivo($subsub, 'enero.pdf', 'public', 'uploads/1_x_enero.pdf');

        $this->actingAs($this->admin)->deleteJson("/api/folders/{$raiz->id}")
            ->assertStatus(422)
            ->assertJsonValidationErrors('password');

        $this->actingAs($this->admin)->deleteJson("/api/folders/{$raiz->id}", ['password' => 'incorrecta'])
            ->assertStatus(422)
            ->assertJsonValidationErrors('password');

        $this->assertNotNull($raiz->fresh());
        $this->assertNotNull($enero->fresh());
        Storage::disk('public')->assertExists($enero->ruta);

        $this->actingAs($this->admin)->deleteJson("/api/folders/{$raiz->id}", ['password' => 'password123'])
            ->assertOk()
            ->assertJson(['carpetas' => 3, 'archivos' => 2]);

        foreach ([$raiz, $sub, $subsub, $dni, $enero] as $modelo) {
            $this->assertNull($modelo->fresh());
        }
        Storage::disk('local')->assertMissing($dni->ruta);
        Storage::disk('public')->assertMissing($enero->ruta);

        $this->assertDatabaseHas('drive_eliminaciones', [
            'user_id' => $this->admin->id,
            'tipo' => 'carpeta',
            'nombre' => $raiz->nombre,
            'carpetas' => 3,
            'archivos' => 2,
        ]);
    }

    public function test_demasiados_intentos_de_contrasena_se_bloquean(): void
    {
        $carpeta = $this->carpeta('Intentos');

        for ($i = 0; $i < 5; $i++) {
            $this->actingAs($this->admin)->deleteJson("/api/folders/{$carpeta->id}", ['password' => 'mala'])->assertStatus(422);
        }

        $this->actingAs($this->admin)->deleteJson("/api/folders/{$carpeta->id}", ['password' => 'password123'])->assertStatus(429);
        $this->assertNotNull($carpeta->fresh());
    }

    public function test_borrar_un_archivo_registra_y_elimina_el_fichero(): void
    {
        $file = $this->archivo($this->carpeta('Suelto'), 'suelto.pdf', 'local', 'drive/archivos/t/suelto.pdf');

        $this->actingAs($this->admin)->deleteJson("/api/files/{$file->id}")->assertOk();

        $this->assertNull($file->fresh());
        Storage::disk('local')->assertMissing($file->ruta);
        $this->assertDatabaseHas('drive_eliminaciones', ['tipo' => 'archivo', 'nombre' => 'suelto.pdf', 'archivos' => 1]);
    }

    // -------------------------------------------------------------
    // ZIP
    // -------------------------------------------------------------

    public function test_extraer_un_zip_con_carpeta_raiz_no_la_duplica(): void
    {
        $destino = $this->carpeta('Destino ZIP');
        $zip = $this->archivoZip($destino, 'Docs.zip', [
            'Docs/a.txt' => 'A',
            'Docs/sub/b.txt' => 'B',
            '__MACOSX/Docs/._a.txt' => 'basura',
        ]);

        $this->actingAs($this->admin)->postJson("/api/files/{$zip->id}/extract")
            ->assertOk()
            ->assertJson(['stats' => ['folders' => 1, 'files' => 2]]);

        $docs = Folder::where('parent_id', $destino->id)->where('nombre', 'Docs')->firstOrFail();
        $this->assertFalse(Folder::where('parent_id', $docs->id)->where('nombre', 'Docs')->exists());
        $this->assertFalse(Folder::where('parent_id', $destino->id)->where('nombre', '__MACOSX')->exists());
        $this->assertTrue(File::where('folder_id', $docs->id)->where('nombre', 'a.txt')->exists());

        $sub = Folder::where('parent_id', $docs->id)->where('nombre', 'sub')->firstOrFail();
        $b = File::where('folder_id', $sub->id)->where('nombre', 'b.txt')->firstOrFail();
        $this->assertSame('local', $b->disco);
        Storage::disk('local')->assertExists($b->ruta);

        // Extraer otra vez no falla: se crea con nombre único.
        $this->actingAs($this->admin)->postJson("/api/files/{$zip->id}/extract")->assertOk();
        $this->assertTrue(Folder::where('parent_id', $destino->id)->where('nombre', 'Docs (1)')->exists());
    }

    public function test_extraer_un_zip_sin_carpeta_raiz_usa_el_nombre_del_zip(): void
    {
        $destino = $this->carpeta('Destino ZIP 2');
        $zip = $this->archivoZip($destino, 'Paquete.zip', ['x.txt' => 'X', 'y.txt' => 'Y']);

        $this->actingAs($this->admin)->postJson("/api/files/{$zip->id}/extract")->assertOk();

        $base = Folder::where('parent_id', $destino->id)->where('nombre', 'Paquete')->firstOrFail();
        $this->assertSame(2, File::where('folder_id', $base->id)->count());
    }

    public function test_un_zip_con_rutas_no_validas_se_rechaza(): void
    {
        $destino = $this->carpeta('Destino ZIP 3');
        $zip = $this->archivoZip($destino, 'malo.zip', ['../fuera.txt' => 'X']);

        $this->actingAs($this->admin)->postJson("/api/files/{$zip->id}/extract")->assertStatus(422);

        $this->assertSame(0, Folder::where('parent_id', $destino->id)->count());
    }

    // -------------------------------------------------------------
    // Caducidades y traslado a privado
    // -------------------------------------------------------------

    public function test_un_documento_que_caduca_hoy_es_proximo_y_no_vencido(): void
    {
        $carpeta = $this->carpeta('Caducidades');
        $hoy = $this->archivo($carpeta, 'hoy.pdf', 'local', 'drive/archivos/t/hoy.pdf');
        $ayer = $this->archivo($carpeta, 'ayer.pdf', 'local', 'drive/archivos/t/ayer.pdf');
        $hoy->update(['tiene_caducidad' => true, 'fecha_caducidad' => today()->toDateString()]);
        $ayer->update(['tiene_caducidad' => true, 'fecha_caducidad' => today()->subDay()->toDateString()]);

        $estados = collect($this->actingAs($this->admin)->getJson('/api/files/expiring')->assertOk()->json('files'))
            ->pluck('estado_caducidad', 'id');

        $this->assertSame('proximo', $estados[$hoy->id]);
        $this->assertSame('vencido', $estados[$ayer->id]);
    }

    public function test_el_comando_mueve_los_archivos_publicos_a_privado(): void
    {
        $carpeta = $this->carpeta('Migracion');
        $publico = $this->archivo($carpeta, 'Contrato firmado.pdf', 'public', 'uploads/1_a_contrato.pdf', 'contrato');
        $sinFichero = File::forceCreate([
            'folder_id' => $carpeta->id, 'usuario_id' => $this->admin->id, 'nombre' => 'falta.pdf',
            'ruta' => 'uploads/no_existe.pdf', 'disco' => 'public', 'tipo' => 'application/pdf', 'tiene_caducidad' => false,
        ]);

        // Simulación: no cambia nada.
        $this->artisan('drive:mover-a-privado', ['--dry-run' => true])->assertExitCode(0);
        $this->assertSame('public', $publico->fresh()->disco);
        Storage::disk('public')->assertExists('uploads/1_a_contrato.pdf');

        // Real.
        $this->artisan('drive:mover-a-privado')->assertExitCode(0);

        $publico->refresh();
        $this->assertSame('local', $publico->disco);
        $this->assertStringStartsWith('drive/archivos/', $publico->ruta);
        $this->assertSame('contrato', Storage::disk('local')->get($publico->ruta));
        Storage::disk('public')->assertMissing('uploads/1_a_contrato.pdf');

        // Sin fichero físico: se informa y no se toca.
        $this->assertSame('public', $sinFichero->fresh()->disco);

        // Idempotente: una segunda pasada no hace nada.
        $this->artisan('drive:mover-a-privado')->assertExitCode(0);
        $this->assertSame($publico->ruta, $publico->fresh()->ruta);
    }

    // -------------------------------------------------------------
    // Fixtures
    // -------------------------------------------------------------

    private function usuario(string $role): User
    {
        return User::factory()->create([
            'role' => $role,
            'name' => 'Usuario drive',
            'email' => 'drive-'.uniqid().'@t.es',
            'password' => 'password123',
        ]);
    }

    private function carpeta(string $nombre): Folder
    {
        return Folder::forceCreate([
            'nombre' => $nombre.' '.uniqid(),
            'parent_id' => 0,
            'tipo' => 1,
            'usuario_id' => $this->admin->id,
        ]);
    }

    private function archivo(Folder $carpeta, string $nombre, string $disco, string $ruta, string $contenido = 'contenido'): File
    {
        Storage::disk($disco)->put($ruta, $contenido);

        return File::forceCreate([
            'folder_id' => $carpeta->id,
            'usuario_id' => $this->admin->id,
            'nombre' => $nombre,
            'ruta' => $ruta,
            'disco' => $disco,
            'tipo' => 'application/pdf',
            'tamaño' => strlen($contenido),
            'tiene_caducidad' => false,
        ]);
    }

    /** @param array<string, string> $entradas ruta dentro del ZIP => contenido */
    private function archivoZip(Folder $carpeta, string $nombre, array $entradas): File
    {
        $temporal = tempnam(sys_get_temp_dir(), 'drivezip');
        $zip = new ZipArchive;
        $zip->open($temporal, ZipArchive::CREATE | ZipArchive::OVERWRITE);
        foreach ($entradas as $ruta => $contenido) {
            $zip->addFromString($ruta, $contenido);
        }
        $zip->close();

        $file = $this->archivo($carpeta, $nombre, 'local', 'drive/archivos/t/'.uniqid().'.zip', file_get_contents($temporal));
        @unlink($temporal);

        return $file;
    }
}
