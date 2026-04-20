<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('documentos', function (Blueprint $table) {
            $table->foreignId('documento_tipo_id')
                ->nullable()
                ->after('obra_id')
                ->constrained('documento_tipos')
                ->nullOnDelete();

            $table->string('nombre_original')->nullable()->after('archivo');
            $table->string('mime_type', 100)->nullable()->after('nombre_original');
            $table->unsignedBigInteger('size')->nullable()->after('mime_type');
            $table->date('fecha_vencimiento')->nullable()->after('size');
        });

        // Backfill: cada documento existente recibe el FK al tipo correspondiente.
        // Si el tipo no existe en el cat\u00e1logo (custom), se crea.
        $documentos = DB::table('documentos')->whereNotNull('tipo')->get(['id', 'tipo']);

        foreach ($documentos as $doc) {
            $tipoId = DB::table('documento_tipos')->where('nombre', $doc->tipo)->value('id');

            if (! $tipoId) {
                $tipoId = DB::table('documento_tipos')->insertGetId([
                    'nombre' => $doc->tipo,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            DB::table('documentos')->where('id', $doc->id)->update([
                'documento_tipo_id' => $tipoId,
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('documentos', function (Blueprint $table) {
            $table->dropForeign(['documento_tipo_id']);
            $table->dropColumn([
                'documento_tipo_id',
                'nombre_original',
                'mime_type',
                'size',
                'fecha_vencimiento',
            ]);
        });
    }
};
