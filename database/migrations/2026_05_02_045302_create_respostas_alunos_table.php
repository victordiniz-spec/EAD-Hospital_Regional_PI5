<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('respostas_alunos')) {
            Schema::create('respostas_alunos', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('aluno_id');
                $table->unsignedBigInteger('avaliacao_id');
                $table->unsignedBigInteger('pergunta_id');
                $table->unsignedBigInteger('resposta_id');
                $table->timestamps();

                $table->unique(['aluno_id', 'avaliacao_id', 'pergunta_id'], 'resp_aluno_avaliacao_pergunta_unique');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('respostas_alunos');
    }
};