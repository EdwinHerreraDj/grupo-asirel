<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('facturas_recibidas', function (Blueprint $table) {

            if (!Schema::hasColumn('facturas_recibidas', 'base_imponible')) {

                $table->decimal('base_imponible', 12, 2)
                    ->default(0)
                    ->after('importe');

                $table->decimal('iva_porcentaje', 5, 2)
                    ->default(21)
                    ->after('base_imponible');

                $table->decimal('iva_importe', 12, 2)
                    ->default(0)
                    ->after('iva_porcentaje');

                $table->decimal('retencion_porcentaje', 5, 2)
                    ->default(0)
                    ->after('iva_importe');

                $table->decimal('retencion_importe', 12, 2)
                    ->default(0)
                    ->after('retencion_porcentaje');

                $table->decimal('total', 12, 2)
                    ->default(0)
                    ->after('retencion_importe');
            }
        });

        // Migrar datos existentes (seguro)
        DB::statement("
            UPDATE facturas_recibidas
            SET 
                base_imponible = importe,
                iva_importe = (importe * iva_porcentaje) / 100,
                retencion_importe = (importe * retencion_porcentaje) / 100,
                total = importe 
                        + ((importe * iva_porcentaje) / 100)
                        - ((importe * retencion_porcentaje) / 100)
        ");
    }

    public function down(): void
    {
        Schema::table('facturas_recibidas', function (Blueprint $table) {
            $table->dropColumn([
                'base_imponible',
                'iva_porcentaje',
                'iva_importe',
                'retencion_porcentaje',
                'retencion_importe',
                'total',
            ]);
        });
    }
};
