<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $tiposPorDefecto = [
        'ADJUDICACIÓN',
        'CONTRATO',
        'PLAN DE SEGURIDAD Y SALUD',
        'PLAN DE GESTIÓN DE RESIDUOS',
        'ACTA DE REPLANTEO',
        'ACTA DE RECEPCIÓN',
        'APROBACIÓN PLAN DE SEGURIDAD Y SALUD',
        'APROBACIÓN PLAN DE GESTIÓN DE RESIDUOS',
        'APERTURA CENTRO DE TRABAJO',
    ];

    public function up(): void
    {
        Schema::create('documento_tipos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre')->unique();
            $table->timestamps();
        });

        $ahora = now();
        $rows = array_map(fn ($n) => [
            'nombre' => $n,
            'created_at' => $ahora,
            'updated_at' => $ahora,
        ], $this->tiposPorDefecto);

        DB::table('documento_tipos')->insert($rows);
    }

    public function down(): void
    {
        Schema::dropIfExists('documento_tipos');
    }
};
