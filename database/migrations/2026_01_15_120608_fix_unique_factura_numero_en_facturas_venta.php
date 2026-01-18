<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('facturas_venta', function (Blueprint $table) {

            // ELIMINAR UNIQUE INCORRECTO
            $table->dropUnique('facturas_venta_numero_factura_unique');
            // usa aquí el nombre EXACTO que viste en SHOW INDEX

            // CREAR UNIQUE CORRECTO
            $table->unique(
                ['serie', 'numero_factura'],
                'facturas_venta_serie_numero_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::table('facturas_venta', function (Blueprint $table) {

            // revertir
            $table->dropUnique('facturas_venta_serie_numero_unique');

            $table->unique(
                ['numero_factura'],
                'facturas_venta_numero_factura_unique'
            );
        });
    }
};
