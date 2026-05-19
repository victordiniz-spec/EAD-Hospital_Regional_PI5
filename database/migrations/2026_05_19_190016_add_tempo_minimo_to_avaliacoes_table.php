<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('avaliacoes', function (Blueprint $table) {
            if (!Schema::hasColumn('avaliacoes', 'tempo_minimo')) {
                $table->integer('tempo_minimo')->nullable()->default(0)->after('tempo_limite');
            }
        });
    }

    public function down(): void
    {
        Schema::table('avaliacoes', function (Blueprint $table) {
            if (Schema::hasColumn('avaliacoes', 'tempo_minimo')) {
                $table->dropColumn('tempo_minimo');
            }
        });
    }
};