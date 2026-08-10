<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reuniones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('espacio_trabajo_id')->nullable()->constrained('espacios_trabajo')->nullOnDelete();
            $table->foreignId('creador_id')->constrained('users')->cascadeOnDelete();
            $table->string('titulo');
            $table->text('descripcion')->nullable();
            $table->string('estado')->default('pendiente');
            $table->date('fecha');
            $table->time('hora');
            $table->string('url')->nullable();
            $table->boolean('archivada')->default(false);
            $table->boolean('recordatorio_enviado')->default(false);
            $table->timestamps();

            $table->index(['fecha', 'hora']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reuniones');
    }
};
