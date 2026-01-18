<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('factura_series', function (Blueprint $table) {
            $table->id();

            /**
             * SERIE FISCAL
             * Ej: F, FV, 2026, RECT
             */
            $table->string('serie', 10)->unique();

            /**
             * CONTADOR GLOBAL
             */
            $table->unsignedInteger('ultimo_numero')->default(0);

            /**
             * CONTROL
             */
            $table->boolean('activa')->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('factura_series');
    }
};
