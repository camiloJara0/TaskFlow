<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gamificaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->constrained('users')->cascadeOnDelete()->unique();
            $table->unsignedBigInteger('xp')->default(0);
            $table->unsignedInteger('nivel')->default(1);
            $table->unsignedInteger('racha')->default(0);
            $table->unsignedInteger('mejor_racha')->default(0);
            $table->date('ultimo_dia_activo')->nullable();
            $table->unsignedBigInteger('tareas_completadas')->default(0);
            $table->json('preferencias')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gamificaciones');
    }
};
