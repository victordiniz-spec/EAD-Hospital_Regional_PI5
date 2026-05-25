<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('duvidas_frequentes')) {
            Schema::create('duvidas_frequentes', function (Blueprint $table) {
                $table->id();
                $table->string('pergunta');
                $table->text('resposta');
                $table->string('categoria')->nullable();
                $table->string('texto_botao')->nullable();
                $table->string('rota_botao')->nullable();
                $table->boolean('ativo')->default(true);
                $table->integer('ordem')->default(0);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('duvidas_frequentes');
    }
};