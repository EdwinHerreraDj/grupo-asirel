<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Configuraci\u00f3n de personalizaci\u00f3n de PDFs almacenada junto a los datos de empresa.
 * Defaults en escala de grises para un aspecto profesional neutro.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('empresa', function (Blueprint $table) {
            $table->string('color_primario', 7)->default('#111827')->after('descripcion');
            $table->string('color_secundario', 7)->default('#d1d5db')->after('color_primario');
            $table->boolean('mostrar_logo_pdf')->default(true)->after('color_secundario');
            $table->text('pie_pdf')->nullable()->after('mostrar_logo_pdf');
        });
    }

    public function down(): void
    {
        Schema::table('empresa', function (Blueprint $table) {
            $table->dropColumn(['color_primario', 'color_secundario', 'mostrar_logo_pdf', 'pie_pdf']);
        });
    }
};
