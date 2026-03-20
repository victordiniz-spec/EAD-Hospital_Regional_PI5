<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

use App\Http\Controllers\UserController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AulaController;
use App\Http\Controllers\AvaliacaoController;

/*
|--------------------------------------------------------------------------
| ROTAS PÚBLICAS
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('auth.login');
})->name('login');

Route::post('/login', [UserController::class, 'login'])->name('login.post');

Route::post('/logout', function () {
    Auth::logout();
    return redirect('/');
})->name('logout');

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

    // DASHBOARDS
    Route::get('/dashboard-aluno', [DashboardController::class, 'aluno'])
        ->name('dashboard.aluno');

    Route::get('/dashboard-professor', [DashboardController::class, 'professor'])
        ->name('dashboard.professor');

    // VIDEOAULAS
    Route::get('/videoaulas', [AulaController::class, 'index'])
        ->name('videoaulas');

    Route::get('/videoaulas/criar', [AulaController::class, 'create'])
        ->name('aulas.criar');

    Route::post('/videoaulas', [AulaController::class, 'store'])
        ->name('aulas.store');

    // 🔥 NOVA ROTA (ASSISTIR)
    Route::get('/assistir-aula/{id}', [AulaController::class, 'assistir'])
        ->name('aulas.assistir');

    // AVALIAÇÕES
    Route::get('/avaliacoes/criar/{aula}', [AvaliacaoController::class, 'create'])
        ->name('avaliacoes.criar');

    Route::post('/avaliacoes', [AvaliacaoController::class, 'store'])
        ->name('avaliacoes.store');

    Route::get('/avaliacoes/{id}', [AvaliacaoController::class, 'show'])
        ->name('avaliacoes.show');

    Route::post('/avaliacoes/responder', [AvaliacaoController::class, 'responder'])
        ->name('avaliacoes.responder');

    // FUTURO
    Route::get('/postestes', fn() => view('dashboard.postestes'))->name('postestes');
    Route::get('/alunos', fn() => view('dashboard.alunos'))->name('alunos');
});