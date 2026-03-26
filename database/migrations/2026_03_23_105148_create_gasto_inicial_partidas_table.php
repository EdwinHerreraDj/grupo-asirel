<?php
// database/migrations/xxxx_create_gasto_inicial_partidas_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gasto_inicial_partidas', function (Blueprint $table) {
            $table->id();

            $table->foreignId('obra_id')
                ->constrained('obras')
                ->restrictOnDelete();

            // Referencia al capítulo (oficio)
            $table->unsignedBigInteger('obra_gasto_categoria_id');
            $table->foreign('obra_gasto_categoria_id')
                ->references('id')
                ->on('obra_gasto_categorias')
                ->restrictOnDelete();

            // Vinculación opcional con su partida de venta equivalente
            $table->foreignId('presupuesto_venta_partida_id')
                ->nullable()
                ->constrained('presupuesto_venta_partidas')
                ->nullOnDelete();

            $table->string('codigo', 50)->nullable();
            $table->text('descripcion');
            $table->string('unidad', 50)->nullable();
            $table->decimal('medicion', 15, 4)->default(0);
            $table->decimal('precio_unitario', 15, 4)->default(0);
            $table->decimal('importe', 15, 2)->default(0);
            $table->unsignedInteger('orden')->default(0);
            $table->boolean('activo')->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gasto_inicial_partidas');
    }
};
