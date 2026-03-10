<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;

Route::get('/', function () {
    return view('auth.login');
});

Route::get('/cadastro-aluno', function () {
    return view('auth.cadastro-aluno');
});

Route::get('/cadastro-professor', function () {
    return view('auth.cadastro-professor');
});

Route::post('/salvar-aluno', [UserController::class, 'salvarAluno']);

Route::post('/salvar-professor', [UserController::class, 'salvarProfessor']);