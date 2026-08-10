<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dependencias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tarea_padre_id')->constrained('tareas')->cascadeOnDelete();
            $table->foreignId('tarea_hija_id')->constrained('tareas')->cascadeOnDelete();
            $table->enum('tipo', ['fin_a_inicio', 'inicio_a_inicio', 'fin_a_fin'])->default('fin_a_inicio');

            $table->unique(['tarea_padre_id', 'tarea_hija_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dependencias');
    }
};
