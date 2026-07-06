<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('certificacion_detalles', function (Blueprint $table) {
            if (! Schema::hasColumn('certificacion_detalles', 'comentario')) {
                $table->string('comentario', 1000)->nullable()->after('importe_linea');
            }
        });
    }

    public function down(): void
    {
        Schema::table('certificacion_detalles', function (Blueprint $table) {
            if (Schema::hasColumn('certificacion_detalles', 'comentario')) {
                $table->dropColumn('comentario');
            }
        });
    }
};
