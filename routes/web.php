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
Route::post('/logout', function () {
    Auth::logout();
    return redirect('/');
})->name('logout');

// CADASTRO
Route::get('/cadastro-aluno', function () {
    return view('auth.cadastro-aluno');
})->name('cadastro.aluno');

Route::post('/salvar-aluno', [UserController::class, 'salvarAluno'])
    ->name('salvar.aluno');


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

        $user->update([
            'name' => request('name'),
            'email' => request('email'),
            'cpf' => request('cpf'),
        ]);

        return back()->with('success', 'Usuário atualizado!');
    })->name('usuarios.update');


    // =========================
    // ✔ APROVAÇÃO
    // =========================
    Route::post('/aprovar-usuario/{id}', [UserController::class, 'aprovar'])
        ->name('usuario.aprovar');

    Route::post('/rejeitar-usuario/{id}', [UserController::class, 'rejeitar'])
        ->name('usuario.rejeitar');


    // =========================
    // 🎥 VIDEOAULAS (PROFESSOR)
    // =========================
    Route::get('/videoaulas', [AulaController::class, 'index'])
        ->name('videoaulas');

    Route::get('/videoaulas/criar', [AulaController::class, 'create'])
        ->name('aulas.criar');

    Route::post('/videoaulas', [AulaController::class, 'store'])
        ->name('aulas.store');

    Route::delete('/aulas/{id}', [AulaController::class, 'destroy'])
        ->name('aulas.destroy');


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

    // CRIAR MODELO
    Route::get('/certificados/criar', function () {
        return view('dashboard.certificados.criar');
    })->name('certificados.criar');

    // SALVAR MODELO
    Route::post('/certificados', function () {

        $caminho = request()->hasFile('assinatura')
            ? request()->file('assinatura')->store('assinaturas', 'public')
            : null;

        \Illuminate\Support\Facades\DB::table('certificados')->insert([
            'curso' => request('curso'),
            'carga_horaria' => request('carga_horaria'),
            'responsavel' => request('responsavel'),
            'cargo' => request('cargo'),
            'assinatura' => $caminho,
            'created_at' => now(),
        ]);

        return back()->with('success', 'Certificado salvo com sucesso!');
    })->name('certificados.store');


    // 🔥 GERAR PDF
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