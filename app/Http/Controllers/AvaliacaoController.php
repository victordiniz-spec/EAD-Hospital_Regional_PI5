<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AvaliacaoController extends Controller
{
    // =========================
    // CRIAR / EDITAR AVALIAÇÃO NORMAL / PÓS-TESTE
    // =========================
    public function create($aula)
    {
        try {
            $aulaDados = DB::table('aulas')
                ->where('id', $aula)
                ->first();

            if (!$aulaDados) {
                return redirect()
                    ->route('videoaulas')
                    ->with('error', 'Aula não encontrada para criar ou editar o pós-teste.');
            }

            $avaliacao = DB::table('avaliacoes')
                ->where('aula_id', $aula)
                ->where(function ($query) {
                    $query->where('tipo', 'normal')
                          ->orWhere('tipo', 'pos_teste')
                          ->orWhere('tipo', 'pós-teste')
                          ->orWhereNull('tipo');
                })
                ->first();

            $perguntas = collect();

            if ($avaliacao) {
                $perguntas = DB::table('perguntas')
                    ->where('avaliacao_id', $avaliacao->id)
                    ->orderBy('id')
                    ->get();

                foreach ($perguntas as $pergunta) {
                    $pergunta->respostas = DB::table('respostas')
                        ->where('pergunta_id', $pergunta->id)
                        ->orderBy('id')
                        ->get();
                }
            }

            return view('avaliacoes.create', [
                'aula' => $aula,
                'aulaDados' => $aulaDados,
                'avaliacao' => $avaliacao,
                'perguntas' => $perguntas,
            ]);

        } catch (\Throwable $e) {
            return redirect()
                ->route('videoaulas')
                ->with('error',
                    'Erro ao abrir pós-teste: ' . $e->getMessage() .
                    ' | Arquivo: ' . $e->getFile() .
                    ' | Linha: ' . $e->getLine()
                );
        }
    }

    // =========================
    // SALVAR / ATUALIZAR AVALIAÇÃO NORMAL / PÓS-TESTE
    // =========================
    public function store(Request $request)
    {
        $request->validate([
            'avaliacao.titulo' => 'required|string|max:255',
            'avaliacao.tempo_limite' => 'nullable|integer|min:1',
            'aula_id' => 'required|integer',
            'perguntas' => 'required|array|min:1',
        ]);

        DB::beginTransaction();

        try {
            $aulaId = $request->aula_id;

            $avaliacaoExistente = DB::table('avaliacoes')
                ->where('aula_id', $aulaId)
                ->where(function ($query) {
                    $query->where('tipo', 'normal')
                          ->orWhere('tipo', 'pos_teste')
                          ->orWhere('tipo', 'pós-teste')
                          ->orWhereNull('tipo');
                })
                ->first();

            $dadosAvaliacao = [
                'titulo' => $request->avaliacao['titulo'],
                'tempo_limite' => $request->avaliacao['tempo_limite'] ?? null,
                'qtd_perguntas' => count($request->perguntas ?? []),
                'tipo' => 'normal',
                'updated_at' => now(),
            ];

            if ($avaliacaoExistente) {
                $avaliacaoId = $avaliacaoExistente->id;

                DB::table('avaliacoes')
                    ->where('id', $avaliacaoId)
                    ->update($dadosAvaliacao);

                $this->apagarPerguntasRespostasDaAvaliacao($avaliacaoId);
            } else {
                $dadosAvaliacao['aula_id'] = $aulaId;
                $dadosAvaliacao['created_at'] = now();

                $avaliacaoId = DB::table('avaliacoes')->insertGetId($dadosAvaliacao);
            }

            $this->salvarPerguntasRespostas($avaliacaoId, $request->perguntas);

            DB::commit();

            return redirect()
                ->route('videoaulas', ['curso_id' => $request->curso_id])
                ->with('success', 'Pós-teste salvo com sucesso!');

        } catch (\Throwable $e) {
            DB::rollBack();

            return back()
                ->withInput()
                ->with('error',
                    'Erro ao salvar pós-teste: ' . $e->getMessage() .
                    ' | Arquivo: ' . $e->getFile() .
                    ' | Linha: ' . $e->getLine()
                );
        }
    }

    // =========================
    // CRIAR / EDITAR PROVA FINAL
    // =========================
    public function createFinal()
    {
        return view('dashboard.prova-final-criar');
    }

    // =========================
    // SALVAR / ATUALIZAR PROVA FINAL
    // =========================
    public function storeFinal(Request $request)
    {
        $request->validate([
            'titulo' => 'required|string|max:255',
            'tempo_limite' => 'nullable|integer|min:1',
            'nota_minima' => 'nullable|numeric|min:0|max:100',
            'tentativas' => 'nullable|integer|min:1',
            'perguntas' => 'required|array|min:1',
        ], [
            'titulo.required' => 'Informe o título da prova final.',
            'perguntas.required' => 'Adicione pelo menos uma pergunta na prova final.',
        ]);

        DB::beginTransaction();

        try {
            /*
            |--------------------------------------------------------------------------
            | IMPORTANTE
            |--------------------------------------------------------------------------
            | Antes o sistema sempre fazia INSERT de uma nova prova final.
            | A tela do aluno buscava a primeira prova final encontrada.
            | Resultado: você atualizava, aparecia sucesso, mas o aluno continuava vendo
            | a prova antiga.
            |
            | Agora:
            | - se veio avaliacao_id, atualiza aquela prova;
            | - se não veio, procura uma prova final existente;
            | - se não existir nenhuma, cria uma nova.
            */

            $avaliacaoFinal = null;

            if ($request->filled('avaliacao_id')) {
                $avaliacaoFinal = DB::table('avaliacoes')
                    ->where('id', $request->avaliacao_id)
                    ->where('tipo', 'final')
                    ->first();
            }

            if (!$avaliacaoFinal) {
                $avaliacaoFinal = DB::table('avaliacoes')
                    ->where('tipo', 'final')
                    ->orderBy('id', 'desc')
                    ->first();
            }

            $dadosAvaliacao = [
                'titulo' => $request->titulo,
                'tempo_limite' => $request->tempo_limite ?? 60,
                'tipo' => 'final',
                'qtd_perguntas' => count($request->perguntas ?? []),
                'updated_at' => now(),
            ];

            // Só grava esses campos se existirem na tabela, para evitar erro 500.
            if (Schema::hasColumn('avaliacoes', 'nota_minima')) {
                $dadosAvaliacao['nota_minima'] = $request->nota_minima ?? 70;
            }

            if (Schema::hasColumn('avaliacoes', 'tentativas')) {
                $dadosAvaliacao['tentativas'] = $request->tentativas ?? 2;
            }

            if ($avaliacaoFinal) {
                $avaliacaoId = $avaliacaoFinal->id;

                DB::table('avaliacoes')
                    ->where('id', $avaliacaoId)
                    ->update($dadosAvaliacao);

                // Remove as perguntas antigas para salvar exatamente o que está no formulário.
                $this->apagarPerguntasRespostasDaAvaliacao($avaliacaoId);

                $mensagem = 'Prova final atualizada com sucesso!';
            } else {
                $dadosAvaliacao['created_at'] = now();

                $avaliacaoId = DB::table('avaliacoes')->insertGetId($dadosAvaliacao);

                $mensagem = 'Prova final criada com sucesso!';
            }

            $this->salvarPerguntasRespostas($avaliacaoId, $request->perguntas);

            DB::commit();

            return redirect()
                ->route('prova.final.criar')
                ->with('success', $mensagem);

        } catch (\Throwable $e) {
            DB::rollBack();

            return back()
                ->withInput()
                ->with('error',
                    'Erro ao salvar prova final: ' . $e->getMessage() .
                    ' | Arquivo: ' . $e->getFile() .
                    ' | Linha: ' . $e->getLine()
                );
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

        $this->salvarNota($alunoId, $id, $nota);
        $this->salvarRespostasAluno($alunoId, $id, $perguntas, $request->respostas ?? []);

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

        if (Schema::hasTable('notas')) {
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

            if (Schema::hasTable('respostas_alunos')) {
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

        /*
        |--------------------------------------------------------------------------
        | BUSCAR A PROVA FINAL CORRETA
        |--------------------------------------------------------------------------
        | Antes usava first(), então muitas vezes carregava a prova antiga.
        | Agora usa a mais recente, que é a que foi criada/atualizada por último.
        */

        $avaliacao = DB::table('avaliacoes')
            ->where('tipo', 'final')
            ->orderBy('updated_at', 'desc')
            ->orderBy('id', 'desc')
            ->first();

        if (!$avaliacao) {
            return redirect()
                ->route('dashboard.aluno')
                ->with('error', 'Prova final ainda não foi criada.');
        }

        $perguntas = DB::table('perguntas')
            ->where('avaliacao_id', $avaliacao->id)
            ->orderBy('id')
            ->get();

        foreach ($perguntas as $pergunta) {
            $pergunta->respostas = DB::table('respostas')
                ->where('pergunta_id', $pergunta->id)
                ->orderBy('id')
                ->get();
        }

        $avaliacao->perguntas = $perguntas;

        // A regra de 70% do curso atual está dentro da view dashboard.prova-final.
        // O parâmetro ?teste=123 também é tratado na view.
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

        /*
        |--------------------------------------------------------------------------
        | SEGURANÇA
        |--------------------------------------------------------------------------
        | Se o formulário não mandar avaliacao_id, usamos a prova final mais recente.
        */

        $avaliacaoId = $request->avaliacao_id;

        if (!$avaliacaoId) {
            $avaliacaoId = DB::table('avaliacoes')
                ->where('tipo', 'final')
                ->orderBy('updated_at', 'desc')
                ->orderBy('id', 'desc')
                ->value('id');
        }

        if (!$avaliacaoId) {
            return redirect()
                ->route('dashboard.aluno')
                ->with('error', 'Prova final não encontrada.');
        }

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

        $this->salvarNota($alunoId, $avaliacaoId, $nota);
        $this->salvarRespostasAluno($alunoId, $avaliacaoId, $perguntas, $request->respostas ?? []);

        if ($nota >= 7) {
            return redirect()
                ->route('dashboard.aluno')
                ->with('success', '🎉 Aprovado com nota ' . number_format($nota, 1));
        }

        return redirect()
            ->route('dashboard.aluno')
            ->with('error', 'Reprovado. Nota: ' . number_format($nota, 1));
    }

    // =========================
    // FUNÇÃO AUXILIAR: APAGAR PERGUNTAS E RESPOSTAS
    // =========================
    private function apagarPerguntasRespostasDaAvaliacao($avaliacaoId)
    {
        $perguntasAntigas = DB::table('perguntas')
            ->where('avaliacao_id', $avaliacaoId)
            ->pluck('id')
            ->toArray();

        if (empty($perguntasAntigas)) {
            return;
        }

        if (Schema::hasTable('respostas_alunos')) {
            DB::table('respostas_alunos')
                ->whereIn('pergunta_id', $perguntasAntigas)
                ->delete();
        }

        DB::table('respostas')
            ->whereIn('pergunta_id', $perguntasAntigas)
            ->delete();

        DB::table('perguntas')
            ->where('avaliacao_id', $avaliacaoId)
            ->delete();
    }

    // =========================
    // FUNÇÃO AUXILIAR: SALVAR PERGUNTAS E RESPOSTAS
    // =========================
    private function salvarPerguntasRespostas($avaliacaoId, $perguntas)
    {
        foreach ($perguntas as $pergunta) {
            if (empty($pergunta['pergunta'])) {
                continue;
            }

            $dadosPergunta = [
                'avaliacao_id' => $avaliacaoId,
                'pergunta' => $pergunta['pergunta'],
                'created_at' => now(),
                'updated_at' => now(),
            ];

            if (Schema::hasColumn('perguntas', 'peso')) {
                $dadosPergunta['peso'] = $pergunta['peso'] ?? 10;
            }

            $perguntaId = DB::table('perguntas')->insertGetId($dadosPergunta);

            $respostas = $pergunta['respostas'] ?? [];
            $correta = isset($pergunta['correta']) ? (int) $pergunta['correta'] : null;

            foreach ($respostas as $index => $resposta) {
                if ($resposta === null || trim((string) $resposta) === '') {
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
    }

    // =========================
    // FUNÇÃO AUXILIAR: SALVAR NOTA
    // =========================
    private function salvarNota($alunoId, $avaliacaoId, $nota)
    {
        if (!Schema::hasTable('notas')) {
            return;
        }

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

    // =========================
    // FUNÇÃO AUXILIAR: SALVAR RESPOSTAS DO ALUNO
    // =========================
    private function salvarRespostasAluno($alunoId, $avaliacaoId, $perguntas, $respostasAluno)
    {
        if (!Schema::hasTable('respostas_alunos')) {
            return;
        }

        foreach ($perguntas as $pergunta) {
            if (!isset($respostasAluno[$pergunta->id])) {
                continue;
            }

            DB::table('respostas_alunos')->updateOrInsert(
                [
                    'aluno_id' => $alunoId,
                    'avaliacao_id' => $avaliacaoId,
                    'pergunta_id' => $pergunta->id,
                ],
                [
                    'resposta_id' => $respostasAluno[$pergunta->id],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
