<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('certificaciones', function (Blueprint $table) {
            $table->id();

            // OBRA ASOCIADA
            $table->unsignedBigInteger('obra_id');
            $table->foreign('obra_id')
                ->references('id')
                ->on('obras')
                ->onDelete('cascade'); // si borras una obra se eliminan sus certificaciones

            // FECHAS
            $table->date('fecha_ingreso');       // = fecha certificación
            $table->date('fecha_contable')->nullable();

            // DATOS CERTIFICACIÓN
            $table->string('numero_certificacion')->nullable();

            // OFICIO (categoría de obra)
            $table->unsignedBigInteger('obra_gasto_categoria_id');
            $table->foreign('obra_gasto_categoria_id')
                ->references('id')
                ->on('obra_gasto_categorias')
                ->onDelete('restrict');

            // DESCRIPCIÓN CORTA
            $table->string('especificacion')->nullable();

            // FACTURA O CERTIFICACIÓN
            $table->enum('tipo_documento', ['factura', 'certificacion']);

            // IMPORTE TOTAL
            $table->decimal('total', 12, 2);

            // ADJUNTO (PDF / imagen)
            $table->string('adjunto_url')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('certificaciones');
    }
};
