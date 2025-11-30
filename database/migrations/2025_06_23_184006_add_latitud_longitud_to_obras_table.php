<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('obras', function (Blueprint $table) {
            $table->decimal('latitud', 10, 7)->nullable()->after('fecha_fin');
            $table->decimal('longitud', 10, 7)->nullable()->after('latitud');
            $table->decimal('radio', 10, 2)->default(0)->after('longitud');
        });
    }

    public function down()
    {
        Schema::table('obras', function (Blueprint $table) {
            $table->dropColumn(['latitud', 'longitud', 'radio']);
        });
    }
};
