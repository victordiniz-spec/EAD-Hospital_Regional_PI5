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

/*
|--------------------------------------------------------------------------
| FUNÇÕES DE APOIO
|--------------------------------------------------------------------------
| super_admin: acesso total
| professor: área administrativa
| residente/preceptor: área do aluno
*/

if (!function_exists('tipoUsuarioAtual')) {
    function tipoUsuarioAtual(): ?string
    {
        return Auth::check() ? (Auth::user()->tipo ?? null) : null;
    }
}

if (!function_exists('statusUsuarioAtual')) {
    function statusUsuarioAtual(): ?string
    {
        return Auth::check() ? (Auth::user()->status ?? null) : null;
    }
}

if (!function_exists('usuarioAprovado')) {
    function usuarioAprovado(): bool
    {
        return Auth::check() && statusUsuarioAtual() === 'aprovado';
    }
}

if (!function_exists('usuarioPodeProfessor')) {
    function usuarioPodeProfessor(): bool
    {
        return usuarioAprovado() && in_array(tipoUsuarioAtual(), ['super_admin', 'professor']);
    }
}

if (!function_exists('usuarioPodeAluno')) {
    function usuarioPodeAluno(): bool
    {
        return usuarioAprovado() && in_array(tipoUsuarioAtual(), ['super_admin', 'residente', 'preceptor']);
    }
}

/*
|--------------------------------------------------------------------------
| MIDDLEWARES LOCAIS
|--------------------------------------------------------------------------
*/

$verificarAprovado = function ($request, $next) {
    if (!Auth::check()) {
        return redirect()->route('login');
    }

    if ((Auth::user()->status ?? null) !== 'aprovado') {
        Auth::logout();

        return redirect()
            ->route('login')
            ->with('error', 'Seu acesso ainda não está aprovado ou foi bloqueado. Entre em contato com a administração.');
    }

    return $next($request);
};

$somenteProfessor = function ($request, $next) {
    if (!Auth::check()) {
        return redirect()->route('login');
    }

    if ((Auth::user()->status ?? null) !== 'aprovado') {
        Auth::logout();

        return redirect()
            ->route('login')
            ->with('error', 'Seu acesso ainda não está aprovado ou foi bloqueado. Entre em contato com a administração.');
    }

    if (!in_array(Auth::user()->tipo, ['super_admin', 'professor'])) {
        abort(403, 'Você não tem permissão para acessar esta área.');
    }

    return $next($request);
};

$somenteAluno = function ($request, $next) {
    if (!Auth::check()) {
        return redirect()->route('login');
    }

    if ((Auth::user()->status ?? null) !== 'aprovado') {
        Auth::logout();

        return redirect()
            ->route('login')
            ->with('error', 'Seu acesso ainda não está aprovado ou foi bloqueado. Entre em contato com a administração.');
    }

    if (!in_array(Auth::user()->tipo, ['super_admin', 'residente', 'preceptor'])) {
        abort(403, 'Você não tem permissão para acessar esta área.');
    }

    return $next($request);
};

/*
|--------------------------------------------------------------------------
| ROTAS PÚBLICAS
|--------------------------------------------------------------------------
*/

// LOGIN
Route::get('/', function () {
    if (Auth::check()) {
        $tipo = Auth::user()->tipo ?? null;

        if (in_array($tipo, ['super_admin', 'professor'])) {
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

// ESQUECI MINHA SENHA
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
| REDIRECIONAMENTO GERAL PROTEGIDO
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', $verificarAprovado])->group(function () {
    Route::get('/dashboard', function () {
        $tipo = Auth::user()->tipo ?? null;

        if (in_array($tipo, ['super_admin', 'professor'])) {
            return redirect()->route('dashboard.professor');
        }

        if (in_array($tipo, ['residente', 'preceptor'])) {
            return redirect()->route('dashboard.aluno');
        }

        abort(403, 'Tipo de usuário não reconhecido.');
    })->name('dashboard');
});

/*
|--------------------------------------------------------------------------
| ÁREA DO ALUNO
|--------------------------------------------------------------------------
| Acesso: super_admin, residente e preceptor.
*/
Route::middleware(['auth', $somenteAluno])->group(function () {

    Route::get('/dashboard-aluno', [DashboardController::class, 'aluno'])
        ->name('dashboard.aluno');

    // VIDEOAULAS DO ALUNO
    Route::get('/minhas-aulas', [AulaController::class, 'aluno'])
        ->name('aluno.aulas');

    Route::get('/assistir-aula/{id}', [AulaController::class, 'assistir'])
        ->name('aulas.assistir');

    // AVALIAÇÕES / PÓS-TESTES DO ALUNO
    Route::get('/avaliacoes/{id}/resultado', [AvaliacaoController::class, 'resultado'])
        ->name('avaliacoes.resultado');

    Route::get('/avaliacoes/{id}', [AvaliacaoController::class, 'show'])
        ->name('avaliacoes.show');

    Route::post('/avaliacoes/{id}/submit', [AvaliacaoController::class, 'responder'])
        ->name('avaliacoes.submit');

    // PROVA FINAL DO ALUNO
    Route::get('/prova-final', [AvaliacaoController::class, 'provaFinal'])
        ->name('prova.final');

    Route::post('/prova-final/responder', [AvaliacaoController::class, 'responderFinal'])
        ->name('prova.final.responder');

    // CERTIFICADO DO ALUNO
    Route::get('/meu-certificado', [CertificadoController::class, 'aluno'])
        ->name('certificado.aluno');

    Route::get('/certificado/gerar/{id}', [CertificadoController::class, 'gerar'])
        ->name('certificado.gerar');
});

/*
|--------------------------------------------------------------------------
| ÁREA ADMINISTRATIVA / PROFESSOR
|--------------------------------------------------------------------------
| Acesso: super_admin e professor.
*/
Route::middleware(['auth', $somenteProfessor])->group(function () {

    // DASHBOARD PROFESSOR
    Route::get('/dashboard-professor', [DashboardController::class, 'professor'])
        ->name('dashboard.professor');

    // USUÁRIOS
    Route::get('/controle-usuarios', [DashboardController::class, 'controleUsuarios'])
        ->name('controle.usuarios');

    Route::put('/usuarios/{id}', function ($id) {
        $user = \App\Models\User::findOrFail($id);

        if ($user->tipo === 'super_admin' && (auth()->user()->tipo ?? null) !== 'super_admin') {
            abort(403, 'Você não pode editar este usuário.');
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
            abort(403, 'O super administrador não pode ser excluído por esta tela.');
        }

        $user->delete();

        return back()->with('success', 'Usuário excluído com sucesso!');
    })->name('usuarios.destroy');

    // APROVAÇÃO / REJEIÇÃO / INUTILIZAÇÃO
    Route::post('/aprovar-usuario/{id}', [UserController::class, 'aprovar'])
        ->name('usuario.aprovar');

    Route::post('/rejeitar-usuario/{id}', [UserController::class, 'rejeitar'])
        ->name('usuario.rejeitar');

    Route::patch('/usuarios/{id}/inutilizar', [UserController::class, 'inutilizar'])
        ->name('usuarios.inutilizar');

    Route::patch('/usuarios/{id}/reativar', [UserController::class, 'reativar'])
        ->name('usuarios.reativar');

    // VIDEOAULAS / CURSOS / BIBLIOTECA
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

    // CRIAR / EDITAR PÓS-TESTE
    Route::get('/avaliacoes/criar/{aula}', [AvaliacaoController::class, 'create'])
        ->name('avaliacoes.criar');

    Route::post('/avaliacoes', [AvaliacaoController::class, 'store'])
        ->name('avaliacoes.store');

    // PROVA FINAL ADMIN
    Route::get('/prova-final/criar', [AvaliacaoController::class, 'createFinal'])
        ->name('prova.final.criar');

    Route::post('/prova-final/salvar', [AvaliacaoController::class, 'storeFinal'])
        ->name('prova.final.store');

    // CERTIFICADOS ADMIN
    Route::get('/certificados/criar', function () {
        return view('dashboard.certificados.criar');
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

    // ALUNOS
    Route::get('/alunos', [DashboardController::class, 'alunos'])
        ->name('alunos');

    // AVISOS
    Route::get('/avisos', [AvisoController::class, 'index'])
        ->name('avisos');

    Route::post('/avisos', [AvisoController::class, 'store'])
        ->name('avisos.store');

    Route::get('/avisos/{id}/edit', [AvisoController::class, 'edit'])
        ->name('avisos.edit');

    Route::put('/avisos/{id}', [AvisoController::class, 'update'])
        ->name('avisos.update');

    Route::delete('/avisos/{id}', [AvisoController::class, 'destroy'])
        ->name('avisos.destroy');

    // FUTURO
    Route::get('/postestes', fn() => view('dashboard.postestes'))
        ->name('postestes');

    // DEBUG
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
