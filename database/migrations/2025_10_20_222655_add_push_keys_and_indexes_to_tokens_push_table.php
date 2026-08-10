<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE tokens_push MODIFY token VARCHAR(512) NOT NULL');

        $columnas = array_column(DB::select('SHOW COLUMNS FROM tokens_push'), 'Field');
        if (!in_array('p256dh', $columnas, true)) {
            DB::statement('ALTER TABLE tokens_push ADD COLUMN p256dh TEXT NULL AFTER token');
        }
        if (!in_array('auth', $columnas, true)) {
            DB::statement('ALTER TABLE tokens_push ADD COLUMN auth TEXT NULL AFTER p256dh');
        }

        $indices = array_column(DB::select('SHOW INDEX FROM tokens_push'), 'Key_name');
        if (!in_array('tokens_push_usuario_token_unique', $indices, true)) {
            if ($this->existeFk('tokens_push_usuario_id_foreign')) {
                DB::statement('ALTER TABLE tokens_push DROP FOREIGN KEY tokens_push_usuario_id_foreign');
            }
            if (in_array('tokens_push_usuario_id_foreign', $indices, true)) {
                DB::statement('ALTER TABLE tokens_push DROP INDEX tokens_push_usuario_id_foreign');
            }
            DB::statement('ALTER TABLE tokens_push ADD UNIQUE INDEX tokens_push_usuario_token_unique (usuario_id, token)');
            DB::statement('ALTER TABLE tokens_push ADD INDEX tokens_push_token_index (token)');
            DB::statement('ALTER TABLE tokens_push ADD CONSTRAINT tokens_push_usuario_id_foreign FOREIGN KEY (usuario_id) REFERENCES users (id) ON DELETE CASCADE');
        }
    }

    public function down(): void
    {
        $indices = array_column(DB::select('SHOW INDEX FROM tokens_push'), 'Key_name');

        if (in_array('tokens_push_usuario_token_unique', $indices, true)) {
            if ($this->existeFk('tokens_push_usuario_id_foreign')) {
                DB::statement('ALTER TABLE tokens_push DROP FOREIGN KEY tokens_push_usuario_id_foreign');
            }
            if (in_array('tokens_push_usuario_token_unique', $indices, true)) {
                DB::statement('ALTER TABLE tokens_push DROP INDEX tokens_push_usuario_token_unique');
            }
            if (in_array('tokens_push_token_index', $indices, true)) {
                DB::statement('ALTER TABLE tokens_push DROP INDEX tokens_push_token_index');
            }
            DB::statement('ALTER TABLE tokens_push ADD CONSTRAINT tokens_push_usuario_id_foreign FOREIGN KEY (usuario_id) REFERENCES users (id) ON DELETE CASCADE');
        }

        $columnas = array_column(DB::select('SHOW COLUMNS FROM tokens_push'), 'Field');
        if (in_array('p256dh', $columnas, true)) {
            DB::statement('ALTER TABLE tokens_push DROP COLUMN p256dh');
        }
        if (in_array('auth', $columnas, true)) {
            DB::statement('ALTER TABLE tokens_push DROP COLUMN auth');
        }

        DB::statement('ALTER TABLE tokens_push MODIFY token TEXT NOT NULL');
    }

    private function existeFk(string $nombre): bool
    {
        $registro = DB::selectOne(
            "SELECT CONSTRAINT_NAME FROM information_schema.TABLE_CONSTRAINTS
             WHERE CONSTRAINT_SCHEMA = DATABASE()
               AND TABLE_NAME = 'tokens_push'
               AND CONSTRAINT_NAME = ?
               AND CONSTRAINT_TYPE = 'FOREIGN KEY'",
            [$nombre]
        );

        return $registro !== null;
    }
};
