<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;

Route::get('/', function () {
    return view('auth.login');
});

Route::get('/cadastro-aluno', function () {
    return view('auth.cadastro-aluno');
});

// CADASTRO DE ALUNO
Route::post('/salvar-aluno', [UserController::class, 'salvarAluno']);

// LOGIN
Route::post('/login', [UserController::class, 'login']);

// DASHBOARDS
Route::get('/dashboard-aluno', function () {
    return "Bem-vindo Aluno!";
});

Route::get('/dashboard-professor', function () {
    return "Bem-vindo Professor!";
});