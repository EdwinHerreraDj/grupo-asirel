<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Un empleado puede trabajar en varias obras a la vez: empleados.obra_id pasa
 * a la tabla pivote empleado_obra (se copian las asignaciones existentes).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('empleado_obra')) {
            Schema::create('empleado_obra', function (Blueprint $table) {
                $table->id();
                $table->foreignId('empleado_id')->constrained('empleados')->cascadeOnDelete();
                $table->foreignId('obra_id')->constrained('obras')->cascadeOnDelete();
                $table->timestamps();

                $table->unique(['empleado_id', 'obra_id']);
            });
        }

        if (Schema::hasColumn('empleados', 'obra_id')) {
            $ahora = now();

            DB::table('empleados')->whereNotNull('obra_id')->orderBy('id')->get(['id', 'obra_id'])
                ->each(fn ($e) => DB::table('empleado_obra')->insertOrIgnore([
                    'empleado_id' => $e->id,
                    'obra_id' => $e->obra_id,
                    'created_at' => $ahora,
                    'updated_at' => $ahora,
                ]));

            Schema::table('empleados', function (Blueprint $table) {
                $table->dropConstrainedForeignId('obra_id');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasColumn('empleados', 'obra_id')) {
            Schema::table('empleados', function (Blueprint $table) {
                $table->foreignId('obra_id')->nullable()->after('contacto_emergencia_telefono')
                    ->constrained('obras')->nullOnDelete();
            });
        }

        if (Schema::hasTable('empleado_obra')) {
            DB::table('empleado_obra')
                ->selectRaw('empleado_id, MIN(obra_id) as obra_id')
                ->groupBy('empleado_id')
                ->get()
                ->each(fn ($f) => DB::table('empleados')->where('id', $f->empleado_id)->update(['obra_id' => $f->obra_id]));

            Schema::drop('empleado_obra');
        }
    }
};
