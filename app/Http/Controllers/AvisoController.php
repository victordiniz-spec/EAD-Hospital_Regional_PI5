<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Aviso;

class AvisoController extends Controller
{
    // =========================
    // LISTAR AVISOS (HISTÓRICO)
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
        $request->validate([
            'titulo' => 'required|string|max:255',
            'mensagem' => 'required|string',
            'categoria' => 'required|in:urgente,informativo'
        ]);

        Aviso::create([
            'titulo' => $request->titulo,
            'mensagem' => $request->mensagem,
            'categoria' => $request->categoria,
        ]);

        return back()->with('success', 'Aviso criado com sucesso!');
    }

    // =========================
    // EDITAR AVISO (CARREGAR DADOS)
    // =========================
    public function edit($id)
    {
        $aviso = Aviso::findOrFail($id);

        return response()->json($aviso);
    }

    // =========================
    // ATUALIZAR AVISO
    // =========================
    public function update(Request $request, $id)
    {
        $request->validate([
            'titulo' => 'required',
            'mensagem' => 'required',
            'categoria' => 'required'
        ]);

        $aviso = Aviso::findOrFail($id);

        $aviso->update([
            'titulo' => $request->titulo,
            'mensagem' => $request->mensagem,
            'categoria' => $request->categoria,
        ]);

        return back()->with('success', 'Aviso atualizado!');
    }

    // =========================
    // EXCLUIR AVISO
    // =========================
    public function destroy($id)
    {
        $aviso = Aviso::findOrFail($id);
        $aviso->delete();

        return back()->with('success', 'Aviso excluído com sucesso!');
    }
}