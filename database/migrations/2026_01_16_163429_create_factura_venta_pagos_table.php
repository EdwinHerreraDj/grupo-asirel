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
        Schema::create('factura_venta_pagos', function (Blueprint $table) {
            $table->id();

            $table->foreignId('factura_venta_id')
                ->constrained('facturas_venta')
                ->cascadeOnDelete();

            $table->date('fecha_pago');
            $table->decimal('importe', 14, 2);
            $table->string('metodo', 50); 
            $table->string('tipo', 20)->default('normal');
            $table->text('observaciones')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('factura_venta_pagos');
    }
};
