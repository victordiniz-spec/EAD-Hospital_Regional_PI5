<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('avisos', function (Blueprint $table) {
            if (!Schema::hasColumn('avisos', 'titulo')) {
                $table->string('titulo')->nullable();
            }

            if (!Schema::hasColumn('avisos', 'mensagem')) {
                $table->text('mensagem')->nullable();
            }

            if (!Schema::hasColumn('avisos', 'categoria')) {
                $table->string('categoria')->default('informativo');
            }

            if (!Schema::hasColumn('avisos', 'status')) {
                $table->string('status')->default('publicado');
            }
        });
    }

    public function down(): void
    {
        Schema::table('avisos', function (Blueprint $table) {
            if (Schema::hasColumn('avisos', 'status')) {
                $table->dropColumn('status');
            }

            if (Schema::hasColumn('avisos', 'categoria')) {
                $table->dropColumn('categoria');
            }

            if (Schema::hasColumn('avisos', 'mensagem')) {
                $table->dropColumn('mensagem');
            }

            if (Schema::hasColumn('avisos', 'titulo')) {
                $table->dropColumn('titulo');
            }
        });
    }
};