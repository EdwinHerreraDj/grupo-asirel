<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Renombra la FK `presupuesto_venta_partidas.gasto_inicial_partida_id`
 * a `coste_partida_id` para alinearla con la terminolog\u00eda del refactor
 * (Obra \u2192 Oficio \u2192 Partidas · con vinculaci\u00f3n venta \u2192 coste).
 */
return new class extends Migration
{
    public function up(): void
    {
        // 1. Drop FK existente (no se puede renombrar una columna con FK activa)
        Schema::table('presupuesto_venta_partidas', function (Blueprint $table) {
            $table->dropForeign(['gasto_inicial_partida_id']);
        });

        // 2. Renombrar columna
        Schema::table('presupuesto_venta_partidas', function (Blueprint $table) {
            $table->renameColumn('gasto_inicial_partida_id', 'coste_partida_id');
        });

        // 3. Recrear FK con el nuevo nombre (misma referencia: gasto_inicial_partidas.id)
        Schema::table('presupuesto_venta_partidas', function (Blueprint $table) {
            $table->foreign('coste_partida_id')
                ->references('id')
                ->on('gasto_inicial_partidas')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('presupuesto_venta_partidas', function (Blueprint $table) {
            $table->dropForeign(['coste_partida_id']);
        });

        Schema::table('presupuesto_venta_partidas', function (Blueprint $table) {
            $table->renameColumn('coste_partida_id', 'gasto_inicial_partida_id');
        });

        Schema::table('presupuesto_venta_partidas', function (Blueprint $table) {
            $table->foreign('gasto_inicial_partida_id')
                ->references('id')
                ->on('gasto_inicial_partidas')
                ->nullOnDelete();
        });
    }
};
