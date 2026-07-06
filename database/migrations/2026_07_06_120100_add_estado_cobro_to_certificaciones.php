<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Estado informativo de cobro/seguimiento para certificaciones.
 * Campo SEPARADO de `estado_certificacion` y `estado_factura`. El historial de
 * cambios se registra en `certificacion_eventos` (infra ya existente).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('certificaciones', function (Blueprint $table) {
            if (! Schema::hasColumn('certificaciones', 'estado_cobro')) {
                $table->string('estado_cobro', 20)->default('pendiente')->after('estado_factura');
            }
        });
    }

    public function down(): void
    {
        Schema::table('certificaciones', function (Blueprint $table) {
            if (Schema::hasColumn('certificaciones', 'estado_cobro')) {
                $table->dropColumn('estado_cobro');
            }
        });
    }
};
