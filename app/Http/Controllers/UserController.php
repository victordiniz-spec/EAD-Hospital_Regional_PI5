<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    // SALVAR ALUNO
    public function salvarAluno(Request $request)
    {
        $request->validate([
            'nome' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'senha' => 'required|min:6',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        $fotoPath = null;
        if ($request->hasFile('foto')) {
            $fotoPath = $request->file('foto')->store('fotos', 'public');
        }

        User::create([
            'name' => $request->nome,                  // nome do input -> coluna 'name'
            'email' => $request->email,
            'password' => bcrypt($request->senha),     // senha do input -> coluna 'password'
            'tipo' => 'aluno',
            'foto' => $fotoPath
        ]);

        return redirect()->back()->with('success', 'Aluno cadastrado com sucesso!');
    }

    // SALVAR PROFESSOR
    public function salvarProfessor(Request $request)
    {
        $request->validate([
            'nome' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'senha' => 'required|min:6',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        $fotoPath = null;
        if ($request->hasFile('foto')) {
            $fotoPath = $request->file('foto')->store('fotos', 'public');
        }

        User::create([
            'name' => $request->nome,
            'email' => $request->email,
            'password' => bcrypt($request->senha),
            'tipo' => 'professor',
            'foto' => $fotoPath
        ]);

        return redirect()->back()->with('success', 'Professor cadastrado com sucesso!');
    }

    // LOGIN
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        $credenciais = $request->only('email', 'password');

        if (Auth::attempt($credenciais)) {
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