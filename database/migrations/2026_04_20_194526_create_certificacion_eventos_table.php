<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabla de auditor\u00eda de transiciones de estado de certificaciones.
 *
 * Eventos registrados: creada, aceptada, anulada, facturada.
 * user_id nullable para cubrir invocaciones desde comandos artisan o jobs.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('certificacion_eventos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('certificacion_id')
                ->constrained('certificaciones')
                ->cascadeOnDelete();
            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->string('tipo', 30); // creada | aceptada | anulada | facturada
            $table->string('estado_previo', 30)->nullable();
            $table->string('estado_nuevo', 30)->nullable();
            $table->text('motivo')->nullable();
            $table->timestamps();

            $table->index(['certificacion_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('certificacion_eventos');
    }
};
