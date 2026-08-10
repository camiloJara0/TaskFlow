<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE notificaciones MODIFY COLUMN tipo ENUM('comentario','asignacion','recordatorio','cambio_estado','mencion','sistema','logro','hito_racha','reunion') NOT NULL DEFAULT 'sistema'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE notificaciones MODIFY COLUMN tipo ENUM('comentario','asignacion','recordatorio','cambio_estado','mencion','sistema','logro','hito_racha') NOT NULL DEFAULT 'sistema'");
    }
};
