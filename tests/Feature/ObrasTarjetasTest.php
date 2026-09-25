<?php

namespace Tests\Feature;

use App\Livewire\Obras\Index;
use App\Models\Obra;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;
use Tests\TestCase;

/** El listado de obras se pinta con toda la información de cada tarjeta. */
class ObrasTarjetasTest extends TestCase
{
    use DatabaseTransactions;

    public function test_la_tarjeta_muestra_los_datos_de_la_obra(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'name' => 'Admin obras',
            'email' => 'obras-'.uniqid().'@t.es',
            'password' => 'password123',
        ]);

        $obra = Obra::forceCreate([
            'nombre' => 'Obra tarjeta '.uniqid(),
            'descripcion' => 'Descripción de prueba',
            'estado' => 'ejecucion',
            'tipo' => 'contratista',
            'importe_presupuestado' => 120000,
            'fecha_inicio' => '2026-03-01',
            'fecha_fin' => '2026-09-30',
        ]);

        Livewire::actingAs($admin)
            ->test(Index::class)
            ->assertOk()
            ->assertSee($obra->nombre)
            ->assertSee('ID #'.$obra->id)
            ->assertSee('Contratista')
            ->assertSee('En ejecución')
            ->assertSee('Presupuesto')
            ->assertSee('Resultado')
            ->assertSee('Balance')
            ->assertSee('Documentación')
            ->assertSee('120.000,00 €');
    }

    public function test_el_filtro_por_estado_funciona(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'name' => 'Admin obras 2',
            'email' => 'obras2-'.uniqid().'@t.es',
            'password' => 'password123',
        ]);

        $enEjecucion = Obra::forceCreate(['nombre' => 'En ejecución '.uniqid(), 'estado' => 'ejecucion', 'importe_presupuestado' => 0]);
        $finalizada = Obra::forceCreate(['nombre' => 'Finalizada '.uniqid(), 'estado' => 'finalizada', 'importe_presupuestado' => 0]);

        Livewire::actingAs($admin)
            ->test(Index::class)
            ->set('estado', 'ejecucion')
            ->assertSee($enEjecucion->nombre)
            ->assertDontSee($finalizada->nombre);
    }
}
