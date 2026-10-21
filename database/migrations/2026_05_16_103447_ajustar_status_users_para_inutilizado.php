
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement(
            "ALTER TABLE users ALTER COLUMN status SET DEFAULT 'pendente'"
        );

        DB::table('users')
            ->whereNull('status')
            ->update(['status' => 'pendente']);

        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            $existe = DB::table('information_schema.TABLE_CONSTRAINTS')
                ->where('CONSTRAINT_SCHEMA', DB::getDatabaseName())
                ->where('TABLE_NAME', 'users')
                ->where('CONSTRAINT_NAME', 'users_status_check')
                ->where('CONSTRAINT_TYPE', 'CHECK')
                ->exists();

            if ($existe) {
                DB::statement(
                    "ALTER TABLE users DROP CHECK users_status_check"
                );
            }
        } elseif ($driver === 'pgsql') {
            DB::statement(
                "ALTER TABLE users DROP CONSTRAINT IF EXISTS users_status_check"
            );
        }

        if (in_array($driver, ['mysql', 'pgsql'], true)) {
            DB::statement("
                ALTER TABLE users
                ADD CONSTRAINT users_status_check
                CHECK (status IN (
                    'pendente',
                    'aprovado',
                    'inutilizado'
                ))
            ");
        }
    }

    public function down(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            $existe = DB::table('information_schema.TABLE_CONSTRAINTS')
                ->where('CONSTRAINT_SCHEMA', DB::getDatabaseName())
                ->where('TABLE_NAME', 'users')
                ->where('CONSTRAINT_NAME', 'users_status_check')
                ->where('CONSTRAINT_TYPE', 'CHECK')
                ->exists();

            if ($existe) {
                DB::statement(
                    "ALTER TABLE users DROP CHECK users_status_check"
                );
            }
        } elseif ($driver === 'pgsql') {
            DB::statement(
                "ALTER TABLE users DROP CONSTRAINT IF EXISTS users_status_check"
            );
        }
    }
};
