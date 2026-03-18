<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Aula;

class AulaController extends Controller
{
    // LISTAR AULAS
    public function index()
    {
        $aulas = Aula::orderBy('id', 'desc')->get();
        return view('dashboard.videoaulas', compact('aulas'));
    }

    // MOSTRAR TELA DE CRIAR
    public function create()
    {
        return view('dashboard.criar-aula');
    }

    // SALVAR NO BANCO
    public function store(Request $request)
    {
        $request->validate([
            'titulo' => 'required',
            'descricao' => 'required',
            'video_url' => 'required'
        ]);

        Aula::create([
            'titulo' => $request->titulo,
            'descricao' => $request->descricao,
            'video_url' => $request->video_url
        ]);

        return redirect()->route('videoaulas')
            ->with('success', 'Aula criada com sucesso!');
    }
}