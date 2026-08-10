<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('miembros_equipo', function (Blueprint $table) {
            $table->id();
            $table->foreignId('equipo_id')->constrained('equipos')->cascadeOnDelete();
            $table->foreignId('usuario_id')->constrained('users')->cascadeOnDelete();
            $table->enum('rol', ['Owner', 'Administrador', 'Editor', 'Miembro', 'Invitado'])->default('Miembro');
            $table->json('permisos')->nullable();
            $table->timestamp('fecha_ingreso');
            $table->timestamps();

            $table->unique(['equipo_id', 'usuario_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('miembros_equipo');
    }
};
