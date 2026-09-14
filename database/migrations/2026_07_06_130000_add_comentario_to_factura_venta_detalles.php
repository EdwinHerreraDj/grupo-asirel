<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Comentario opcional por línea de factura de venta. Se usa al facturar desde
 * certificaciones en el modo "líneas + comentarios", para arrastrar el
 * comentario que el usuario puso en cada línea de la certificación.
 * Aditiva y nullable: no afecta a facturas existentes.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('factura_venta_detalles', function (Blueprint $table) {
            if (! Schema::hasColumn('factura_venta_detalles', 'comentario')) {
                $table->string('comentario', 1000)->nullable()->after('importe_linea');
            }
        });
    }

    public function down(): void
    {
        Schema::table('factura_venta_detalles', function (Blueprint $table) {
            if (Schema::hasColumn('factura_venta_detalles', 'comentario')) {
                $table->dropColumn('comentario');
            }
        });
    }
};
