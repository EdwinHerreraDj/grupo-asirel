<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('certificacion_detalles', function (Blueprint $table) {
            $table->foreignId('presupuesto_venta_partida_id')
                ->nullable()
                ->after('certificacion_id')
                ->constrained('presupuesto_venta_partidas')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('certificacion_detalles', function (Blueprint $table) {
            $table->dropForeign(['presupuesto_venta_partida_id']);
            $table->dropColumn('presupuesto_venta_partida_id');
        });
    }
};
