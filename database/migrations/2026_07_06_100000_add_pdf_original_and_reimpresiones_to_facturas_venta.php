<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * VeriFactu: separa el PDF ORIGINAL emitido (inmutable) de las COPIAS /
 * reimpresiones posteriores.
 *
 *  - `facturas_venta.pdf_url` sigue siendo la ruta del PDF ORIGINAL histórico.
 *  - `pdf_original_generado_at` documenta cuándo se congeló ese original.
 *  - `factura_venta_reimpresiones` registra cada copia/reimpresión generada
 *    después (trazabilidad: quién, cuándo, tipo). Las copias NO se almacenan
 *    como archivo: se generan al vuelo con el branding actual.
 *
 * Cambios puramente aditivos: no toca datos existentes.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('facturas_venta', function (Blueprint $table) {
            if (! Schema::hasColumn('facturas_venta', 'pdf_original_generado_at')) {
                $table->timestamp('pdf_original_generado_at')->nullable()->after('pdf_url');
            }
        });

        // Backfill: las facturas ya emitidas con PDF existente pasan a tener su
        // original marcado como congelado (protegido frente a sobrescritura).
        \Illuminate\Support\Facades\DB::table('facturas_venta')
            ->whereNotNull('pdf_url')
            ->where('estado', '!=', 'borrador')
            ->whereNull('pdf_original_generado_at')
            ->update([
                'pdf_original_generado_at' => \Illuminate\Support\Facades\DB::raw('COALESCE(fecha_emision, updated_at)'),
            ]);

        if (! Schema::hasTable('factura_venta_reimpresiones')) {
            Schema::create('factura_venta_reimpresiones', function (Blueprint $table) {
                $table->id();

                $table->foreignId('factura_venta_id')
                    ->constrained('facturas_venta')
                    ->cascadeOnDelete();

                $table->foreignId('user_id')
                    ->nullable()
                    ->constrained('users')
                    ->nullOnDelete();

                // 'reimpresion' (copia visual posterior). Reservado por si en el
                // futuro hay otros tipos (p. ej. 'reenvio').
                $table->string('tipo', 30)->default('reimpresion');

                $table->timestamps();

                $table->index(['factura_venta_id', 'created_at']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('factura_venta_reimpresiones');

        Schema::table('facturas_venta', function (Blueprint $table) {
            if (Schema::hasColumn('facturas_venta', 'pdf_original_generado_at')) {
                $table->dropColumn('pdf_original_generado_at');
            }
        });
    }
};
