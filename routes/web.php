<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

use App\Http\Controllers\UserController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AulaController;
use App\Http\Controllers\AvaliacaoController;
use App\Http\Controllers\AvisoController;
use App\Http\Controllers\CertificadoController;
use App\Http\Controllers\SuporteController;

/*
|--------------------------------------------------------------------------
| ROTAS PÚBLICAS
|--------------------------------------------------------------------------
*/

// LOGIN
Route::get('/', function () {
    if (Auth::check()) {
        $tipo = Auth::user()->tipo ?? null;

        if (in_array($tipo, ['super_admin', 'admin', 'professor'])) {
            return redirect()->route('dashboard.professor');
        }

        if (in_array($tipo, ['residente', 'preceptor'])) {
            return redirect()->route('dashboard.aluno');
        }
    }

    return view('auth.login');
})->name('login');

Route::post('/login', [UserController::class, 'login'])->name('login.post');

// LOGOUT
Route::post('/logout', [UserController::class, 'logout'])->name('logout');

// CADASTRO
Route::get('/cadastro-aluno', function () {
    return view('auth.cadastro-aluno');
})->name('cadastro.aluno');

Route::post('/salvar-aluno', [UserController::class, 'salvarAluno'])
    ->name('salvar.aluno');

// VERIFICAÇÃO DE E-MAIL NO CADASTRO
Route::get('/verificar-email-cadastro', [UserController::class, 'telaVerificarCadastro'])
    ->name('cadastro.verificar');

Route::post('/verificar-email-cadastro', [UserController::class, 'verificarCodigoCadastro'])
    ->name('cadastro.verificar.codigo');

Route::post('/reenviar-codigo-cadastro', [UserController::class, 'reenviarCodigoCadastro'])
    ->name('cadastro.reenviar.codigo');


// =========================
// ESQUECI MINHA SENHA
// =========================
Route::get('/esqueci-minha-senha', [UserController::class, 'telaEsqueciSenha'])
    ->name('senha.esqueci');

Route::post('/esqueci-minha-senha', [UserController::class, 'enviarCodigoRedefinicaoSenha'])
    ->name('senha.enviar.codigo');

Route::get('/redefinir-senha', [UserController::class, 'telaRedefinirSenha'])
    ->name('senha.redefinir');

Route::post('/redefinir-senha', [UserController::class, 'redefinirSenha'])
    ->name('senha.atualizar');

Route::post('/reenviar-codigo-senha', [UserController::class, 'reenviarCodigoRedefinicaoSenha'])
    ->name('senha.reenviar.codigo');


/*
|--------------------------------------------------------------------------
| ROTAS PROTEGIDAS
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {

    // =========================
    // REDIRECIONAMENTO GERAL
    // =========================
    Route::get('/dashboard', function () {
        $tipo = auth()->user()->tipo ?? null;

        if (in_array($tipo, ['super_admin', 'admin', 'professor'])) {
            return redirect()->route('dashboard.professor');
        }

        if (in_array($tipo, ['residente', 'preceptor'])) {
            return redirect()->route('dashboard.aluno');
        }

        abort(403, 'Tipo de usuário não reconhecido.');
    })->name('dashboard');


    // =========================
    // DASHBOARDS
    // =========================
    Route::get('/dashboard-aluno', [DashboardController::class, 'aluno'])
        ->name('dashboard.aluno');

    Route::get('/dashboard-professor', [DashboardController::class, 'professor'])
        ->name('dashboard.professor');


    // =========================
    // 📊 ACOMPANHAMENTO DOS RESIDENTES
    // =========================
    Route::get('/acompanhamento-residentes', [DashboardController::class, 'acompanhamentoResidentes'])
        ->name('acompanhamento.residentes');


    // =========================
    // 👤 USUÁRIOS
    // =========================
    Route::get('/controle-usuarios', [DashboardController::class, 'controleUsuarios'])
        ->name('controle.usuarios');

    Route::put('/usuarios/{id}', function ($id) {
        $user = \App\Models\User::findOrFail($id);

        if ($user->tipo === 'super_admin' && auth()->user()->tipo !== 'super_admin') {
            abort(403, 'Você não pode editar o super administrador.');
        }

        $cpf = preg_replace('/\D/', '', request('cpf'));

        request()->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'cpf' => 'required',
        ]);

        if (\App\Models\User::where('cpf', $cpf)->where('id', '!=', $id)->exists()) {
            return back()->with('error', 'Este CPF já está cadastrado em outro usuário.');
        }

        $user->update([
            'name' => request('name'),
            'email' => request('email'),
            'cpf' => $cpf,
        ]);

        return back()->with('success', 'Usuário atualizado com sucesso!');
    })->name('usuarios.update');

    Route::delete('/usuarios/{id}', function ($id) {
        $user = \App\Models\User::findOrFail($id);

        if (auth()->id() == $user->id) {
            return back()->with('error', 'Você não pode excluir o próprio usuário logado.');
        }

        if ($user->tipo === 'super_admin') {
            return back()->with('error', 'O super administrador não pode ser excluído por esta tela.');
        }

        $user->delete();

        return back()->with('success', 'Usuário excluído com sucesso!');
    })->name('usuarios.destroy');


    // =========================
    // ✔ APROVAÇÃO / REJEIÇÃO / INUTILIZAÇÃO
    // =========================
    Route::post('/aprovar-usuario/{id}', [UserController::class, 'aprovar'])
        ->name('usuario.aprovar');

    Route::post('/rejeitar-usuario/{id}', [UserController::class, 'rejeitar'])
        ->name('usuario.rejeitar');

    Route::patch('/usuarios/{id}/inutilizar', [UserController::class, 'inutilizar'])
        ->name('usuarios.inutilizar');

    Route::patch('/usuarios/{id}/reativar', [UserController::class, 'reativar'])
        ->name('usuarios.reativar');


    // =========================
    // 🔔 NOTIFICAÇÕES DA NAVBAR - USUÁRIOS PENDENTES
    // =========================
    Route::get('/navbar/usuarios-pendentes', function () {
        $usuarioLogado = auth()->user();

        if (!$usuarioLogado) {
            return response()->json([
                'success' => false,
                'total' => 0,
                'usuarios' => [],
                'message' => 'Usuário não autenticado.',
            ]);
        }

        $tipo = strtolower($usuarioLogado->tipo ?? 'usuario');

        $podeVerPendentes = in_array($tipo, [
            'super_admin',
            'admin',
            'administrador',
            'professor',
        ]);

        if (!$podeVerPendentes) {
            return response()->json([
                'success' => true,
                'total' => 0,
                'usuarios' => [],
            ]);
        }

        $query = DB::table('users')
            ->where(function ($q) {
                $q->where('status', 'pendente')
                    ->orWhere('status', 'aguardando')
                    ->orWhere('status', 'aguardando_aprovacao')
                    ->orWhere('status', 'aguardando aprovação');
            });

        $total = (clone $query)->count();

        $usuarios = $query
            ->orderByDesc('created_at')
            ->limit(10)
            ->get()
            ->map(function ($user) {
                $tipoUsuario = $user->tipo ?? 'usuario';

                $criadoEm = null;

                if (!empty($user->created_at)) {
                    try {
                        $criadoEm = \Carbon\Carbon::parse($user->created_at)
                            ->timezone('America/Sao_Paulo')
                            ->format('d/m/Y H:i');
                    } catch (\Throwable $e) {
                        $criadoEm = $user->created_at;
                    }
                }

                return [
                    'id' => $user->id,
                    'name' => $user->name ?? 'Usuário sem nome',
                    'email' => $user->email ?? '',
                    'cpf' => $user->cpf ?? '',
                    'tipo' => $tipoUsuario,
                    'tipo_formatado' => ucfirst(str_replace('_', ' ', $tipoUsuario)),
                    'status' => $user->status ?? 'pendente',
                    'created_at' => $user->created_at,
                    'created_at_formatado' => $criadoEm,
                    'aprovar_url' => route('usuario.aprovar', $user->id),
                    'rejeitar_url' => route('usuario.rejeitar', $user->id),
                    'controle_url' => route('controle.usuarios'),
                    'csrf_token' => csrf_token(),
                ];
            })
            ->values();

        return response()->json([
            'success' => true,
            'total' => $total,
            'usuarios' => $usuarios,
            'controle_url' => route('controle.usuarios'),
        ]);
    })->name('navbar.usuarios-pendentes');


    // =========================
    // 🔔 ALERTAS AUTOMÁTICOS DO PROFESSOR (NAVBAR)
    // =========================
    Route::get('/navbar/alertas-professor', function () {
        $usuarioLogado = auth()->user();

        if (!$usuarioLogado) {
            return response()->json([
                'success' => false,
                'total' => 0,
                'alertas' => [],
                'message' => 'Usuário não autenticado.',
            ]);
        }

        $tipo = strtolower($usuarioLogado->tipo ?? 'usuario');

        $podeVerAlertasProfessor = in_array($tipo, [
            'super_admin',
            'admin',
            'administrador',
            'professor',
        ]);

        if (!$podeVerAlertasProfessor) {
            return response()->json([
                'success' => true,
                'total' => 0,
                'alertas' => [],
            ]);
        }

        $alertas = collect();

        $schema = DB::getSchemaBuilder();
        $tiposAlunos = ['residente', 'preceptor'];

        // 1. Usuários pendentes de aprovação
        if ($schema->hasTable('users')) {
            $pendentes = DB::table('users')
                ->whereIn('tipo', $tiposAlunos)
                ->where(function ($q) {
                    $q->where('status', 'pendente')
                        ->orWhere('status', 'aguardando')
                        ->orWhere('status', 'aguardando_aprovacao')
                        ->orWhere('status', 'aguardando aprovação');
                })
                ->orderByDesc('created_at')
                ->limit(5)
                ->get();

            foreach ($pendentes as $pendente) {
                $criadoEm = null;

                if (!empty($pendente->created_at)) {
                    try {
                        $criadoEm = \Carbon\Carbon::parse($pendente->created_at)
                            ->timezone('America/Sao_Paulo')
                            ->format('d/m/Y H:i');
                    } catch (\Throwable $e) {
                        $criadoEm = $pendente->created_at;
                    }
                }

                $alertas->push([
                    'id' => 'pendente_' . $pendente->id,
                    'tipo' => 'pendente',
                    'nivel' => 'warning',
                    'icone' => 'usuario',
                    'titulo' => 'Novo usuário aguardando aprovação',
                    'mensagem' => ($pendente->name ?? 'Usuário sem nome') . ' solicitou acesso ao sistema.',
                    'detalhe' => trim(($pendente->email ?? '') . (!empty($pendente->cpf) ? ' • CPF: ' . $pendente->cpf : '')),
                    'data' => $criadoEm,
                    'acao_texto' => 'Aprovar no controle',
                    'acao_url' => route('controle.usuarios'),
                ]);
            }
        }

        $totalAulas = $schema->hasTable('aulas')
            ? DB::table('aulas')->count()
            : 0;

        $totalAvaliacoes = $schema->hasTable('avaliacoes')
            ? DB::table('avaliacoes')
                ->where(function ($query) {
                    $query->where('tipo', 'normal')
                        ->orWhere('tipo', 'pos_teste')
                        ->orWhere('tipo', 'pós-teste')
                        ->orWhereNull('tipo');
                })
                ->count()
            : 0;

        if ($schema->hasTable('users')) {
            $residentes = DB::table('users')
                ->whereIn('tipo', $tiposAlunos)
                ->where('status', 'aprovado')
                ->orderBy('name')
                ->get();

            foreach ($residentes as $residente) {
                $aulasAssistidas = 0;
                $avaliacoesFeitas = 0;
                $media = 0;
                $ultimaAtividade = null;

                if ($schema->hasTable('aulas_assistidas')) {
                    $aulasAssistidas = DB::table('aulas_assistidas')
                        ->where('aluno_id', $residente->id)
                        ->where('assistido', true)
                        ->distinct('aula_id')
                        ->count('aula_id');

                    $ultimaAtividade = DB::table('aulas_assistidas')
                        ->where('aluno_id', $residente->id)
                        ->max('updated_at');
                }

                if ($schema->hasTable('notas')) {
                    $avaliacoesFeitas = DB::table('notas')
                        ->where('aluno_id', $residente->id)
                        ->distinct('avaliacao_id')
                        ->count('avaliacao_id');

                    $media = DB::table('notas')
                        ->where('aluno_id', $residente->id)
                        ->avg('nota') ?? 0;

                    if (!$ultimaAtividade) {
                        $ultimaAtividade = DB::table('notas')
                            ->where('aluno_id', $residente->id)
                            ->max('created_at');
                    }
                }

                $progresso = $totalAulas > 0
                    ? (int) round(($aulasAssistidas / $totalAulas) * 100)
                    : 0;

                $postestesPendentes = max($totalAvaliacoes - $avaliacoesFeitas, 0);

                $diasSemProgresso = null;

                if ($ultimaAtividade) {
                    try {
                        $diasSemProgresso = (int) floor(
                            \Carbon\Carbon::parse($ultimaAtividade)
                                ->timezone('America/Sao_Paulo')
                                ->startOfDay()
                                ->diffInDays(now()->timezone('America/Sao_Paulo')->startOfDay())
                        );
                    } catch (\Throwable $e) {
                        $diasSemProgresso = null;
                    }
                }

                if ($diasSemProgresso !== null && $diasSemProgresso >= 7) {
                    $alertas->push([
                        'id' => 'sem_progresso_' . $residente->id,
                        'tipo' => 'sem_progresso',
                        'nivel' => 'danger',
                        'icone' => 'tempo',
                        'titulo' => 'Residente sem progresso há ' . $diasSemProgresso . ' dia(s)',
                        'mensagem' => ($residente->name ?? 'Residente') . ' não registra aula assistida ou pós-teste recente.',
                        'detalhe' => 'Progresso atual: ' . $progresso . '%',
                        'data' => null,
                        'acao_texto' => 'Ver acompanhamento',
                        'acao_url' => route('acompanhamento.residentes'),
                    ]);
                }

                if ($progresso < 50 && ($aulasAssistidas > 0 || $postestesPendentes > 0)) {
                    $alertas->push([
                        'id' => 'baixo_progresso_' . $residente->id,
                        'tipo' => 'baixo_progresso',
                        'nivel' => 'warning',
                        'icone' => 'grafico',
                        'titulo' => 'Baixo progresso no curso',
                        'mensagem' => ($residente->name ?? 'Residente') . ' está com apenas ' . $progresso . '% de progresso.',
                        'detalhe' => $aulasAssistidas . ' de ' . $totalAulas . ' aula(s) concluída(s).',
                        'data' => null,
                        'acao_texto' => 'Ver acompanhamento',
                        'acao_url' => route('acompanhamento.residentes'),
                    ]);
                }

                if ($postestesPendentes > 0) {
                    $alertas->push([
                        'id' => 'posteste_pendente_' . $residente->id,
                        'tipo' => 'posteste_pendente',
                        'nivel' => 'info',
                        'icone' => 'teste',
                        'titulo' => 'Pós-teste pendente',
                        'mensagem' => ($residente->name ?? 'Residente') . ' possui ' . $postestesPendentes . ' pós-teste(s) pendente(s).',
                        'detalhe' => 'Pós-testes feitos: ' . $avaliacoesFeitas . ' de ' . $totalAvaliacoes . '.',
                        'data' => null,
                        'acao_texto' => 'Ver acompanhamento',
                        'acao_url' => route('acompanhamento.residentes'),
                    ]);
                }

                if ($media > 0 && $media < 7) {
                    $alertas->push([
                        'id' => 'media_baixa_' . $residente->id,
                        'tipo' => 'media_baixa',
                        'nivel' => 'danger',
                        'icone' => 'nota',
                        'titulo' => 'Média abaixo do ideal',
                        'mensagem' => ($residente->name ?? 'Residente') . ' está com média ' . number_format($media, 1, ',', '.') . '.',
                        'detalhe' => 'Acompanhar desempenho nas avaliações.',
                        'data' => null,
                        'acao_texto' => 'Ver acompanhamento',
                        'acao_url' => route('acompanhamento.residentes'),
                    ]);
                }

                if ($progresso >= 70 && $media >= 7) {
                    $alertas->push([
                        'id' => 'quase_certificado_' . $residente->id,
                        'tipo' => 'quase_certificado',
                        'nivel' => 'success',
                        'icone' => 'certificado',
                        'titulo' => 'Residente quase liberando certificado',
                        'mensagem' => ($residente->name ?? 'Residente') . ' já tem bom progresso e média favorável.',
                        'detalhe' => 'Progresso: ' . $progresso . '% • Média: ' . number_format($media, 1, ',', '.'),
                        'data' => null,
                        'acao_texto' => 'Ver certificados',
                        'acao_url' => route('certificados.criar'),
                    ]);
                }
            }
        }

        $alertas = $alertas
            ->take(20)
            ->values();

        return response()->json([
            'success' => true,
            'total' => $alertas->count(),
            'alertas' => $alertas,
            'acompanhamento_url' => route('acompanhamento.residentes'),
        ]);
    })->name('navbar.alertas-professor');


    // =========================
    // 🎥 VIDEOAULAS / CURSOS / BIBLIOTECA
    // =========================
    Route::get('/videoaulas', [AulaController::class, 'index'])
        ->name('videoaulas');

    Route::get('/videoaulas/criar', [AulaController::class, 'create'])
        ->name('aulas.criar');

    Route::post('/videoaulas', [AulaController::class, 'store'])
        ->name('aulas.store');

    Route::put('/aulas/{id}', [AulaController::class, 'update'])
        ->name('aulas.update');

    Route::delete('/aulas/{id}', [AulaController::class, 'destroy'])
        ->name('aulas.destroy');

    Route::get('/biblioteca-cursos', [AulaController::class, 'bibliotecaCursos'])
        ->name('biblioteca.cursos');

    Route::post('/biblioteca-cursos/{id}/duplicar', [AulaController::class, 'duplicarCurso'])
        ->name('biblioteca.cursos.duplicar');

    Route::delete('/biblioteca-cursos/{id}', [AulaController::class, 'excluirCurso'])
        ->name('biblioteca.cursos.excluir');

    Route::post('/biblioteca-modulos/{id}/duplicar', [AulaController::class, 'duplicarModulo'])
        ->name('biblioteca.modulos.duplicar');

    Route::get('/banco-perguntas', [AulaController::class, 'bancoPerguntas'])
        ->name('banco.perguntas');


    // =========================
    // 🎬 VIDEOAULAS (ALUNO)
    // =========================
    Route::get('/minhas-aulas', [AulaController::class, 'aluno'])
        ->name('aluno.aulas');

    Route::get('/assistir-aula/{id}', [AulaController::class, 'assistir'])
        ->name('aulas.assistir');


    // =========================
    // 📝 AVALIAÇÕES
    // =========================
    Route::get('/avaliacoes/criar/{aula}', [AvaliacaoController::class, 'create'])
        ->name('avaliacoes.criar');

    Route::post('/avaliacoes', [AvaliacaoController::class, 'store'])
        ->name('avaliacoes.store');

    Route::get('/avaliacoes/{id}/resultado', [AvaliacaoController::class, 'resultado'])
        ->name('avaliacoes.resultado');

    Route::get('/avaliacoes/{id}', [AvaliacaoController::class, 'show'])
        ->name('avaliacoes.show');

    Route::post('/avaliacoes/{id}/submit', [AvaliacaoController::class, 'responder'])
        ->name('avaliacoes.submit');


    // =========================
    // 📝 PROVA FINAL (ALUNO)
    // =========================
    Route::get('/prova-final', [AvaliacaoController::class, 'provaFinal'])
        ->name('prova.final');

    Route::post('/prova-final/responder', [AvaliacaoController::class, 'responderFinal'])
        ->name('prova.final.responder');


    // =========================
    // 🛠️ PROVA FINAL (ADMIN)
    // =========================
    Route::get('/prova-final/criar', [AvaliacaoController::class, 'createFinal'])
        ->name('prova.final.criar');

    Route::post('/prova-final/salvar', [AvaliacaoController::class, 'storeFinal'])
        ->name('prova.final.store');


    // =========================
    // 🎓 CERTIFICADOS
    // =========================
    Route::get('/certificados/criar', function () {
        $certificados = collect();

        if (DB::getSchemaBuilder()->hasTable('certificados_emitidos')) {
            $certificados = DB::table('certificados_emitidos')
                ->select(
                    'id',
                    'aluno_id',
                    'aluno_nome',
                    'email',
                    'cpf',
                    'curso',
                    'carga_horaria',
                    'nota_final',
                    'codigo_validacao',
                    'data_emissao',
                    'created_at',
                    'updated_at'
                )
                ->orderByDesc('data_emissao')
                ->orderByDesc('created_at')
                ->get();
        }

        return view('dashboard.certificados.criar', compact('certificados'));
    })->name('certificados.criar');

    Route::post('/certificados', function () {
        DB::table('certificados')->insert([
            'curso' => request('curso'),
            'carga_horaria' => request('carga_horaria'),
            'responsavel' => request('responsavel'),
            'cargo' => request('cargo'),
            'assinatura' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Certificado salvo com sucesso!');
    })->name('certificados.store');

    Route::get('/meu-certificado', [CertificadoController::class, 'aluno'])
        ->name('certificado.aluno');

    Route::get('/certificado/gerar/{id}', [CertificadoController::class, 'gerar'])
        ->name('certificado.gerar');


    // =========================
    // 👨‍🎓 ALUNOS
    // =========================
    Route::get('/alunos', [DashboardController::class, 'alunos'])
        ->name('alunos');


    // =========================
    // 📢 AVISOS
    // =========================
    Route::get('/avisos', [AvisoController::class, 'index'])
        ->name('avisos');

    Route::post('/avisos', [AvisoController::class, 'store'])
        ->name('avisos.store');

    Route::get('/avisos/{id}/edit', [AvisoController::class, 'edit'])
        ->name('avisos.edit');

    Route::put('/avisos/{id}', [AvisoController::class, 'update'])
        ->name('avisos.update');

    Route::patch('/avisos/{id}/favorito', [AvisoController::class, 'toggleFavorito'])
        ->name('avisos.toggle-favorito');

    Route::delete('/avisos/{id}', [AvisoController::class, 'destroy'])
        ->name('avisos.destroy');
    
    // =========================
    // 💬 CENTRAL DE SUPORTE / FAQ
    // =========================
    Route::get('/suporte', [SuporteController::class, 'index'])
        ->name('suporte.index');

    Route::get('/suporte/admin', [SuporteController::class, 'admin'])
        ->name('suporte.admin');

    // IA GEMINI DO SUPORTE
    Route::post('/suporte/ia', [SuporteController::class, 'perguntarIa'])
        ->name('suporte.ia');

    Route::post('/suporte/duvidas', [SuporteController::class, 'store'])
        ->name('suporte.store');

    Route::put('/suporte/duvidas/{id}', [SuporteController::class, 'update'])
        ->name('suporte.update');

    Route::delete('/suporte/duvidas/{id}', [SuporteController::class, 'destroy'])
        ->name('suporte.destroy');


    // =========================
    // FUTURO
    // =========================
    Route::get('/postestes', fn() => view('dashboard.postestes'))
        ->name('postestes');


    // =========================
    // DEBUG
    // =========================
    Route::get('/gerar-senha', function () {
        return bcrypt('123456');
    });

});


/*
|--------------------------------------------------------------------------
| ROTAS DE TESTE DAS TELAS DE ERRO
|--------------------------------------------------------------------------
| Use apenas para testar as páginas criativas de erro.
| Antes de entregar o sistema em produção final, pode remover essas rotas.
|--------------------------------------------------------------------------
*/

Route::get('/teste-404', function () {
    abort(404);
});

Route::get('/teste-403', function () {
    abort(403);
});

Route::get('/teste-500', function () {
    abort(500);
});