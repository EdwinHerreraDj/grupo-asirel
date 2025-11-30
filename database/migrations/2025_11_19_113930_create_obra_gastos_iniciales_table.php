<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('obra_gastos_iniciales', function (Blueprint $table) {
            $table->id();

            $table->foreignId('obra_id')
                ->constrained('obras')
                ->onDelete('cascade');

            $table->foreignId('gasto_base_id')
                ->constrained('gastos_base')
                ->onDelete('cascade');

            $table->decimal('importe', 10, 2)->default(0);

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('obra_gastos_iniciales');
    }
};
    