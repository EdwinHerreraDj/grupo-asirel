<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('resumen_fichajes_mensuales', function (Blueprint $table) {
            $table->id();

            $table->foreignId('obra_id')->constrained('obras')->onDelete('cascade');

            $table->unsignedBigInteger('empleado_id');

            $table->string('mes', 7);

            $table->decimal('horas_trabajadas', 8, 2)->default(0);
            $table->decimal('tarifa_hora', 8, 2)->nullable();
            $table->decimal('total_ganado', 10, 2)->nullable();
            $table->integer('metros_realizados')->nullable();

            $table->timestamps();

            $table->unique(['obra_id', 'empleado_id', 'mes']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('resumen_fichajes_mensuales');
    }
};
