<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AvaliacaoController extends Controller
{
    // =========================
    // CRIAR AVALIAÇÃO (NORMAL)
    // =========================
    public function create($aula)
    {
        return view('avaliacoes.create', compact('aula'));
    }

    // =========================
    // SALVAR AVALIAÇÃO NORMAL
    // =========================
    public function store(Request $request)
    {
        $avaliacaoId = DB::table('avaliacoes')->insertGetId([
            'titulo' => $request->avaliacao['titulo'],
            'tempo_limite' => $request->avaliacao['tempo_limite'],
            'tipo' => 'normal',
            'created_at' => now(),
        ]);

        foreach ($request->perguntas as $pergunta) {

            $perguntaId = DB::table('perguntas')->insertGetId([
                'avaliacao_id' => $avaliacaoId,
                'pergunta' => $pergunta['pergunta'],
                'created_at' => now(),
            ]);

            foreach ($pergunta['respostas'] as $index => $resposta) {
                DB::table('respostas')->insert([
                    'pergunta_id' => $perguntaId,
                    'resposta' => $resposta,
                    'correta' => ($pergunta['correta'] == $index) ? 1 : 0,
                ]);
            }
        }

        return back()->with('success', 'Avaliação criada com sucesso!');
    }

    // =========================
    // 🔥 CRIAR PROVA FINAL (ADMIN)
    // =========================
    public function createFinal()
    {
        return view('dashboard.prova-final-criar');
    }

    // =========================
    // 🔥 SALVAR PROVA FINAL (ADMIN)
    // =========================
    public function storeFinal(Request $request)
    {
        $avaliacaoId = DB::table('avaliacoes')->insertGetId([
            'titulo' => $request->titulo,
            'tempo_limite' => $request->tempo_limite,
            'tipo' => 'final',
            'created_at' => now(),
        ]);

        foreach ($request->perguntas as $pergunta) {

            $perguntaId = DB::table('perguntas')->insertGetId([
                'avaliacao_id' => $avaliacaoId,
                'pergunta' => $pergunta['pergunta'],
                'created_at' => now(),
            ]);

            foreach ($pergunta['respostas'] as $index => $resposta) {
                DB::table('respostas')->insert([
                    'pergunta_id' => $perguntaId,
                    'resposta' => $resposta,
                    'correta' => ($pergunta['correta'] == $index) ? 1 : 0,
                ]);
            }
        }

        return back()->with('success', 'Prova final criada com sucesso!');
    }

    // =========================
    // MOSTRAR AVALIAÇÃO NORMAL
    // =========================
    public function show($id)
    {
        $avaliacao = DB::table('avaliacoes')->where('id', $id)->first();

        $perguntas = DB::table('perguntas')
            ->where('avaliacao_id', $id)
            ->get();

        foreach ($perguntas as $pergunta) {
            $pergunta->respostas = DB::table('respostas')
                ->where('pergunta_id', $pergunta->id)
                ->get();
        }

        return view('avaliacoes.show', compact('avaliacao', 'perguntas'));
    }

    // =========================
    // RESPONDER AVALIAÇÃO NORMAL
    // =========================
    public function responder(Request $request, $id)
    {
        $perguntas = DB::table('perguntas')
            ->where('avaliacao_id', $id)
            ->get();

        $acertos = 0;

        foreach ($perguntas as $pergunta) {

            $respostaCerta = DB::table('respostas')
                ->where('pergunta_id', $pergunta->id)
                ->where('correta', 1)
                ->first();

            if ($request->respostas[$pergunta->id] == $respostaCerta->id) {
                $acertos++;
            }
        }

        $nota = ($acertos / count($perguntas)) * 10;

        return back()->with('success', "Você tirou nota {$nota}");
    }

    // =========================
    // 🔥 MOSTRAR PROVA FINAL (ALUNO)
    // =========================
    public function provaFinal()
    {
        $alunoId = auth()->id();

        $totalAulas = DB::table('aulas')->count();

        $assistidas = DB::table('aulas_assistidas')
            ->where('aluno_id', $alunoId)
            ->count();

        if ($assistidas < $totalAulas) {
            return back()->with('error', 'Você precisa concluir todas as aulas.');
        }

        $prova = DB::table('avaliacoes')
            ->where('tipo', 'final')
            ->first();

        if (!$prova) {
            return back()->with('error', 'Prova final ainda não foi criada.');
        }

        $perguntas = DB::table('perguntas')
            ->where('avaliacao_id', $prova->id)
            ->get();

        foreach ($perguntas as $pergunta) {
            $pergunta->respostas = DB::table('respostas')
                ->where('pergunta_id', $pergunta->id)
                ->get();
        }

        return view('dashboard.prova-final', compact('prova', 'perguntas'));
    }

    // =========================
    // 🔥 RESPONDER PROVA FINAL
    // =========================
    public function responderFinal(Request $request)
    {
        $avaliacaoId = $request->avaliacao_id;

        $perguntas = DB::table('perguntas')
            ->where('avaliacao_id', $avaliacaoId)
            ->get();

        $acertos = 0;

        foreach ($perguntas as $pergunta) {

            $respostaCerta = DB::table('respostas')
                ->where('pergunta_id', $pergunta->id)
                ->where('correta', 1)
                ->first();

            if (isset($request->respostas[$pergunta->id]) &&
                $request->respostas[$pergunta->id] == $respostaCerta->id) {
                $acertos++;
            }
        }

        $nota = ($acertos / count($perguntas)) * 10;

        DB::table('resultados')->insert([
            'aluno_id' => auth()->id(),
            'avaliacao_id' => $avaliacaoId,
            'nota' => $nota,
            'created_at' => now(),
        ]);

        if ($nota >= 7) {
            return redirect()->route('dashboard.aluno')
                ->with('success', "🎉 Aprovado com nota {$nota}");
        } else {
            return back()->with('error', "Reprovado. Nota: {$nota}");
        }
    }
}