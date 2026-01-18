<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('certificaciones', function (Blueprint $table) {
            $table->string('estado_certificacion', 20)->change();
            $table->string('estado_factura', 20)->change();
        });
    }

    public function down(): void
    {
        Schema::table('certificaciones', function (Blueprint $table) {
            // si antes era ENUM y quieres revertirlo:
            // $table->enum('estado_certificacion', ['enviada','aceptada','facturada'])->change();
            // $table->enum('estado_factura', ['pendiente','facturada'])->change();
        });
    }
};
