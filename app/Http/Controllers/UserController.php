<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function salvarAluno(Request $request)
    {
        $request->validate([
            'nome' => 'required',
            'email' => 'required|email|unique:users,email',
            'senha' => 'required|min:6'
        ]);

        User::create([
            'name' => $request->nome,
            'email' => $request->email,
            'password' => bcrypt($request->senha),
            'tipo' => 'aluno'
        ]);

        return redirect('/')->with('success', 'Conta criada com sucesso!');
    }

    public function login(Request $request)
    {
        // 🔐 validação
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        $credenciais = $request->only('email', 'password');

        if (Auth::attempt($credenciais)) {

            // 🔥 MUITO IMPORTANTE (segurança)
            $request->session()->regenerate();

            $user = Auth::user();

            if ($user->tipo == 'professor') {
                return redirect('/dashboard-professor');
            } else {
                return redirect('/dashboard-aluno');
            }
        }

        return back()->with('erro', 'Email ou senha inválidos');
    }
}