<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Campo para adjuntar una proforma / documento externo a la factura.
 * Nullable porque es opcional.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('facturas_venta', function (Blueprint $table) {
            $table->string('adjunto')->nullable()->after('pdf_url');
        });
    }

    public function down(): void
    {
        Schema::table('facturas_venta', function (Blueprint $table) {
            $table->dropColumn('adjunto');
        });
    }
};
