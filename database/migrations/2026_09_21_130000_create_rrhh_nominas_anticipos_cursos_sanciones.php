<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Recursos humanos (fase 3): nóminas (registro de las que hace la gestoría),
 * anticipos y vales, cursos y formación, y sanciones. Aditiva.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('rrhh_nominas')) {
            Schema::create('rrhh_nominas', function (Blueprint $table) {
                $table->id();
                $table->foreignId('empleado_id')->constrained('empleados')->cascadeOnDelete();
                $table->unsignedSmallInteger('anio');
                $table->unsignedTinyInteger('mes');
                $table->string('tipo', 20)->default('mensual'); // mensual | extra | finiquito | atrasos
                $table->decimal('bruto', 10, 2);
                $table->decimal('irpf', 10, 2)->default(0);
                $table->decimal('seguridad_social', 10, 2)->default(0);
                $table->decimal('anticipos', 10, 2)->default(0); // suma de los anticipos descontados
                $table->decimal('otras_deducciones', 10, 2)->default(0);
                $table->decimal('neto', 10, 2);
                $table->decimal('coste_empresa', 10, 2)->nullable();
                $table->string('estado', 12)->default('pendiente'); // pendiente | pagada
                $table->date('fecha_pago')->nullable();
                $table->foreignId('file_id')->nullable()->constrained('files')->nullOnDelete();
                $table->text('observaciones')->nullable();
                $table->foreignId('usuario_id')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();

                $table->unique(['empleado_id', 'anio', 'mes', 'tipo']);
                $table->index(['anio', 'mes']);
            });
        }

        if (! Schema::hasTable('rrhh_anticipos')) {
            Schema::create('rrhh_anticipos', function (Blueprint $table) {
                $table->id();
                $table->foreignId('empleado_id')->constrained('empleados')->cascadeOnDelete();
                $table->string('tipo', 12)->default('anticipo'); // anticipo | vale
                $table->date('fecha');
                $table->decimal('importe', 10, 2);
                $table->string('concepto')->nullable();
                $table->string('forma_pago', 20)->nullable(); // efectivo | transferencia | otro
                // null = pendiente de descontar; con valor = descontado en esa nómina
                $table->foreignId('nomina_id')->nullable()->constrained('rrhh_nominas')->nullOnDelete();
                $table->foreignId('file_id')->nullable()->constrained('files')->nullOnDelete();
                $table->text('observaciones')->nullable();
                $table->foreignId('usuario_id')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();

                $table->index(['empleado_id', 'nomina_id']);
            });
        }

        if (! Schema::hasTable('rrhh_cursos')) {
            Schema::create('rrhh_cursos', function (Blueprint $table) {
                $table->id();
                $table->foreignId('empleado_id')->constrained('empleados')->cascadeOnDelete();
                $table->string('nombre', 200);
                $table->string('categoria', 20)->default('otro'); // prl | oficio | carnet | otro
                $table->string('entidad', 150)->nullable();
                $table->decimal('horas', 6, 1)->nullable();
                $table->date('fecha');
                $table->date('fecha_caducidad')->nullable();
                $table->foreignId('file_id')->nullable()->constrained('files')->nullOnDelete();
                $table->text('observaciones')->nullable();
                $table->foreignId('usuario_id')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();

                $table->index(['empleado_id', 'fecha']);
                $table->index('fecha_caducidad');
            });
        }

        if (! Schema::hasTable('rrhh_sanciones')) {
            Schema::create('rrhh_sanciones', function (Blueprint $table) {
                $table->id();
                $table->foreignId('empleado_id')->constrained('empleados')->cascadeOnDelete();
                $table->date('fecha_hechos');
                $table->date('fecha_comunicacion')->nullable();
                $table->string('gravedad', 12); // leve | grave | muy_grave
                $table->string('tipo', 30); // amonestacion_verbal | amonestacion_escrita | suspension | despido | otra
                $table->unsignedSmallInteger('dias_suspension')->nullable();
                $table->date('fecha_inicio_suspension')->nullable();
                $table->text('descripcion');
                $table->string('estado', 12)->default('vigente'); // vigente | anulada
                $table->foreignId('file_id')->nullable()->constrained('files')->nullOnDelete();
                $table->foreignId('usuario_id')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();

                $table->index(['empleado_id', 'fecha_hechos']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('rrhh_sanciones');
        Schema::dropIfExists('rrhh_cursos');
        Schema::dropIfExists('rrhh_anticipos');
        Schema::dropIfExists('rrhh_nominas');
    }
};
