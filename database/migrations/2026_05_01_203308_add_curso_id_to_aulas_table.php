<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('aulas', function (Blueprint $table) {
            if (!Schema::hasColumn('aulas', 'curso_id')) {
                $table->foreignId('curso_id')
                    ->nullable()
                    ->constrained('cursos')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('aulas', function (Blueprint $table) {
            if (Schema::hasColumn('aulas', 'curso_id')) {
                $table->dropConstrainedForeignId('curso_id');
            }
        });
    }
};