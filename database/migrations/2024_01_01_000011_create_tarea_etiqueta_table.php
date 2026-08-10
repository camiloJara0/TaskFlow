<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tarea_etiqueta', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tarea_id')->constrained('tareas')->cascadeOnDelete();
            $table->foreignId('etiqueta_id')->constrained('etiquetas')->cascadeOnDelete();

            $table->unique(['tarea_id', 'etiqueta_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tarea_etiqueta');
    }
};
