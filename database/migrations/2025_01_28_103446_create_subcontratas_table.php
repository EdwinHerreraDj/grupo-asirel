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
        Schema::create('subcontratas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('obra_id')->constrained('obras')->onDelete('cascade');
            $table->string('nombre');
            $table->text('descripcion')->nullable();
            $table->date('fecha');
            $table->decimal('importe', 10, 2);
            $table->string('archivo_factura')->nullable();
            $table->string('archivo_contrato')->nullable();
            $table->timestamps();

            // Relación con la tabla 'obras'
            $table->foreign('obra_id')->references('id')->on('obras')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subcontratas');
    }
};
