<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AulaController;
use App\Http\Controllers\AvaliacaoController;

// ------------------------
// LOGIN
// ------------------------
Route::get('/', function () {
    return view('auth.login');
});

Route::post('/login', [UserController::class, 'login']);

Route::post('/logout', function () {
    \Illuminate\Support\Facades\Auth::logout();
    return redirect('/');
});

// ------------------------
// CADASTRO DE ALUNO
// ------------------------
Route::get('/cadastro-aluno', function () {
    return view('auth.cadastro-aluno');
});

Route::post('/salvar-aluno', [UserController::class, 'salvarAluno']);

// ------------------------
// DASHBOARD ALUNO
// ------------------------
Route::get('/dashboard-aluno', function () {
    return view('dashboard.aluno');
})->middleware('auth')->name('dashboard.aluno');

// ------------------------
// DASHBOARD PROFESSOR
// ------------------------
Route::get('/dashboard-professor', [DashboardController::class, 'professor'])
    ->middleware('auth')
    ->name('dashboard.professor');

// ------------------------
// VIDEOAULAS
// ------------------------
Route::get('/videoaulas', [AulaController::class, 'index'])
    ->middleware('auth')
    ->name('videoaulas');

// CRIAR AULA
Route::get('/videoaulas/criar', [AulaController::class, 'create'])
    ->middleware('auth')
    ->name('aulas.criar');

// SALVAR AULA
Route::post('/videoaulas', [AulaController::class, 'store'])
    ->middleware('auth')
    ->name('aulas.store');

// ------------------------
// AVALIAÇÕES (PÓS-TESTE)
// ------------------------

// PROFESSOR - CRIAR AVALIAÇÃO
Route::get('/avaliacoes/criar/{aula}', [AvaliacaoController::class, 'create'])
    ->middleware('auth')
    ->name('avaliacoes.criar');

// SALVAR AVALIAÇÃO
Route::post('/avaliacoes', [AvaliacaoController::class, 'store'])
    ->middleware('auth')
    ->name('avaliacoes.store');

// ALUNO - VISUALIZAR TESTE
Route::get('/avaliacoes/{id}', [AvaliacaoController::class, 'show'])
    ->middleware('auth')
    ->name('avaliacoes.show');

// ALUNO - RESPONDER TESTE
Route::post('/avaliacoes/responder', [AvaliacaoController::class, 'responder'])
    ->middleware('auth')
    ->name('avaliacoes.responder');

// ------------------------
// FUTURAS TELAS
// ------------------------
Route::get('/postestes', function () {
    return view('dashboard.postestes');
})->middleware('auth')->name('postestes');

Route::get('/alunos', function () {
    return view('dashboard.alunos');
})->middleware('auth')->name('alunos');