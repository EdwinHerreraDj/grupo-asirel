<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Recursos humanos (fase 1): empleados, historial de altas/bajas y tipos de
 * documento configurables. La documentación se guarda en el Drive: cada
 * empleado tiene su carpeta y un apartado (subcarpeta) por tipo de documento.
 *
 *  - folders.sistema: carpetas raíz gestionadas por la app ("Trabajadores",
 *    "Trabajadores de baja").
 *  - folders.rrhh_tipo_documento_id: apartado de documentación de un empleado.
 *
 * Aditiva: no modifica datos existentes.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('rrhh_tipos_documento')) {
            Schema::create('rrhh_tipos_documento', function (Blueprint $table) {
                $table->id();
                $table->string('nombre', 150)->unique();
                $table->boolean('obligatorio')->default(true);
                $table->boolean('requiere_caducidad')->default(false);
                $table->unsignedSmallInteger('dias_aviso')->default(30);
                $table->unsignedInteger('orden')->default(0);
                $table->boolean('activo')->default(true);
                $table->timestamps();
            });

            // Tipos iniciales; se pueden cambiar desde Recursos humanos → Configuración.
            $ahora = now();
            DB::table('rrhh_tipos_documento')->insert(array_map(
                fn ($t, $i) => [
                    'nombre' => $t[0],
                    'obligatorio' => $t[1],
                    'requiere_caducidad' => $t[2],
                    'dias_aviso' => 30,
                    'orden' => $i + 1,
                    'activo' => true,
                    'created_at' => $ahora,
                    'updated_at' => $ahora,
                ],
                $tipos = [
                    ['DNI - NIE', true, true],
                    ['Contrato de trabajo', true, false],
                    ['Alta en la Seguridad Social', true, false],
                    ['Reconocimiento médico', true, true],
                    ['Formación PRL', true, true],
                    ['Entrega de EPIs', true, false],
                    ['Nóminas', false, false],
                    ['Otros', false, false],
                ],
                array_keys($tipos),
            ));
        }

        if (! Schema::hasTable('empleados')) {
            Schema::create('empleados', function (Blueprint $table) {
                $table->id();
                $table->string('nombre', 100);
                $table->string('apellidos', 150);
                $table->string('dni', 20)->unique();
                $table->string('nss', 20)->nullable();
                $table->date('fecha_nacimiento')->nullable();
                $table->string('telefono', 30)->nullable();
                $table->string('email', 150)->nullable();
                $table->string('direccion')->nullable();
                $table->string('codigo_postal', 10)->nullable();
                $table->string('poblacion', 100)->nullable();
                $table->string('provincia', 100)->nullable();
                $table->string('puesto', 100)->nullable();
                $table->string('categoria_convenio', 100)->nullable();
                $table->string('tipo_contrato', 40)->nullable();
                $table->string('jornada', 20)->nullable();
                $table->decimal('horas_semanales', 5, 2)->nullable();
                $table->decimal('salario_bruto_anual', 12, 2)->nullable();
                $table->text('iban')->nullable(); // cifrado (cast encrypted)
                $table->string('contacto_emergencia_nombre', 150)->nullable();
                $table->string('contacto_emergencia_relacion', 60)->nullable();
                $table->string('contacto_emergencia_telefono', 30)->nullable();
                $table->foreignId('obra_id')->nullable()->constrained('obras')->nullOnDelete();
                $table->unsignedBigInteger('folder_id')->nullable()->index();
                $table->string('estado', 20)->default('activo')->index();
                $table->text('observaciones')->nullable();
                $table->timestamps();

                $table->index(['apellidos', 'nombre']);
            });
        }

        if (! Schema::hasTable('empleado_periodos')) {
            Schema::create('empleado_periodos', function (Blueprint $table) {
                $table->id();
                $table->foreignId('empleado_id')->constrained('empleados')->cascadeOnDelete();
                $table->date('fecha_alta');
                $table->date('fecha_baja')->nullable();
                $table->string('tipo_contrato', 40)->nullable();
                $table->string('motivo_baja', 40)->nullable();
                $table->text('observaciones_baja')->nullable();
                $table->timestamps();

                $table->index(['empleado_id', 'fecha_alta']);
            });
        }

        Schema::table('folders', function (Blueprint $table) {
            if (! Schema::hasColumn('folders', 'sistema')) {
                $table->string('sistema', 30)->nullable()->after('tipo')->index();
            }
            if (! Schema::hasColumn('folders', 'rrhh_tipo_documento_id')) {
                $table->unsignedBigInteger('rrhh_tipo_documento_id')->nullable()->after('tipo')->index();
            }
        });
    }

    public function down(): void
    {
        Schema::table('folders', function (Blueprint $table) {
            if (Schema::hasColumn('folders', 'rrhh_tipo_documento_id')) {
                $table->dropIndex(['rrhh_tipo_documento_id']);
                $table->dropColumn('rrhh_tipo_documento_id');
            }
            if (Schema::hasColumn('folders', 'sistema')) {
                $table->dropIndex(['sistema']);
                $table->dropColumn('sistema');
            }
        });

        Schema::dropIfExists('empleado_periodos');
        Schema::dropIfExists('empleados');
        Schema::dropIfExists('rrhh_tipos_documento');
    }
};
