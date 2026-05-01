<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\User;

return new class extends Migration
{
    public function up(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Ajustes na tabela users
        |--------------------------------------------------------------------------
        */

        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (!Schema::hasColumn('users', 'cpf')) {
                    $table->string('cpf', 14)->nullable()->unique();
                }

                if (!Schema::hasColumn('users', 'foto')) {
                    $table->string('foto')->nullable();
                }

                if (!Schema::hasColumn('users', 'tipo')) {
                    $table->string('tipo')->default('residente');
                }

                if (!Schema::hasColumn('users', 'status')) {
                    $table->string('status')->default('pendente');
                }
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Cursos
        |--------------------------------------------------------------------------
        */

        if (!Schema::hasTable('cursos')) {
            Schema::create('cursos', function (Blueprint $table) {
                $table->id();
                $table->string('nome', 100);
                $table->text('descricao')->nullable();
                $table->foreignId('professor_id')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Módulos
        |--------------------------------------------------------------------------
        */

        if (!Schema::hasTable('modulos')) {
            Schema::create('modulos', function (Blueprint $table) {
                $table->id();
                $table->string('nome');
                $table->foreignId('curso_id')->nullable()->constrained('cursos')->nullOnDelete();
                $table->integer('ordem')->default(0);
                $table->timestamps();
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Aulas
        |--------------------------------------------------------------------------
        */

        if (!Schema::hasTable('aulas')) {
            Schema::create('aulas', function (Blueprint $table) {
                $table->id();
                $table->string('titulo', 100);
                $table->text('descricao')->nullable();
                $table->text('video_url')->nullable();
                $table->foreignId('curso_id')->nullable()->constrained('cursos')->nullOnDelete();
                $table->foreignId('modulo_id')->nullable()->constrained('modulos')->nullOnDelete();
                $table->timestamps();
            });
        } else {
            Schema::table('aulas', function (Blueprint $table) {
                if (!Schema::hasColumn('aulas', 'modulo_id')) {
                    $table->foreignId('modulo_id')->nullable()->constrained('modulos')->nullOnDelete();
                }
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Matrículas
        |--------------------------------------------------------------------------
        */

        if (!Schema::hasTable('matriculas')) {
            Schema::create('matriculas', function (Blueprint $table) {
                $table->id();
                $table->foreignId('aluno_id')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('curso_id')->nullable()->constrained('cursos')->nullOnDelete();
                $table->timestamps();
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Avaliações
        |--------------------------------------------------------------------------
        */

        if (!Schema::hasTable('avaliacoes')) {
            Schema::create('avaliacoes', function (Blueprint $table) {
                $table->id();
                $table->string('titulo', 100);
                $table->foreignId('aula_id')->nullable()->constrained('aulas')->nullOnDelete();
                $table->string('tipo')->default('normal');
                $table->integer('tempo_limite')->nullable();
                $table->integer('qtd_perguntas')->nullable();
                $table->timestamps();
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Perguntas
        |--------------------------------------------------------------------------
        */

        if (!Schema::hasTable('perguntas')) {
            Schema::create('perguntas', function (Blueprint $table) {
                $table->id();
                $table->text('pergunta');
                $table->foreignId('avaliacao_id')->nullable()->constrained('avaliacoes')->nullOnDelete();
                $table->timestamps();
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Respostas
        |--------------------------------------------------------------------------
        */

        if (!Schema::hasTable('respostas')) {
            Schema::create('respostas', function (Blueprint $table) {
                $table->id();
                $table->text('resposta');
                $table->boolean('correta')->default(false);
                $table->foreignId('pergunta_id')->nullable()->constrained('perguntas')->nullOnDelete();
                $table->timestamps();
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Notas
        |--------------------------------------------------------------------------
        */

        if (!Schema::hasTable('notas')) {
            Schema::create('notas', function (Blueprint $table) {
                $table->id();
                $table->foreignId('aluno_id')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('avaliacao_id')->nullable()->constrained('avaliacoes')->nullOnDelete();
                $table->decimal('nota', 5, 2)->nullable();
                $table->timestamps();
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Aulas assistidas
        |--------------------------------------------------------------------------
        */

        if (!Schema::hasTable('aulas_assistidas')) {
            Schema::create('aulas_assistidas', function (Blueprint $table) {
                $table->id();
                $table->foreignId('aluno_id')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('aula_id')->nullable()->constrained('aulas')->nullOnDelete();
                $table->boolean('assistido')->default(false);
                $table->timestamps();
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Avisos
        |--------------------------------------------------------------------------
        */

        if (!Schema::hasTable('avisos')) {
            Schema::create('avisos', function (Blueprint $table) {
                $table->id();
                $table->string('titulo');
                $table->text('mensagem');
                $table->string('categoria')->default('informativo');
                $table->timestamps();
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Certificados
        |--------------------------------------------------------------------------
        */

        if (!Schema::hasTable('certificados')) {
            Schema::create('certificados', function (Blueprint $table) {
                $table->id();
                $table->string('curso')->nullable();
                $table->integer('carga_horaria')->nullable();
                $table->string('responsavel')->nullable();
                $table->string('cargo')->nullable();
                $table->string('assinatura')->nullable();
                $table->timestamps();
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Modelos de certificados
        |--------------------------------------------------------------------------
        */

        if (!Schema::hasTable('certificados_modelos')) {
            Schema::create('certificados_modelos', function (Blueprint $table) {
                $table->id();
                $table->string('curso')->nullable();
                $table->integer('carga_horaria')->nullable();
                $table->string('responsavel')->nullable();
                $table->string('cargo')->nullable();
                $table->string('assinatura')->nullable();
                $table->timestamps();
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Certificados emitidos
        |--------------------------------------------------------------------------
        */

        if (!Schema::hasTable('certificados_emitidos')) {
            Schema::create('certificados_emitidos', function (Blueprint $table) {
                $table->id();
                $table->foreignId('aluno_id')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('modelo_id')->nullable()->constrained('certificados_modelos')->nullOnDelete();
                $table->string('nome_aluno')->nullable();
                $table->string('codigo')->nullable()->unique();
                $table->date('data_conclusao')->nullable();
                $table->timestamps();
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Usuário administrador inicial
        |--------------------------------------------------------------------------
        */

        if (Schema::hasTable('users')) {
            $adminExiste = DB::table('users')
                ->where('cpf', '12345678900')
                ->orWhere('email', 'admin@email.com')
                ->exists();

            if (!$adminExiste) {
                DB::table('users')->insert([
                    'name' => 'Administrador',
                    'cpf' => '12345678900',
                    'email' => 'admin@email.com',
                    'password' => bcrypt('12345678'),
                    'tipo' => 'admin',
                    'status' => 'aprovado',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('certificados_emitidos');
        Schema::dropIfExists('certificados_modelos');
        Schema::dropIfExists('certificados');
        Schema::dropIfExists('avisos');
        Schema::dropIfExists('aulas_assistidas');
        Schema::dropIfExists('notas');
        Schema::dropIfExists('respostas');
        Schema::dropIfExists('perguntas');
        Schema::dropIfExists('avaliacoes');
        Schema::dropIfExists('matriculas');
        Schema::dropIfExists('aulas');
        Schema::dropIfExists('modulos');
        Schema::dropIfExists('cursos');
    }
};