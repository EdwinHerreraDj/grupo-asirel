<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Estado informativo de cobro/seguimiento para facturas de venta.
 * Campo SEPARADO del `estado` operativo/fiscal. Incluye sello ligero de
 * auditoría (quién/cuándo cambió) sin tabla de historial aparte.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('facturas_venta', function (Blueprint $table) {
            if (! Schema::hasColumn('facturas_venta', 'estado_cobro')) {
                $table->string('estado_cobro', 20)->default('pendiente')->after('estado');
            }
            if (! Schema::hasColumn('facturas_venta', 'estado_cobro_actualizado_at')) {
                $table->timestamp('estado_cobro_actualizado_at')->nullable()->after('estado_cobro');
            }
            if (! Schema::hasColumn('facturas_venta', 'estado_cobro_actualizado_por')) {
                $table->foreignId('estado_cobro_actualizado_por')
                    ->nullable()
                    ->after('estado_cobro_actualizado_at')
                    ->constrained('users')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('facturas_venta', function (Blueprint $table) {
            if (Schema::hasColumn('facturas_venta', 'estado_cobro_actualizado_por')) {
                $table->dropConstrainedForeignId('estado_cobro_actualizado_por');
            }
            foreach (['estado_cobro_actualizado_at', 'estado_cobro'] as $col) {
                if (Schema::hasColumn('facturas_venta', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
