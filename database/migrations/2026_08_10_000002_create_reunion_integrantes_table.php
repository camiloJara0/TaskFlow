<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reunion_integrantes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_reunion')->constrained('reuniones')->cascadeOnDelete();
            $table->foreignId('id_usuario')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['id_reunion', 'id_usuario']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reunion_integrantes');
    }
};
