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
        Schema::create('certificacion_detalles', function (Blueprint $table) {

            $table->id();

            // RELACIÓN CON CERTIFICACIÓN
            $table->foreignId('certificacion_id')
                ->constrained('certificaciones')
                ->cascadeOnDelete();

            // DETALLE DEL TRABAJO
            $table->string('concepto');
            $table->string('unidad', 20)->nullable();

            $table->decimal('cantidad', 12, 2)->default(0);
            $table->decimal('precio_unitario', 12, 2)->default(0);

            // IMPORTE CALCULADO (BASE)
            $table->decimal('importe_linea', 12, 2)->default(0);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('certificacion_detalles');
    }
};
