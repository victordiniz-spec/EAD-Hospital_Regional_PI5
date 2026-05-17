<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('certificados_emitidos')) {
            Schema::create('certificados_emitidos', function (Blueprint $table) {
                $table->id();

                $table->foreignId('aluno_id')
                    ->nullable()
                    ->constrained('users')
                    ->nullOnDelete();

                $table->foreignId('certificado_modelo_id')
                    ->nullable()
                    ->constrained('certificados')
                    ->nullOnDelete();

                $table->string('aluno_nome');
                $table->string('email')->nullable();
                $table->string('cpf')->nullable();

                $table->string('curso');
                $table->integer('carga_horaria')->default(0);

                $table->decimal('nota_final', 5, 2)->nullable();

                $table->string('codigo_validacao')->unique();

                $table->timestamp('data_emissao')->nullable();

                $table->timestamps();

                $table->index('aluno_id');
                $table->index('certificado_modelo_id');
                $table->index('cpf');
                $table->index('curso');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('certificados_emitidos');
    }
};