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

        // 🔥 REMOVE MÁSCARA DO CPF
        $cpf = preg_replace('/\D/', '', $request->cpf);

        User::create([
            'name' => $request->nome,
            'cpf' => $cpf,
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

        // 🔥 REMOVE MÁSCARA DO CPF
        $cpf = preg_replace('/\D/', '', $request->cpf);

        // 🔍 Buscar usuário
        $user = User::where('cpf', $cpf)->first();

        // ❌ Erro login
        if (!$user || !Hash::check($request->password, $user->password)) {
            return back()->with('erro', 'CPF ou senha inválidos');
        }

        // 🚫 Não aprovado
        if ($user->status !== 'aprovado') {
            return back()->with('erro', 'Aguarde aprovação do administrador.');
        }

        // ✅ Login
        Auth::login($user);
        $request->session()->regenerate();

        // 🔥 REDIRECIONAMENTO FINAL CORRETO
        if ($user->tipo === 'admin') {
            return redirect('/dashboard-professor');
        }

        // residente e preceptor
        return redirect('/dashboard-aluno');
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