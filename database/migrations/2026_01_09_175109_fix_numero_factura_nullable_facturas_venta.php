<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        DB::statement("
            ALTER TABLE facturas_venta
            MODIFY numero_factura VARCHAR(30) NULL
        ");
    }

    public function down(): void
    {
        DB::statement("
            ALTER TABLE facturas_venta
            MODIFY numero_factura VARCHAR(30) NOT NULL
        ");
    }
};
