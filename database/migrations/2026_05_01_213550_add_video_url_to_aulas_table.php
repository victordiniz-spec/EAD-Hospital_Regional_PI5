<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('aulas', function (Blueprint $table) {
            if (!Schema::hasColumn('aulas', 'video_url')) {
                $table->text('video_url')->nullable()->after('descricao');
            }
        });
    }

    public function down(): void
    {
        Schema::table('aulas', function (Blueprint $table) {
            if (Schema::hasColumn('aulas', 'video_url')) {
                $table->dropColumn('video_url');
            }
        });
    }
};