<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        /**
         * 1️⃣ AMPLIAR ENUM (permitir antiguos + nuevos)
         */
        DB::statement("
            ALTER TABLE facturas_recibidas 
            MODIFY estado ENUM(
                'pendiente_de_emision',
                'pendiente_de_vencimiento',
                'aplazada',
                'pagada',
                'impagada',
                'pendiente_emision_doc_pago',
                'pendiente_vencimiento',
                'devuelta'
            ) NOT NULL
        ");

        /**
         * 2️⃣ NORMALIZAR DATOS
         */
        DB::statement("
            UPDATE facturas_recibidas
            SET estado = 'pendiente_emision_doc_pago'
            WHERE estado = 'pendiente_de_emision'
        ");

        DB::statement("
            UPDATE facturas_recibidas
            SET estado = 'pendiente_vencimiento'
            WHERE estado = 'pendiente_de_vencimiento'
        ");

        DB::statement("
            UPDATE facturas_recibidas
            SET estado = 'devuelta'
            WHERE estado = 'aplazada'
        ");

        /**
         * 3️⃣ DEJAR ENUM FINAL LIMPIO
         */
        DB::statement("
            ALTER TABLE facturas_recibidas 
            MODIFY estado ENUM(
                'pendiente_emision_doc_pago',
                'pendiente_vencimiento',
                'devuelta',
                'pagada',
                'impagada'
            ) NOT NULL DEFAULT 'pendiente_vencimiento'
        ");
    }

    public function down(): void
    {
        DB::statement("
            ALTER TABLE facturas_recibidas 
            MODIFY estado ENUM(
                'pendiente_de_emision',
                'pendiente_de_vencimiento',
                'aplazada',
                'pagada',
                'impagada'
            ) NOT NULL DEFAULT 'pendiente_de_vencimiento'
        ");
    }
};
