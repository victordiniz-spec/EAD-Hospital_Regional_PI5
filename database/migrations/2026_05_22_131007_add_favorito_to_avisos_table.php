<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('avisos', function (Blueprint $table) {
            if (!Schema::hasColumn('avisos', 'favorito')) {
                $table->boolean('favorito')
                    ->default(false)
                    ->after('categoria');
            }
        });
    }

    public function down(): void
    {
        Schema::table('avisos', function (Blueprint $table) {
            if (Schema::hasColumn('avisos', 'favorito')) {
                $table->dropColumn('favorito');
            }
        });
    }
};