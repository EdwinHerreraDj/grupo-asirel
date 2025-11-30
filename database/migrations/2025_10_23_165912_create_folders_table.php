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
        Schema::create('folders', function (Blueprint $table) {
            $table->id();
            // 0 = carpeta en raíz; cualquier otro valor = id de la carpeta padre
            $table->unsignedBigInteger('parent_id')->default(0)->index();
            $table->unsignedBigInteger('usuario_id')->nullable()->index(); // quién la creó (opcional)
            $table->string('nombre', 150);
            $table->tinyInteger('tipo')->default(1); // 1=padre (raíz), 2=subcarpeta (convención actual)
            $table->timestamps();

            // Un nombre no puede repetirse dentro del mismo nivel
            $table->unique(['parent_id', 'nombre']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('folders');
    }
};
