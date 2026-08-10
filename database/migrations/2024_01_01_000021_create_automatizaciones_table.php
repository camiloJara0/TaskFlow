<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('automatizaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workspace_id')->constrained('espacios_trabajo')->cascadeOnDelete();
            $table->string('nombre');
            $table->string('evento');
            $table->json('condicion')->nullable();
            $table->json('accion');
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('automatizaciones');
    }
};
