<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('obra_gastos_iniciales', function (Blueprint $table) {

            // 1. Primero eliminar la foreign key SI existe
            if (Schema::hasColumn('obra_gastos_iniciales', 'gasto_base_id')) {

                // nombre de la FK: obra_gastos_iniciales_gasto_base_id_foreign
                $table->dropForeign('obra_gastos_iniciales_gasto_base_id_foreign');

                // 2. Luego eliminar la columna
                $table->dropColumn('gasto_base_id');
            }

            // 3. Añadir nueva columna
            if (!Schema::hasColumn('obra_gastos_iniciales', 'obra_gasto_categoria_id')) {
                $table->foreignId('obra_gasto_categoria_id')
                    ->after('obra_id')
                    ->constrained('obra_gasto_categorias')
                    ->onDelete('cascade');
            }
        });
    }

    public function down()
    {
        Schema::table('obra_gastos_iniciales', function (Blueprint $table) {

            // Revertir el cambio (opcional)
            if (Schema::hasColumn('obra_gastos_iniciales', 'obra_gasto_categoria_id')) {
                $table->dropForeign(['obra_gasto_categoria_id']);
                $table->dropColumn('obra_gasto_categoria_id');
            }

            // Restaurar columna antigua si quieres
            $table->foreignId('gasto_base_id')
                ->nullable()
                ->constrained('gastos_base')
                ->onDelete('cascade');
        });
    }
};
