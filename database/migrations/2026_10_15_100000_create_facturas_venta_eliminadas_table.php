<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Rastro de las facturas de venta eliminadas. Borrar una factura emitida es
 * excepcional (equivocaciones haciendo pruebas), así que queda constancia de
 * qué se borró, quién lo hizo y por qué, aunque la factura ya no exista.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('facturas_venta_eliminadas')) {
            return;
        }

        Schema::create('facturas_venta_eliminadas', function (Blueprint $table) {
            $table->id();

            // Identificación de la factura borrada
            $table->unsignedBigInteger('factura_id');
            $table->string('serie', 20)->nullable();
            $table->unsignedInteger('numero_factura')->nullable();
            $table->string('numero_formateado', 50)->nullable();
            $table->string('estado', 20)->nullable();
            $table->string('origen', 30)->nullable();
            $table->date('fecha_emision')->nullable();

            // A quién y a qué iba
            $table->unsignedBigInteger('cliente_id')->nullable();
            $table->string('cliente_nombre')->nullable();
            $table->unsignedBigInteger('obra_id')->nullable();
            $table->string('obra_nombre')->nullable();
            $table->string('codigo_certificacion', 50)->nullable();

            // Importes
            $table->decimal('base_imponible', 12, 2)->default(0);
            $table->decimal('iva_importe', 12, 2)->default(0);
            $table->decimal('retencion_importe', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);

            // Quién y por qué
            $table->foreignId('usuario_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('usuario_nombre')->nullable();
            $table->string('ip', 45)->nullable();
            $table->text('motivo');
            $table->boolean('numero_liberado')->default(false);
            $table->json('datos')->nullable(); // copia completa por si hace falta

            $table->timestamps();

            $table->index(['serie', 'numero_factura']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('facturas_venta_eliminadas');
    }
};
