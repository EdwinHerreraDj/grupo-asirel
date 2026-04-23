<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Limpieza Fase 4-A del refactor Presupuesto:
 *  - Elimina el pivot antiguo `obra_gastos_iniciales` (ya no se escribe desde Fase 1).
 *  - Elimina la tabla hu\u00e9rfana `gastos_base` (0 usos en c\u00f3digo tras Fase 1).
 *  - Elimina la FK inversa redundante `gasto_inicial_partidas.presupuesto_venta_partida_id`
 *    (la vinculaci\u00f3n correcta es unidireccional: venta \u2192 coste).
 */
return new class extends Migration
{
    public function up(): void
    {
        // 1. Drop FK + columna inversa redundante en gasto_inicial_partidas
        Schema::table('gasto_inicial_partidas', function (Blueprint $table) {
            $table->dropForeign(['presupuesto_venta_partida_id']);
            $table->dropColumn('presupuesto_venta_partida_id');
        });

        // 2. Drop tabla pivot antigua
        Schema::dropIfExists('obra_gastos_iniciales');

        // 3. Drop tabla cat\u00e1logo hu\u00e9rfana
        Schema::dropIfExists('gastos_base');
    }

    public function down(): void
    {
        // Recrear tabla cat\u00e1logo hu\u00e9rfana (solo esqueleto, sin datos)
        Schema::create('gastos_base', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->text('descripcion')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        // Recrear pivot antiguo (solo esqueleto, sin datos)
        Schema::create('obra_gastos_iniciales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('obra_id')->constrained()->cascadeOnDelete();
            $table->foreignId('obra_gasto_categoria_id')
                ->constrained('obra_gasto_categorias')
                ->cascadeOnDelete();
            $table->decimal('importe', 10, 2)->default(0);
            $table->timestamps();
        });

        // Recrear columna + FK inversa
        Schema::table('gasto_inicial_partidas', function (Blueprint $table) {
            $table->foreignId('presupuesto_venta_partida_id')
                ->nullable()
                ->constrained('presupuesto_venta_partidas')
                ->nullOnDelete();
        });
    }
};
