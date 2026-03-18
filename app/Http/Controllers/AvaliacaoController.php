<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Avaliacao;
use App\Models\Pergunta;
use App\Models\Resposta;
use App\Models\Nota;
use App\Models\Aula; // 🔥 IMPORTANTE

class AvaliacaoController extends Controller
{
    // ------------------------
    // PROFESSOR - CRIAR AVALIAÇÃO
    // ------------------------
    public function create($aula)
    {
        // 🔥 CORREÇÃO AQUI (buscar aula no banco)
        $aula = Aula::findOrFail($aula);

        return view('dashboard.criar-avaliacao', compact('aula'));
    }

    // ------------------------
    // SALVAR AVALIAÇÃO
    // ------------------------
    public function store(Request $request)
    {
        $request->validate([
            'titulo' => 'required',
            'pergunta' => 'required'
        ]);

        // Criar avaliação
        $avaliacao = Avaliacao::create([
            'titulo' => $request->titulo,
            'aula_id' => $request->aula_id
        ]);

        // Criar pergunta
        $pergunta = Pergunta::create([
            'pergunta' => $request->pergunta,
            'avaliacao_id' => $avaliacao->id
        ]);

        // Criar respostas
        for ($i = 1; $i <= 4; $i++) {
            Resposta::create([
                'resposta' => $request->input("resposta$i"),
                'correta' => $request->correta == $i,
                'pergunta_id' => $pergunta->id
            ]);
        }

        return back()->with('success', 'Pós-teste criado com sucesso!');
    }

    // ------------------------
    // ALUNO - VISUALIZAR TESTE
    // ------------------------
    public function show($id)
    {
        $avaliacao = Avaliacao::with('perguntas.respostas')->findOrFail($id);

        return view('aluno.fazer-avaliacao', compact('avaliacao'));
    }

    // ------------------------
    // ALUNO - RESPONDER TESTE
    // ------------------------
    public function responder(Request $request)
    {
        $inicio = $request->inicio;
        $fim = now();

        // calcular tempo em segundos
        $tempo = strtotime($fim) - strtotime($inicio);

        $corretas = 0;

        if ($request->respostas) {
            foreach ($request->respostas as $resposta_id) {
                $resposta = Resposta::find($resposta_id);

                if ($resposta && $resposta->correta) {
                    $corretas++;
                }
            }
        }

        // cálculo simples de nota
        $nota = $corretas * 10;

        // salvar nota
        Nota::create([
            'aluno_id' => auth()->id(),
            'avaliacao_id' => $request->avaliacao_id,
            'nota' => $nota
        ]);

        return back()->with('success', "Nota: $nota | Tempo: $tempo segundos");
    }
}