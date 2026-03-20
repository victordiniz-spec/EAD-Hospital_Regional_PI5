<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Avaliacao;
use App\Models\Pergunta;
use App\Models\Resposta;
use App\Models\Nota;
use App\Models\Aula;

class AvaliacaoController extends Controller
{
    // ------------------------
    // PROFESSOR - CRIAR AVALIAÇÃO
    // ------------------------
    public function create($aula)
    {
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
            'pergunta' => 'required',
        ]);

        $avaliacao = Avaliacao::create([
            'titulo' => $request->titulo,
            'aula_id' => $request->aula_id,
        ]);

        $pergunta = Pergunta::create([
            'pergunta' => $request->pergunta,
            'avaliacao_id' => $avaliacao->id,
        ]);

        for ($i = 1; $i <= 4; $i++) {
            Resposta::create([
                'resposta' => $request->input("resposta$i"),
                'correta' => $request->correta == $i,
                'pergunta_id' => $pergunta->id,
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
        $alunoId = auth()->id();

        // Verificar se o aluno assistiu à aula
        $assistiu = DB::table('aulas_assistidas')
            ->where('aluno_id', $alunoId)
            ->where('aula_id', $avaliacao->aula_id)
            ->exists();

        if (!$assistiu) {
            return redirect()->back()->with('error', 'Você precisa assistir a aula antes de fazer o teste!');
        }

        return view('aluno.fazer-avaliacao', compact('avaliacao'));
    }

    // ------------------------
    // ALUNO - RESPONDER TESTE
    // ------------------------
    public function responder(Request $request, $id)
    {
        $avaliacao = Avaliacao::findOrFail($id);
        $respostasSelecionadas = $request->input('resposta', []);
        $corretas = 0;

        foreach ($respostasSelecionadas as $pergunta_id => $resposta_id) {
            $resposta = Resposta::find($resposta_id);
            if ($resposta && $resposta->correta) {
                $corretas++;
            }
        }

        $nota = $corretas * 10; // ou sua lógica de nota

        Nota::create([
            'aluno_id' => auth()->id(),
            'avaliacao_id' => $avaliacao->id,
            'nota' => $nota,
        ]);

        return redirect()->route('dashboard.aluno')->with('success', "Nota: $nota");
    }
}