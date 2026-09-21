<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Recursos humanos (fase 2): ausencias (vacaciones, bajas médicas, permisos…),
 * tipos de ausencia configurables, calendario de festivos y días de
 * vacaciones por empleado. Aditiva: no modifica datos existentes.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('rrhh_tipos_ausencia')) {
            Schema::create('rrhh_tipos_ausencia', function (Blueprint $table) {
                $table->id();
                $table->string('nombre', 100)->unique();
                $table->string('color', 20)->default('cyan');
                $table->boolean('es_vacaciones')->default(false);
                $table->boolean('es_baja_medica')->default(false);
                $table->boolean('retribuida')->default(true);
                $table->boolean('requiere_justificante')->default(false);
                $table->decimal('dias_anuales', 5, 1)->nullable(); // con valor: tiene saldo anual
                $table->string('computo', 12)->default('naturales'); // naturales | laborables
                $table->unsignedInteger('orden')->default(0);
                $table->boolean('activo')->default(true);
                $table->timestamps();
            });

            $ahora = now();
            $tipos = [
                // nombre, color, vacaciones, baja médica, retribuida, justificante, días/año, cómputo
                ['Vacaciones', 'cyan', true, false, true, false, 30, 'naturales'],
                ['Baja por enfermedad', 'rose', false, true, true, true, null, 'naturales'],
                ['Accidente de trabajo', 'red', false, true, true, true, null, 'naturales'],
                ['Asuntos propios', 'violet', false, false, true, false, null, 'laborables'],
                ['Permiso retribuido', 'emerald', false, false, true, true, null, 'naturales'],
                ['Nacimiento y cuidado de menor', 'pink', false, false, true, true, null, 'naturales'],
                ['Permiso no retribuido', 'slate', false, false, false, false, null, 'naturales'],
                ['Ausencia injustificada', 'amber', false, false, false, false, null, 'laborables'],
            ];

            DB::table('rrhh_tipos_ausencia')->insert(array_map(fn ($t, $i) => [
                'nombre' => $t[0],
                'color' => $t[1],
                'es_vacaciones' => $t[2],
                'es_baja_medica' => $t[3],
                'retribuida' => $t[4],
                'requiere_justificante' => $t[5],
                'dias_anuales' => $t[6],
                'computo' => $t[7],
                'orden' => $i + 1,
                'activo' => true,
                'created_at' => $ahora,
                'updated_at' => $ahora,
            ], $tipos, array_keys($tipos)));
        }

        if (! Schema::hasTable('rrhh_ausencias')) {
            Schema::create('rrhh_ausencias', function (Blueprint $table) {
                $table->id();
                $table->foreignId('empleado_id')->constrained('empleados')->cascadeOnDelete();
                $table->foreignId('rrhh_tipo_ausencia_id')->constrained('rrhh_tipos_ausencia')->restrictOnDelete();
                $table->date('fecha_inicio');
                $table->date('fecha_fin')->nullable(); // null = baja médica abierta
                $table->text('observaciones')->nullable();
                $table->foreignId('file_id')->nullable()->constrained('files')->nullOnDelete(); // justificante en el Drive
                $table->foreignId('usuario_id')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();

                $table->index(['empleado_id', 'fecha_inicio']);
                $table->index(['fecha_inicio', 'fecha_fin']);
            });
        }

        if (! Schema::hasTable('rrhh_festivos')) {
            Schema::create('rrhh_festivos', function (Blueprint $table) {
                $table->id();
                $table->date('fecha')->unique();
                $table->string('nombre', 150);
                $table->timestamps();
            });
        }

        if (! Schema::hasColumn('empleados', 'dias_vacaciones_anuales')) {
            Schema::table('empleados', function (Blueprint $table) {
                // null = los del tipo "Vacaciones"
                $table->decimal('dias_vacaciones_anuales', 5, 1)->nullable()->after('salario_bruto_anual');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('empleados', 'dias_vacaciones_anuales')) {
            Schema::table('empleados', fn (Blueprint $table) => $table->dropColumn('dias_vacaciones_anuales'));
        }

        Schema::dropIfExists('rrhh_festivos');
        Schema::dropIfExists('rrhh_ausencias');
        Schema::dropIfExists('rrhh_tipos_ausencia');
    }
};
