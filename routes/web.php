<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

use App\Http\Controllers\UserController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AulaController;
use App\Http\Controllers\AvaliacaoController;
use App\Http\Controllers\AvisoController;
use App\Http\Controllers\CertificadoController;

/*
|--------------------------------------------------------------------------
| ROTAS PÚBLICAS
|--------------------------------------------------------------------------
*/

// LOGIN
Route::get('/', function () {
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
    // DASHBOARDS
    // =========================
    Route::get('/dashboard-aluno', [DashboardController::class, 'aluno'])
        ->name('dashboard.aluno');

    Route::get('/dashboard-professor', [DashboardController::class, 'professor'])
        ->name('dashboard.professor');


    // =========================
    // 👤 USUÁRIOS
    // =========================
    Route::get('/controle-usuarios', [DashboardController::class, 'controleUsuarios'])
        ->name('controle.usuarios');

    Route::put('/usuarios/{id}', function ($id) {
        $user = \App\Models\User::findOrFail($id);

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

        $user->delete();

        return back()->with('success', 'Usuário excluído com sucesso!');
    })->name('usuarios.destroy');


    // =========================
    // ✔ APROVAÇÃO
    // =========================
    Route::post('/aprovar-usuario/{id}', [UserController::class, 'aprovar'])
        ->name('usuario.aprovar');

    Route::post('/rejeitar-usuario/{id}', [UserController::class, 'rejeitar'])
        ->name('usuario.rejeitar');


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
        return view('dashboard.certificados.criar');
    })->name('certificados.criar');

    Route::post('/certificados', function () {
        \Illuminate\Support\Facades\DB::table('certificados')->insert([
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

    Route::delete('/avisos/{id}', [AvisoController::class, 'destroy'])
        ->name('avisos.destroy');


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