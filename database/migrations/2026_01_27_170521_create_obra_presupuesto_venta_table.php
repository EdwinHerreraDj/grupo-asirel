<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('obra_presupuestos_venta', function (Blueprint $table) {

            $table->id();

            // Relación con la obra
            $table->foreignId('obra_id')
                ->constrained('obras')
                ->cascadeOnDelete();

            // Relación con el oficio (categoría de gasto)
            $table->foreignId('obra_gasto_categoria_id')
                ->constrained('obra_gasto_categorias')
                ->cascadeOnDelete();

            // Presupuesto de venta
            $table->string('unidad', 50)->nullable();           // m2, ml, ud, h…
            $table->decimal('cantidad', 12, 3)->nullable();     // cantidad contratada
            $table->decimal('precio_unitario', 12, 4)->nullable();
            $table->text('observaciones')->nullable();

            // Total de venta (opcional persistirlo)
            $table->decimal('importe_total', 14, 2)->nullable();

            $table->timestamps();

            // Garantiza 1 presupuesto por obra + oficio
            $table->unique([
                'obra_id',
                'obra_gasto_categoria_id'
            ], 'obra_oficio_presupuesto_venta_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('obra_presupuestos_venta');
    }
};
