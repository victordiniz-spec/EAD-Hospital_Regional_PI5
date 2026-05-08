<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('avisos_lidos', function (Blueprint $table) {
            $table->id();

            // Não vamos usar foreign key forçada para evitar erro de incompatibilidade
            $table->unsignedBigInteger('aviso_id');
            $table->unsignedBigInteger('user_id');

            $table->timestamp('lido_em')->nullable();

            $table->timestamps();

            // Evita o mesmo usuário marcar o mesmo aviso duas vezes
            $table->unique(['aviso_id', 'user_id']);

            // Índices para deixar a consulta rápida
            $table->index('aviso_id');
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('avisos_lidos');
    }
};