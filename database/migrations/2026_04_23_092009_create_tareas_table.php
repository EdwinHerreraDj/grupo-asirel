<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tareas', function (Blueprint $table) {
            $table->id();

            $table->string('titulo', 255);
            $table->text('descripcion')->nullable();

            $table->enum('prioridad', ['baja', 'media', 'alta'])->default('media');
            $table->enum('estado', ['pendiente', 'en_curso', 'completada'])->default('pendiente');

            $table->date('fecha_limite')->nullable();
            $table->timestamp('completada_en')->nullable();

            $table->foreignId('obra_id')->nullable()
                ->constrained('obras')
                ->nullOnDelete();

            $table->foreignId('asignado_a')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignId('creado_por')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->timestamps();

            $table->index(['asignado_a', 'estado']);
            $table->index(['creado_por', 'estado']);
            $table->index(['obra_id', 'estado']);
            $table->index('fecha_limite');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tareas');
    }
};
