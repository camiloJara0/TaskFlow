<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('logros', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('titulo');
            $table->string('descripcion');
            $table->string('icono')->default('i-lucide-trophy');
            $table->enum('rareza', ['comun', 'raro', 'epico', 'legendario'])->default('comun');
            $table->enum('categoria', [
                'productividad', 'disciplina', 'constancia',
                'estudio', 'trabajo', 'organizacion',
            ])->default('productividad');
            $table->string('condicion')->nullable();
            $table->unsignedInteger('meta')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('logros');
    }
};
