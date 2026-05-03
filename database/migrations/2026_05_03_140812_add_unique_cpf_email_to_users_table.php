<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'email_verified_at')) {
                $table->timestamp('email_verified_at')->nullable();
            }
        });

        try {
            Schema::table('users', function (Blueprint $table) {
                $table->unique('cpf');
            });
        } catch (\Throwable $e) {
            // Ignora caso o índice já exista
        }

        try {
            Schema::table('users', function (Blueprint $table) {
                $table->unique('email');
            });
        } catch (\Throwable $e) {
            // Ignora caso o índice já exista
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            try {
                $table->dropUnique(['cpf']);
            } catch (\Throwable $e) {
                //
            }

            try {
                $table->dropUnique(['email']);
            } catch (\Throwable $e) {
                //
            }
        });
    }
};