<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

use App\Http\Controllers\UserController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AulaController;
use App\Http\Controllers\AvaliacaoController;
use App\Http\Controllers\AvisoController; // 🔥 FALTAVA ISSO

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
    // APROVAÇÃO DE USUÁRIOS 🔥
    // =========================
    Route::post('/aprovar-usuario/{id}', [UserController::class, 'aprovar'])
        ->name('usuario.aprovar'); // ✅ CORRIGIDO

    Route::post('/rejeitar-usuario/{id}', [UserController::class, 'rejeitar'])
        ->name('usuario.rejeitar'); // ✅ CORRIGIDO

    // =========================
    // VIDEOAULAS
    // =========================
    Route::get('/videoaulas', [AulaController::class, 'index'])
        ->name('videoaulas');

    Route::get('/videoaulas/criar', [AulaController::class, 'create'])
        ->name('aulas.criar');

    Route::post('/videoaulas', [AulaController::class, 'store'])
        ->name('aulas.store');

    Route::get('/assistir-aula/{id}', [AulaController::class, 'assistir'])
        ->name('aulas.assistir');

    Route::delete('/aulas/{id}', [AulaController::class, 'destroy'])
        ->name('aulas.destroy');

    // =========================
    // AVALIAÇÕES
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
    // ALUNOS
    // =========================
    Route::get('/alunos', [DashboardController::class, 'alunos'])
        ->name('alunos');

    // =========================
    // AVISOS 🔥
    // =========================
    Route::get('/avisos', [AvisoController::class, 'index'])
        ->name('avisos');

    Route::post('/avisos', [AvisoController::class, 'store'])
        ->name('avisos.store');

    // =========================
    // FUTURO
    // =========================
    Route::get('/postestes', fn() => view('dashboard.postestes'))
        ->name('postestes');

    // =========================
    // DEBUG (opcional)
    // =========================
    Route::get('/gerar-senha', function () {
        return bcrypt('123456');
    });

});