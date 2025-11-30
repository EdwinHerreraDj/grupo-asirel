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
        Schema::create('gastos_generales_empresa', function (Blueprint $table) {
            $table->id();

            // Información base del gasto
            $table->string('concepto');
            $table->decimal('importe', 10, 2);

            // Fechas importantes
            $table->date('fecha_factura');                
            $table->date('fecha_contable')->nullable();
            $table->date('fecha_vencimiento')->nullable();

            // Identificación de documentación
            $table->string('numero_factura', 150)->nullable();
            $table->string('factura_url')->nullable();

            // Relación categoría principal
            $table->foreignId('categoria_id')
                ->constrained('categorias_gastos_empresa')
                ->onDelete('restrict');

            // Campo adicional de especificación
            $table->string('especificacion')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gastos_generales_empresa');
    }
};
