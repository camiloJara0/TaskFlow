<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tareas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('espacio_trabajo_id')->nullable()->constrained('espacios_trabajo')->nullOnDelete();
            $table->foreignId('creador_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('responsable_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('titulo');
            $table->jsonb('descripcion')->nullable();
            $table->string('estado')->nullable();
            $table->enum('prioridad', ['Alta', 'Media', 'Baja', 'Urgente'])->default('Media');
            $table->datetime('fecha_inicio')->nullable();
            $table->datetime('fecha_vencimiento')->nullable();
            $table->decimal('estimacion_horas', 8, 2)->nullable();
            $table->decimal('horas_invertidas', 8, 2)->nullable()->default(0);
            $table->integer('porcentaje')->default(0);
            $table->integer('orden')->default(0);
            $table->boolean('es_recurrente')->default(false);
            $table->boolean('plantilla')->default(false);
            $table->boolean('archivada')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tareas');
    }
};
