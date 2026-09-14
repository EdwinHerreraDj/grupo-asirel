<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Drive: almacenamiento privado y registro de borrados.
 *
 *  - files.disco: disco donde está el fichero físico. Conviven dos formatos:
 *      · `uploads/...` en el disco público (subidas de la app React),
 *      · `drive/...` en el disco privado `local` (formato antiguo).
 *    Se rellena a partir de la ruta actual; las subidas nuevas van a `local`.
 *  - drive_eliminaciones: quién borró qué carpeta o archivo y cuánto contenido.
 *
 * Aditiva: no modifica ni borra datos existentes.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('files', 'disco')) {
            Schema::table('files', function (Blueprint $table) {
                $table->string('disco', 20)->nullable()->after('ruta');
            });
        }

        DB::table('files')->whereNull('disco')->where('ruta', 'like', 'uploads/%')->update(['disco' => 'public']);
        DB::table('files')->whereNull('disco')->update(['disco' => 'local']);

        if (! Schema::hasTable('drive_eliminaciones')) {
            Schema::create('drive_eliminaciones', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('tipo', 20); // carpeta | archivo
                $table->string('nombre');
                $table->string('ubicacion', 1000)->nullable();
                $table->unsignedInteger('carpetas')->default(0);
                $table->unsignedInteger('archivos')->default(0);
                $table->timestamps();

                $table->index('created_at');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('drive_eliminaciones');

        if (Schema::hasColumn('files', 'disco')) {
            Schema::table('files', function (Blueprint $table) {
                $table->dropColumn('disco');
            });
        }
    }
};
