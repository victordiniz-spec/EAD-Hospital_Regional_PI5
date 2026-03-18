<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\DashboardController;

// LOGIN
Route::get('/', function () {
    return view('auth.login');
});

// CADASTRO DE ALUNO
Route::get('/cadastro-aluno', function () {
    return view('auth.cadastro-aluno');
});

Route::post('/salvar-aluno', [UserController::class, 'salvarAluno']);

// LOGIN POST
Route::post('/login', [UserController::class, 'login']);

// LOGOUT
Route::post('/logout', function () {
    \Illuminate\Support\Facades\Auth::logout();
    return redirect('/');
});

// DASHBOARD ALUNO
Route::get('/dashboard-aluno', function () {
    return view('dashboard.aluno');
})->middleware('auth');

// DASHBOARD PROFESSOR (AGORA COM DADOS DO BANCO)
Route::get('/dashboard-professor', [DashboardController::class, 'professor'])
    ->middleware('auth');