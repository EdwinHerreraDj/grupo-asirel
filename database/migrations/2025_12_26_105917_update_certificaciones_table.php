<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('certificaciones', function (Blueprint $table) {

            // 🔹 ELIMINAR LO OBSOLETO
            if (Schema::hasColumn('certificaciones', 'especificacion')) {
                $table->dropColumn('especificacion');
            }

            // 🔹 RELACIÓN CLIENTE
            if (!Schema::hasColumn('certificaciones', 'cliente_id')) {
                $table->foreignId('cliente_id')
                    ->nullable()
                    ->constrained()
                    ->after('obra_id');
            }

            // 🔹 FECHA CERTIFICACIÓN (OBLIGATORIA A NIVEL APP, NULLABLE EN BD)
            if (!Schema::hasColumn('certificaciones', 'fecha_certificacion')) {
                $table->date('fecha_certificacion')
                    ->nullable()
                    ->after('cliente_id');
            }

            // 🔹 IMPORTES BASE
            if (!Schema::hasColumn('certificaciones', 'base_imponible')) {
                $table->decimal('base_imponible', 12, 2)->default(0);
            }

            if (!Schema::hasColumn('certificaciones', 'iva_porcentaje')) {
                $table->decimal('iva_porcentaje', 5, 2)->default(21);
            }

            if (!Schema::hasColumn('certificaciones', 'iva_importe')) {
                $table->decimal('iva_importe', 12, 2)->default(0);
            }

            if (!Schema::hasColumn('certificaciones', 'retencion_porcentaje')) {
                $table->decimal('retencion_porcentaje', 5, 2)->default(0);
            }

            if (!Schema::hasColumn('certificaciones', 'retencion_importe')) {
                $table->decimal('retencion_importe', 12, 2)->default(0);
            }

            if (!Schema::hasColumn('certificaciones', 'total')) {
                $table->decimal('total', 12, 2)->default(0);
            }

            // 🔹 ESTADOS
            if (!Schema::hasColumn('certificaciones', 'estado_certificacion')) {
                $table->enum('estado_certificacion', ['enviada', 'aceptada', 'facturada'])
                    ->default('enviada');
            }

            if (!Schema::hasColumn('certificaciones', 'estado_factura')) {
                $table->enum('estado_factura', [
                    'enviada',
                    'pte_recepcion_doc_cobro',
                    'pte_vencimiento',
                    'devuelta',
                    'cobrada',
                    'impagada'
                ])->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('certificaciones', function (Blueprint $table) {

            $table->dropConstrainedForeignId('cliente_id');

            $table->dropColumn([
                'fecha_certificacion',
                'fecha_contable',

                'base_imponible',
                'iva_porcentaje',
                'iva_importe',
                'retencion_porcentaje',
                'retencion_importe',
                'total',

                'estado_certificacion',
                'estado_factura',
            ]);
        });
    }
};
