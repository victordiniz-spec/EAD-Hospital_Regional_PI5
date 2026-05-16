<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Mantém o padrão como pendente para novos cadastros
        DB::statement("ALTER TABLE users ALTER COLUMN status SET DEFAULT 'pendente'");

        // Garante que usuários sem status virem pendentes
        DB::table('users')
            ->whereNull('status')
            ->update(['status' => 'pendente']);

        // Remove uma restrição antiga, caso já exista
        DB::statement("ALTER TABLE users DROP CONSTRAINT IF EXISTS users_status_check");

        // Permite somente os status usados pelo sistema
        DB::statement("
            ALTER TABLE users
            ADD CONSTRAINT users_status_check
            CHECK (status IN ('pendente', 'aprovado', 'inutilizado'))
        ");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE users DROP CONSTRAINT IF EXISTS users_status_check");

        DB::statement("ALTER TABLE users ALTER COLUMN status SET DEFAULT 'pendente'");
    }
};