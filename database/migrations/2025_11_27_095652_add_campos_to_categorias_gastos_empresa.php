<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('categorias_gastos_empresa', function (Blueprint $table) {

            // Código contable (ej: 001, 002, 001.001)
            if (!Schema::hasColumn('categorias_gastos_empresa', 'codigo')) {
                $table->string('codigo', 20)->nullable()->after('id');
            }

            // Relación padre (NULL = categoría padre)
            if (!Schema::hasColumn('categorias_gastos_empresa', 'parent_id')) {
                $table->unsignedBigInteger('parent_id')->nullable()->after('descripcion');
            }

            // Nivel: 1 = padre, 2 = subcategoría
            if (!Schema::hasColumn('categorias_gastos_empresa', 'nivel')) {
                $table->integer('nivel')->default(1)->after('parent_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('categorias_gastos_empresa', function (Blueprint $table) {
            $table->dropColumn(['codigo', 'parent_id', 'nivel']);
        });
    }
};
