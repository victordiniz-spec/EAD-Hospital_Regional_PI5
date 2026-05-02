<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AvaliacaoController extends Controller
{
    // =========================
    // CRIAR AVALIAÇÃO NORMAL
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
        $request->validate([
            'avaliacao.titulo' => 'required|string|max:255',
            'avaliacao.tempo_limite' => 'nullable|integer|min:1',
            'aula_id' => 'nullable|integer',
            'perguntas' => 'required|array',
        ]);

        DB::beginTransaction();

        try {
            $avaliacaoId = DB::table('avaliacoes')->insertGetId([
                'titulo' => $request->avaliacao['titulo'],
                'aula_id' => $request->aula_id ?? null,
                'tempo_limite' => $request->avaliacao['tempo_limite'] ?? null,
                'qtd_perguntas' => count($request->perguntas ?? []),
                'tipo' => 'normal',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            foreach ($request->perguntas as $pergunta) {
                if (empty($pergunta['pergunta'])) {
                    continue;
                }

                $perguntaId = DB::table('perguntas')->insertGetId([
                    'avaliacao_id' => $avaliacaoId,
                    'pergunta' => $pergunta['pergunta'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $respostas = $pergunta['respostas'] ?? [];
                $correta = isset($pergunta['correta']) ? (int) $pergunta['correta'] : null;

                foreach ($respostas as $index => $resposta) {
                    if (empty($resposta)) {
                        continue;
                    }

                    DB::table('respostas')->insert([
                        'pergunta_id' => $perguntaId,
                        'resposta' => $resposta,
                        'correta' => $correta === (int) $index,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            DB::commit();

            return back()->with('success', 'Avaliação criada com sucesso!');

        } catch (\Throwable $e) {
            DB::rollBack();

            return back()
                ->withInput()
                ->with('error', 'Erro ao criar avaliação: ' . $e->getMessage());
        }
    }

    // =========================
    // CRIAR PROVA FINAL
    // =========================
    public function createFinal()
    {
        return view('dashboard.prova-final-criar');
    }

    // =========================
    // SALVAR PROVA FINAL
    // =========================
    public function storeFinal(Request $request)
    {
        $request->validate([
            'titulo' => 'required|string|max:255',
            'tempo_limite' => 'nullable|integer|min:1',
            'perguntas' => 'required|array',
        ]);

        DB::beginTransaction();

        try {
            $avaliacaoId = DB::table('avaliacoes')->insertGetId([
                'titulo' => $request->titulo,
                'tempo_limite' => $request->tempo_limite,
                'tipo' => 'final',
                'qtd_perguntas' => count($request->perguntas ?? []),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            foreach ($request->perguntas as $pergunta) {
                if (empty($pergunta['pergunta'])) {
                    continue;
                }

                $perguntaId = DB::table('perguntas')->insertGetId([
                    'avaliacao_id' => $avaliacaoId,
                    'pergunta' => $pergunta['pergunta'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $respostas = $pergunta['respostas'] ?? [];
                $correta = isset($pergunta['correta']) ? (int) $pergunta['correta'] : null;

                foreach ($respostas as $index => $resposta) {
                    if (empty($resposta)) {
                        continue;
                    }

                    DB::table('respostas')->insert([
                        'pergunta_id' => $perguntaId,
                        'resposta' => $resposta,
                        'correta' => $correta === (int) $index,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            DB::commit();

            return back()->with('success', 'Prova final criada com sucesso!');

        } catch (\Throwable $e) {
            DB::rollBack();

            return back()
                ->withInput()
                ->with('error', 'Erro ao criar prova final: ' . $e->getMessage());
        }
    }

    // =========================
    // MOSTRAR AVALIAÇÃO NORMAL / PÓS-TESTE
    // =========================
    public function show($id)
    {
        $avaliacao = DB::table('avaliacoes')->where('id', $id)->first();

        if (!$avaliacao) {
            return redirect()
                ->route('dashboard.aluno')
                ->with('error', 'Pós-teste não encontrado.');
        }

        $perguntas = DB::table('perguntas')
            ->where('avaliacao_id', $id)
            ->orderBy('id')
            ->get();

        foreach ($perguntas as $pergunta) {
            $pergunta->respostas = DB::table('respostas')
                ->where('pergunta_id', $pergunta->id)
                ->orderBy('id')
                ->get();
        }

        return view('avaliacoes.show', compact('avaliacao', 'perguntas'));
    }

    // =========================
    // RESPONDER AVALIAÇÃO NORMAL / PÓS-TESTE
    // =========================
    public function responder(Request $request, $id)
    {
        $alunoId = auth()->id();

        if (!$alunoId) {
            return redirect()->route('login');
        }

        $perguntas = DB::table('perguntas')
            ->where('avaliacao_id', $id)
            ->get();

        if ($perguntas->count() === 0) {
            return redirect()
                ->route('dashboard.aluno')
                ->with('error', 'Este pós-teste ainda não possui perguntas cadastradas.');
        }

        $acertos = 0;

        foreach ($perguntas as $pergunta) {
            $respostaCerta = DB::table('respostas')
                ->where('pergunta_id', $pergunta->id)
                ->where('correta', true)
                ->first();

            if (
                isset($request->respostas[$pergunta->id]) &&
                $respostaCerta &&
                (int) $request->respostas[$pergunta->id] === (int) $respostaCerta->id
            ) {
                $acertos++;
            }
        }

        $nota = ($acertos / $perguntas->count()) * 10;

        // Salva ou atualiza nota do aluno
        if (DB::getSchemaBuilder()->hasTable('notas')) {
            $notaExistente = DB::table('notas')
                ->where('aluno_id', $alunoId)
                ->where('avaliacao_id', $id)
                ->first();

            if ($notaExistente) {
                DB::table('notas')
                    ->where('id', $notaExistente->id)
                    ->update([
                        'nota' => $nota,
                        'updated_at' => now(),
                    ]);
            } else {
                DB::table('notas')->insert([
                    'aluno_id' => $alunoId,
                    'avaliacao_id' => $id,
                    'nota' => $nota,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // Salva as respostas escolhidas pelo aluno
        if (DB::getSchemaBuilder()->hasTable('respostas_alunos')) {
            foreach ($perguntas as $pergunta) {
                if (isset($request->respostas[$pergunta->id])) {
                    DB::table('respostas_alunos')->updateOrInsert(
                        [
                            'aluno_id' => $alunoId,
                            'avaliacao_id' => $id,
                            'pergunta_id' => $pergunta->id,
                        ],
                        [
                            'resposta_id' => $request->respostas[$pergunta->id],
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]
                    );
                }
            }
        }

        return redirect()
            ->route('dashboard.aluno')
            ->with('success', 'Você concluiu o pós-teste. Nota: ' . number_format($nota, 1));
    }

    // =========================
    // RESULTADO DO PÓS-TESTE
    // =========================
    public function resultado($id)
    {
        $alunoId = auth()->id();

        if (!$alunoId) {
            return response()->json([
                'success' => false,
                'message' => 'Usuário não autenticado.'
            ], 401);
        }

        $avaliacao = DB::table('avaliacoes')
            ->where('id', $id)
            ->first();

        if (!$avaliacao) {
            return response()->json([
                'success' => false,
                'message' => 'Avaliação não encontrada.'
            ], 404);
        }

        $nota = null;

        if (DB::getSchemaBuilder()->hasTable('notas')) {
            $nota = DB::table('notas')
                ->where('aluno_id', $alunoId)
                ->where('avaliacao_id', $id)
                ->value('nota');
        }

        $perguntas = DB::table('perguntas')
            ->where('avaliacao_id', $id)
            ->orderBy('id')
            ->get();

        foreach ($perguntas as $pergunta) {
            $respostas = DB::table('respostas')
                ->where('pergunta_id', $pergunta->id)
                ->orderBy('id')
                ->get();

            $respostaAlunoId = null;

            if (DB::getSchemaBuilder()->hasTable('respostas_alunos')) {
                $respostaAlunoId = DB::table('respostas_alunos')
                    ->where('aluno_id', $alunoId)
                    ->where('avaliacao_id', $id)
                    ->where('pergunta_id', $pergunta->id)
                    ->value('resposta_id');
            }

            $pergunta->respostas = $respostas;
            $pergunta->resposta_aluno_id = $respostaAlunoId;
        }

        return response()->json([
            'success' => true,
            'avaliacao' => $avaliacao,
            'nota' => $nota,
            'perguntas' => $perguntas,
        ]);
    }

    // =========================
    // MOSTRAR PROVA FINAL
    // =========================
    public function provaFinal(Request $request)
    {
        $alunoId = auth()->id();

        if (!$alunoId) {
            return redirect()->route('login');
        }

        $modoTeste = $request->query('teste', false);

        $avaliacao = DB::table('avaliacoes')
            ->where('tipo', 'final')
            ->first();

        if (!$avaliacao) {
            return redirect()
                ->route('dashboard.aluno')
                ->with('error', 'Prova final ainda não foi criada.');
        }

        $totalAulas = DB::table('aulas')->count();

        $assistidas = DB::table('aulas_assistidas')
            ->where('aluno_id', $alunoId)
            ->where('assistido', true)
            ->count();

        if (!$modoTeste && $totalAulas > 0 && $assistidas < $totalAulas) {
            return redirect()
                ->route('dashboard.aluno')
                ->with('error', 'Você precisa concluir todas as aulas.');
        }

        $avaliacao->perguntas = DB::table('perguntas')
            ->where('avaliacao_id', $avaliacao->id)
            ->orderBy('id')
            ->get();

        foreach ($avaliacao->perguntas as $pergunta) {
            $pergunta->respostas = DB::table('respostas')
                ->where('pergunta_id', $pergunta->id)
                ->orderBy('id')
                ->get();
        }

        return view('dashboard.prova-final', compact('avaliacao'));
    }

    // =========================
    // RESPONDER PROVA FINAL
    // =========================
    public function responderFinal(Request $request)
    {
        $alunoId = auth()->id();

        if (!$alunoId) {
            return redirect()->route('login');
        }

        $avaliacaoId = $request->avaliacao_id;

        $perguntas = DB::table('perguntas')
            ->where('avaliacao_id', $avaliacaoId)
            ->get();

        if ($perguntas->count() === 0) {
            return redirect()
                ->route('dashboard.aluno')
                ->with('error', 'A prova final ainda não possui perguntas cadastradas.');
        }

        $acertos = 0;

        foreach ($perguntas as $pergunta) {
            $respostaCerta = DB::table('respostas')
                ->where('pergunta_id', $pergunta->id)
                ->where('correta', true)
                ->first();

            if (
                isset($request->respostas[$pergunta->id]) &&
                $respostaCerta &&
                (int) $request->respostas[$pergunta->id] === (int) $respostaCerta->id
            ) {
                $acertos++;
            }
        }

        $nota = ($acertos / $perguntas->count()) * 10;

        if (DB::getSchemaBuilder()->hasTable('notas')) {
            $notaExistente = DB::table('notas')
                ->where('aluno_id', $alunoId)
                ->where('avaliacao_id', $avaliacaoId)
                ->first();

            if ($notaExistente) {
                DB::table('notas')
                    ->where('id', $notaExistente->id)
                    ->update([
                        'nota' => $nota,
                        'updated_at' => now(),
                    ]);
            } else {
                DB::table('notas')->insert([
                    'aluno_id' => $alunoId,
                    'avaliacao_id' => $avaliacaoId,
                    'nota' => $nota,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        if ($nota >= 7) {
            return redirect()
                ->route('dashboard.aluno')
                ->with('success', '🎉 Aprovado com nota ' . number_format($nota, 1));
        }

        return redirect()
            ->route('dashboard.aluno')
            ->with('error', 'Reprovado. Nota: ' . number_format($nota, 1));
    }
}