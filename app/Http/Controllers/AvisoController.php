<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Aviso;

class AvisoController extends Controller
{
    // =========================
    // LISTAR AVISOS
    // =========================
    public function index()
    {
        $avisos = Aviso::orderBy('created_at', 'desc')->get();

        return view('avisos.index', compact('avisos'));
    }

    // =========================
    // CRIAR AVISO
    // =========================
    public function store(Request $request)
    {
        // 🔥 VALIDAÇÃO
        $request->validate([
            'titulo' => 'required|string|max:255',
            'mensagem' => 'required|string',
            'categoria' => 'required|in:urgente,informativo'
        ]);

        // 🔥 SALVAR
        Aviso::create([
            'titulo' => $request->titulo,
            'mensagem' => $request->mensagem,
            'categoria' => $request->categoria,
        ]);

        return back()->with('success', 'Aviso criado com sucesso!');
    }
}