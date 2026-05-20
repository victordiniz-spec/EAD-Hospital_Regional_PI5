<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('aulas', function (Blueprint $table) {
            if (!Schema::hasColumn('aulas', 'tempo_minimo_video')) {
                $table->integer('tempo_minimo_video')
                    ->nullable()
                    ->default(0)
                    ->after('video_url');
            }

            if (!Schema::hasColumn('aulas', 'tempo_maximo_video')) {
                $table->integer('tempo_maximo_video')
                    ->nullable()
                    ->after('tempo_minimo_video');
            }
        });
    }

    public function down(): void
    {
        Schema::table('aulas', function (Blueprint $table) {
            if (Schema::hasColumn('aulas', 'tempo_maximo_video')) {
                $table->dropColumn('tempo_maximo_video');
            }

            if (Schema::hasColumn('aulas', 'tempo_minimo_video')) {
                $table->dropColumn('tempo_minimo_video');
            }
        });
    }
};