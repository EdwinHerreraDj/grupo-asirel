<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Recursos humanos (fase 4): tipos de turno, cuadrante (turno y obra de cada
 * empleado cada día) y fecha de fin de contrato prevista en cada periodo de
 * alta (para avisar antes de que termine). Aditiva.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('rrhh_turnos')) {
            Schema::create('rrhh_turnos', function (Blueprint $table) {
                $table->id();
                $table->string('nombre', 100)->unique();
                $table->string('color', 20)->default('cyan');
                $table->time('hora_inicio');
                $table->time('hora_fin');
                $table->time('hora_inicio_2')->nullable(); // jornada partida
                $table->time('hora_fin_2')->nullable();
                $table->unsignedSmallInteger('descanso_minutos')->default(0);
                $table->unsignedInteger('orden')->default(0);
                $table->boolean('activo')->default(true);
                $table->timestamps();
            });

            $ahora = now();
            DB::table('rrhh_turnos')->insert([
                ['nombre' => 'Jornada partida', 'color' => 'cyan', 'hora_inicio' => '08:00', 'hora_fin' => '13:00',
                    'hora_inicio_2' => '14:00', 'hora_fin_2' => '17:00', 'descanso_minutos' => 0, 'orden' => 1,
                    'activo' => true, 'created_at' => $ahora, 'updated_at' => $ahora],
                ['nombre' => 'Mañana', 'color' => 'amber', 'hora_inicio' => '07:00', 'hora_fin' => '15:00',
                    'hora_inicio_2' => null, 'hora_fin_2' => null, 'descanso_minutos' => 0, 'orden' => 2,
                    'activo' => true, 'created_at' => $ahora, 'updated_at' => $ahora],
                ['nombre' => 'Tarde', 'color' => 'indigo', 'hora_inicio' => '14:00', 'hora_fin' => '22:00',
                    'hora_inicio_2' => null, 'hora_fin_2' => null, 'descanso_minutos' => 0, 'orden' => 3,
                    'activo' => true, 'created_at' => $ahora, 'updated_at' => $ahora],
            ]);
        }

        if (! Schema::hasTable('rrhh_cuadrante')) {
            Schema::create('rrhh_cuadrante', function (Blueprint $table) {
                $table->id();
                $table->foreignId('empleado_id')->constrained('empleados')->cascadeOnDelete();
                $table->date('fecha');
                $table->foreignId('rrhh_turno_id')->constrained('rrhh_turnos')->restrictOnDelete();
                $table->foreignId('obra_id')->nullable()->constrained('obras')->nullOnDelete();
                $table->string('observaciones', 255)->nullable();
                $table->foreignId('usuario_id')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();

                $table->unique(['empleado_id', 'fecha']);
                $table->index(['fecha', 'obra_id']);
            });
        }

        if (! Schema::hasColumn('empleado_periodos', 'fecha_fin_contrato')) {
            Schema::table('empleado_periodos', function (Blueprint $table) {
                $table->date('fecha_fin_contrato')->nullable()->after('tipo_contrato');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('empleado_periodos', 'fecha_fin_contrato')) {
            Schema::table('empleado_periodos', fn (Blueprint $table) => $table->dropColumn('fecha_fin_contrato'));
        }

        Schema::dropIfExists('rrhh_cuadrante');
        Schema::dropIfExists('rrhh_turnos');
    }
};
