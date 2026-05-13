<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Añadir 'en_pausa' al enum de estado
        DB::statement("ALTER TABLE obras MODIFY estado ENUM('planificacion', 'ejecucion', 'en_pausa', 'finalizada') NOT NULL DEFAULT 'planificacion'");

        // Añadir columna tipo (subcontratista / contratista)
        Schema::table('obras', function (Blueprint $table) {
            $table->enum('tipo', ['subcontratista', 'contratista'])
                ->default('subcontratista')
                ->after('estado');
        });
    }

    public function down(): void
    {
        Schema::table('obras', function (Blueprint $table) {
            $table->dropColumn('tipo');
        });

        DB::statement("ALTER TABLE obras MODIFY estado ENUM('planificacion', 'ejecucion', 'finalizada') NOT NULL DEFAULT 'planificacion'");
    }
};
