<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Documentación adjunta relacionada con una factura de venta (albaranes,
 * partes de trabajo, certificaciones firmadas, justificantes, fotos, etc.).
 *
 * NO forma parte del documento fiscal: no afecta a importes, fechas,
 * numeración, estado ni al PDF original emitido. Es solo soporte documental
 * del expediente de la factura.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('factura_venta_documentos')) {
            return;
        }

        Schema::create('factura_venta_documentos', function (Blueprint $table) {
            $table->id();

            $table->foreignId('factura_venta_id')
                ->constrained('facturas_venta')
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->string('nombre_original');   // nombre visible / de descarga
            $table->string('ruta');              // ruta en disco 'public'
            $table->string('mime', 150)->nullable();
            $table->unsignedBigInteger('tamano')->nullable(); // bytes

            $table->timestamps();

            $table->index(['factura_venta_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('factura_venta_documentos');
    }
};
