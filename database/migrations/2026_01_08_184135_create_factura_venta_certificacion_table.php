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
        Schema::create('factura_venta_certificacion', function (Blueprint $table) {
            $table->id();

            $table->foreignId('factura_venta_id')
                ->constrained('facturas_venta')
                ->cascadeOnDelete();

            $table->foreignId('certificacion_id')
                ->constrained('certificaciones')
                ->cascadeOnDelete();

            // Importes congelados por certificación (MUY IMPORTANTE)
            $table->decimal('base_imponible', 12, 2);
            $table->decimal('iva_importe', 12, 2);
            $table->decimal('retencion_importe', 12, 2);
            $table->decimal('total', 12, 2);

            $table->timestamps();

            $table->unique(
                ['factura_venta_id', 'certificacion_id'],
                'factura_certificacion_unique'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('factura_venta_certificacion');
    }
};
