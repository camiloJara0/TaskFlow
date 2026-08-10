<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('favoritos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->constrained('users')->cascadeOnDelete();
            $table->string('favoritable_type');
            $table->unsignedBigInteger('favoritable_id');
            $table->timestamps();

            $table->unique(['usuario_id', 'favoritable_type', 'favoritable_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('favoritos');
    }
};
