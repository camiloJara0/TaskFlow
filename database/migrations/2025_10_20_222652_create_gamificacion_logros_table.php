<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gamificacion_logros', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gamificacion_id')->constrained('gamificaciones')->cascadeOnDelete();
            $table->foreignId('logro_id')->constrained('logros')->cascadeOnDelete();
            $table->timestamp('desbloqueado_en')->useCurrent();
            $table->unique(['gamificacion_id', 'logro_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gamificacion_logros');
    }
};
