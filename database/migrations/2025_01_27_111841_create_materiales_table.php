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
        Schema::create('materiales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('obra_id')->constrained('obras')->onDelete('cascade'); // Relación con la tabla obras
            $table->string('nombre'); // Nombre del material (select principal)
            $table->string('descripcion')->nullable(); // Descripción adicional (cuando seleccionan "Otro")
            $table->decimal('precio_unitario', 10, 2); // Precio unitario del material
            $table->integer('cantidad'); // Cantidad utilizada
            $table->date('fecha'); // Fecha del registro del gasto
            $table->decimal('importe', 15, 2); // Importe total (cantidad x precio unitario)
            $table->string('archivo_factura')->nullable(); // Ruta del archivo (PDF o imagen)
            $table->timestamps(); // Campos created_at y updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('materiales');
    }
};
