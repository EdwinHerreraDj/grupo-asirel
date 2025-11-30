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
        Schema::create('facturas_recibidas', function (Blueprint $table) {
            $table->id();

            // RELACIONES
            $table->foreignId('obra_id')->constrained('obras')->onDelete('cascade');
            $table->foreignId('proveedor_id')->nullable()->constrained('proveedores')->nullOnDelete();
            $table->foreignId('oficio_id')->constrained('obra_gasto_categorias')->onDelete('cascade');

            // COSTE: material o mano de obra
            $table->enum('tipo_coste', ['material', 'mano_obra'])->default('material');

            // FACTURA
            $table->string('numero_factura')->nullable();
            $table->string('concepto')->nullable();
            $table->decimal('importe', 12, 2)->default(0);

            // FECHAS
            $table->date('fecha_factura');
            $table->date('fecha_contable')->nullable();
            $table->date('vencimiento')->nullable();

            // TIPO DE PAGO
            $table->enum('tipo_pago', [
                'transferencia',
                'pronto_pago',
                'confirming',
                'pagare',
                'contado'
            ])->nullable();

            // ESTADOS (ASIREL)
            $table->enum('estado', [
                'pendiente_de_vencimiento',
                'pagada',
                'pendiente_de_emision',
                'aplazada',
                'impagada'
            ])->default('pendiente_de_vencimiento');

            // ARCHIVO
            $table->string('adjunto')->nullable(); 

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('facturas_recibidas');
    }
};
