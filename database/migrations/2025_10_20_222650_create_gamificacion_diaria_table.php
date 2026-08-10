<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gamificacion_diaria', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gamificacion_id')->constrained('gamificaciones')->cascadeOnDelete();
            $table->date('fecha');
            $table->unsignedInteger('tareas')->default(0);
            $table->unsignedBigInteger('xp')->default(0);
            $table->unique(['gamificacion_id', 'fecha']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gamificacion_diaria');
    }
};
