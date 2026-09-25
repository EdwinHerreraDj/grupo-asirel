<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Throwable;

/**
 * Imágenes del panel (menú, menú plegado y favicon). Una sola fila.
 * El logo de la empresa se queda para los PDFs e informes.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('panel_apariencia')) {
            return;
        }

        Schema::create('panel_apariencia', function (Blueprint $table) {
            $table->id();
            $table->string('logo')->nullable();          // menú abierto
            $table->string('logo_pequeno')->nullable();  // menú plegado
            $table->string('favicon')->nullable();       // pestaña del navegador
            $table->timestamps();
        });

        // Hasta ahora el panel mostraba el logo de la empresa: se deja una COPIA
        // como logo inicial para que nada cambie de aspecto. Es una copia
        // aparte, así que cambiarla nunca afecta al logo de los PDFs.
        $logo = null;

        try {
            $logoEmpresa = Schema::hasTable('empresa') ? DB::table('empresa')->value('logo') : null;

            if ($logoEmpresa && Storage::disk('public')->exists($logoEmpresa)) {
                $copia = 'panel/logo-inicial-'.uniqid().'.'.pathinfo($logoEmpresa, PATHINFO_EXTENSION);
                Storage::disk('public')->copy($logoEmpresa, $copia);
                $logo = $copia;
            }
        } catch (Throwable $e) {
            report($e); // Si no se puede copiar, se usa el logo que trae la app.
        }

        DB::table('panel_apariencia')->insert([
            'logo' => $logo,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('panel_apariencia');
    }
};
