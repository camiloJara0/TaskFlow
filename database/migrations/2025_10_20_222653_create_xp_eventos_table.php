<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('xp_eventos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gamificacion_id')->constrained('gamificaciones')->cascadeOnDelete();
            $table->unsignedBigInteger('tarea_id')->nullable();
            $table->unsignedBigInteger('cantidad');
            $table->string('motivo');
            $table->timestamps();
            $table->unique(['gamificacion_id', 'tarea_id'], 'xp_evt_tarea_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('xp_eventos');
    }
};
