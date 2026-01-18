<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('facturas_venta', function (Blueprint $table) {
            $table->string('origen', 20)
                ->default('manual')
                ->after('serie');
        });
    }

    public function down(): void
    {
        Schema::table('facturas_venta', function (Blueprint $table) {
            $table->dropColumn('origen');
        });
    }
};
