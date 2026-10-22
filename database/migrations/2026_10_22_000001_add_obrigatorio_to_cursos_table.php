<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Identifica quais cursos fazem parte da formação obrigatória.
     * Os cursos já existentes começam como obrigatórios.
     */
    public function up(): void
    {
        if (!Schema::hasTable('cursos')) {
            throw new \RuntimeException('A tabela cursos precisa existir antes desta migration.');
        }

        if (!Schema::hasColumn('cursos', 'obrigatorio')) {
            Schema::table('cursos', function (Blueprint $table) {
                $table->boolean('obrigatorio')->default(true);
            });
        }
    }

    /**
     * Reverte apenas a coluna adicionada, sem apagar cursos existentes.
     */
    public function down(): void
    {
        if (Schema::hasTable('cursos') && Schema::hasColumn('cursos', 'obrigatorio')) {
            Schema::table('cursos', function (Blueprint $table) {
                $table->dropColumn('obrigatorio');
            });
        }
    }
};
