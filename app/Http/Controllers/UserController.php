<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    // =========================
    // CADASTRO (USUÁRIO)
    // =========================
    public function salvarAluno(Request $request)
    {
        $request->validate([
            'nome' => 'required|string|max:255',
            'cpf' => 'required|unique:users,cpf',
            'email' => 'required|email|unique:users,email',
            'senha' => 'required|min:6',
            'tipo' => 'required|in:residente,preceptor'
        ]);

        User::create([
            'name' => $request->nome,
            'cpf' => $request->cpf,
            'email' => $request->email,
            'password' => bcrypt($request->senha),
            'tipo' => $request->tipo,
            'status' => 'pendente'
        ]);

        return redirect('/')->with('success', 'Cadastro enviado! Aguarde aprovação.');
    }

    // =========================
    // LOGIN COM CPF
    // =========================
    public function login(Request $request)
    {
        $request->validate([
            'cpf' => 'required',
            'password' => 'required'
        ]);

        // 🔍 Buscar usuário pelo CPF
        $user = User::where('cpf', $request->cpf)->first();

        // ❌ CPF ou senha inválidos
        if (!$user || !Hash::check($request->password, $user->password)) {
            return back()->with('erro', 'CPF ou senha inválidos');
        }

        // 🚫 Usuário não aprovado
        if ($user->status !== 'aprovado') {
            return back()->with('erro', 'Aguarde aprovação do administrador.');
        }

        // ✅ Login
        Auth::login($user);
        $request->session()->regenerate();

        // 🔀 Redirecionamento
        if ($user->tipo === 'preceptor') {
            return redirect('/dashboard-professor');
        } else {
            return redirect('/dashboard-aluno');
        }
    }

    // =========================
    // APROVAR USUÁRIO
    // =========================
    public function aprovar($id)
    {
        $user = User::findOrFail($id);
        $user->status = 'aprovado';
        $user->save();

        return back()->with('success', 'Usuário aprovado com sucesso!');
    }

    // =========================
    // REJEITAR USUÁRIO
    // =========================
    public function rejeitar($id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        return back()->with('success', 'Usuário rejeitado.');
    }

    // =========================
    // LOGOUT
    // =========================
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}