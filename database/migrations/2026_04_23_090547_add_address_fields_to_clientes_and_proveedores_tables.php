<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clientes', function (Blueprint $table) {
            $table->string('codigo_postal', 20)->nullable()->after('direccion');
            $table->string('poblacion', 150)->nullable()->after('codigo_postal');
            $table->string('provincia', 150)->nullable()->after('poblacion');
            $table->string('pais', 100)->nullable()->after('provincia');
        });

        Schema::table('proveedores', function (Blueprint $table) {
            $table->string('codigo_postal', 20)->nullable()->after('direccion');
            $table->string('poblacion', 150)->nullable()->after('codigo_postal');
            $table->string('provincia', 150)->nullable()->after('poblacion');
            $table->string('pais', 100)->nullable()->after('provincia');
        });
    }

    public function down(): void
    {
        Schema::table('clientes', function (Blueprint $table) {
            $table->dropColumn(['codigo_postal', 'poblacion', 'provincia', 'pais']);
        });

        Schema::table('proveedores', function (Blueprint $table) {
            $table->dropColumn(['codigo_postal', 'poblacion', 'provincia', 'pais']);
        });
    }
};
