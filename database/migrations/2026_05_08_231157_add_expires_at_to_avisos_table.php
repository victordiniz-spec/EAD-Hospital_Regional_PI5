<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('avisos', function (Blueprint $table) {
            if (!Schema::hasColumn('avisos', 'expires_at')) {
                $table->timestamp('expires_at')->nullable()->after('mensagem');
            }
        });
    }

    public function down(): void
    {
        Schema::table('avisos', function (Blueprint $table) {
            if (Schema::hasColumn('avisos', 'expires_at')) {
                $table->dropColumn('expires_at');
            }
        });
    }
};