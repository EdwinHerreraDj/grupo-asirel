<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('presupuesto_venta_partidas', function (Blueprint $table) {
            $table->foreignId('gasto_inicial_partida_id')
                ->nullable()
                ->after('obra_id')
                ->constrained('gasto_inicial_partidas')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('presupuesto_venta_partidas', function (Blueprint $table) {
            $table->dropForeign(['gasto_inicial_partida_id']);
            $table->dropColumn('gasto_inicial_partida_id');
        });
    }
};
