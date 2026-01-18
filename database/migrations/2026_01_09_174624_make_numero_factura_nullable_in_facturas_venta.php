<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('facturas_venta', function (Blueprint $table) {
            $table->string('numero_factura')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('facturas_venta', function (Blueprint $table) {
            $table->string('numero_factura')->nullable(false)->change();
        });
    }
};
