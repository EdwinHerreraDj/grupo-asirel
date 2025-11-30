<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('login_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Relación con la tabla users
            $table->string('ip_address')->nullable(); // Dirección IP del usuario
            $table->string('user_agent')->nullable(); // Información del navegador/dispositivo
            $table->timestamp('logged_in_at')->nullable(); // Fecha y hora de inicio de sesión
            $table->timestamp('logged_out_at')->nullable(); // Fecha y hora de cierre de sesión
            $table->timestamps(); // timestamps para created_at y updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('login_logs');
    }
};
