<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recordatorios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('tarea_id')->constrained('tareas')->cascadeOnDelete();
            $table->datetime('fecha');
            $table->enum('tipo', ['5_minutos', '30_minutos', '1_hora', '1_dia', '1_semana'])->default('1_hora');
            $table->boolean('enviado')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recordatorios');
    }
};
