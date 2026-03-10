<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class UserController extends Controller
{

    public function salvarAluno(Request $request)
    {

        User::create([
            'name' => $request->nome,
            'email' => $request->email,
            'password' => bcrypt($request->senha),
            'tipo' => 'aluno'
        ]);

        return redirect('/');

    }

    public function salvarProfessor(Request $request)
    {

        User::create([
            'name' => $request->nome,
            'email' => $request->email,
            'password' => bcrypt($request->senha),
            'tipo' => 'professor'
        ]);

        return redirect('/');

    }

}