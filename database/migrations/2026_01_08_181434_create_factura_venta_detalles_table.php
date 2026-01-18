<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('factura_venta_detalles', function (Blueprint $table) {
            $table->id();

            // Relación con la factura de venta
            $table->foreignId('factura_venta_id')
                ->constrained('facturas_venta')
                ->onDelete('cascade');

            /**
             * Trazabilidad (opcional)
             * Si la línea viene de una certificación/detalle
             */
            $table->foreignId('certificacion_id')
                ->nullable()
                ->constrained('certificaciones')
                ->nullOnDelete();

            $table->foreignId('certificacion_detalle_id')
                ->nullable()
                ->constrained('certificacion_detalles')
                ->nullOnDelete();

            /**
             * Datos de línea "congelados"
             */
            $table->string('concepto', 255);
            $table->string('unidad', 50)->nullable();

            $table->decimal('cantidad', 12, 2)->default(0);
            $table->decimal('precio_unitario', 12, 2)->default(0);
            $table->decimal('importe_linea', 12, 2)->default(0);

            /**
             * Orden visual
             */
            $table->unsignedInteger('orden')->default(1);

            $table->timestamps();

            // Índices útiles
            $table->index(['factura_venta_id', 'orden']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('factura_venta_detalles');
    }
};
