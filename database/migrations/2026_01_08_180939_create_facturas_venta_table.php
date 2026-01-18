<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('facturas_venta', function (Blueprint $table) {
            $table->id();

            /**
             * NUMERACIÓN FISCAL (GENERADA DESDE factura_series)
             * Ej: F-2026-000001
             */
            $table->string('serie', 10)->index();
            $table->string('numero_factura', 30)->unique();

            /**
             * FECHAS
             */
            $table->date('fecha_emision');
            $table->date('fecha_contable')->nullable();
            $table->date('vencimiento')->nullable();

            /**
             * RELACIONES (OPCIONALES)
             */
            $table->foreignId('obra_id')
                ->nullable()
                ->constrained('obras')
                ->nullOnDelete();

            $table->foreignId('cliente_id')
                ->nullable()
                ->constrained('clientes')
                ->nullOnDelete();

            /**
             * AGRUPADOR DE CERTIFICACIONES
             * Permite facturar varias certificaciones con el mismo código
             */
            $table->string('codigo_certificacion', 50)
                ->nullable()
                ->index();

            /**
             * IMPORTES CONGELADOS (FISCALES)
             */
            $table->decimal('base_imponible', 12, 2)->default(0);

            $table->decimal('iva_porcentaje', 5, 2)->default(21);
            $table->decimal('iva_importe', 12, 2)->default(0);

            $table->decimal('retencion_porcentaje', 5, 2)->default(0);
            $table->decimal('retencion_importe', 12, 2)->default(0);

            $table->decimal('total', 12, 2)->default(0);

            /**
             * ESTADO (STRING, FLEXIBLE)
             * borrador | emitida | enviada | pagada | anulada
             */
            $table->string('estado', 30)
                ->default('borrador')
                ->index();

            /**
             * PDF / DOCUMENTO
             */
            $table->string('pdf_url')->nullable();

            /**
             * OBSERVACIONES
             */
            $table->text('observaciones')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('facturas_venta');
    }
};
