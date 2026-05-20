<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Carbon;

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
            'avaliacao.tempo_minimo' => 'nullable|integer|min:0',
            'aula_id' => 'required|integer',
            'perguntas' => 'required|array|min:1',
        ]);

        $tempoMinimo = isset($request->avaliacao['tempo_minimo'])
            ? (int) $request->avaliacao['tempo_minimo']
            : 0;

        $tempoMaximo = isset($request->avaliacao['tempo_limite'])
            ? (int) $request->avaliacao['tempo_limite']
            : null;

        if ($tempoMaximo && $tempoMinimo > $tempoMaximo) {
            return back()
                ->withInput()
                ->with('error', 'O tempo mínimo não pode ser maior que o tempo máximo do pós-teste.');
        }

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

            if (Schema::hasColumn('avaliacoes', 'tempo_minimo')) {
                $dadosAvaliacao['tempo_minimo'] = $request->avaliacao['tempo_minimo'] ?? 0;
            }

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
    // PERGUNTAS E ALTERNATIVAS EMBARALHADAS POR ALUNO
    // =========================
    public function show($id)
    {
        $alunoId = auth()->id();

        if (!$alunoId) {
            return redirect()->route('login');
        }

        $avaliacao = DB::table('avaliacoes')->where('id', $id)->first();

        if (!$avaliacao) {
            return redirect()
                ->route('dashboard.aluno')
                ->with('error', 'Pós-teste não encontrado.');
        }

        $validacaoAula = $this->validarAulaAssistidaParaPosTeste($avaliacao, $alunoId);

        if ($validacaoAula !== true) {
            return redirect()
                ->route('aluno.aulas', ['aula_id' => $avaliacao->aula_id ?? null])
                ->with('error', $validacaoAula);
        }

        /*
        |--------------------------------------------------------------------------
        | REGISTRA O INÍCIO DO PÓS-TESTE NA SESSÃO
        |--------------------------------------------------------------------------
        | Isso permite validar tempo mínimo e tempo máximo no servidor.
        | Mesmo que o aluno tente burlar o JavaScript, o controller confere.
        */
        $chaveInicio = $this->chaveInicioAvaliacao($id, $alunoId);

        if (!session()->has($chaveInicio)) {
            session()->put($chaveInicio, now()->toDateTimeString());
        }

        /*
        |--------------------------------------------------------------------------
        | EMBARALHAMENTO
        |--------------------------------------------------------------------------
        | Cada aluno recebe uma ordem diferente.
        | O mesmo aluno mantém a mesma ordem se atualizar a página.
        */
        $perguntas = DB::table('perguntas')
            ->where('avaliacao_id', $id)
            ->get()
            ->sortBy(function ($pergunta) use ($alunoId, $id) {
                return md5('pergunta-' . $alunoId . '-' . $id . '-' . $pergunta->id);
            })
            ->values();

        foreach ($perguntas as $pergunta) {
            $pergunta->respostas = DB::table('respostas')
                ->where('pergunta_id', $pergunta->id)
                ->get()
                ->sortBy(function ($resposta) use ($alunoId, $id, $pergunta) {
                    return md5('resposta-' . $alunoId . '-' . $id . '-' . $pergunta->id . '-' . $resposta->id);
                })
                ->values();
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

        $avaliacao = DB::table('avaliacoes')
            ->where('id', $id)
            ->first();

        if (!$avaliacao) {
            return redirect()
                ->route('dashboard.aluno')
                ->with('error', 'Pós-teste não encontrado.');
        }

        $validacaoAula = $this->validarAulaAssistidaParaPosTeste($avaliacao, $alunoId);

        if ($validacaoAula !== true) {
            return redirect()
                ->route('aluno.aulas', ['aula_id' => $avaliacao->aula_id ?? null])
                ->with('error', $validacaoAula);
        }

        /*
        |--------------------------------------------------------------------------
        | VALIDA TEMPO MÍNIMO E TEMPO MÁXIMO
        |--------------------------------------------------------------------------
        | tempo_minimo e tempo_limite estão em minutos.
        | tempo_minimo só funciona se existir a coluna na tabela avaliacoes.
        */
        $validacaoTempo = $this->validarTempoAvaliacao($avaliacao, $id, $alunoId);

        if ($validacaoTempo !== true) {
            return back()->with('error', $validacaoTempo);
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

        session()->forget($this->chaveInicioAvaliacao($id, $alunoId));

        return redirect()
            ->route('dashboard.aluno')
            ->with('success', 'Você concluiu o pós-teste. Nota: ' . number_format($nota, 1));
    }

    // =========================
    // REINICIAR TEMPO DO PÓS-TESTE
    // =========================
    public function resetarTempo(Request $request, $id)
    {
        $alunoId = auth()->id();

        if (!$alunoId) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuário não autenticado.'
                ], 401);
            }

            return redirect()->route('login');
        }

        session()->forget($this->chaveInicioAvaliacao($id, $alunoId));

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Tempo do pós-teste reiniciado com sucesso.'
            ]);
        }

        return back()->with('success', 'Tempo do pós-teste reiniciado. Ao abrir novamente, o tempo começará do zero.');
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
            ->get()
            ->sortBy(function ($pergunta) use ($alunoId, $avaliacao) {
                return md5('final-pergunta-' . $alunoId . '-' . $avaliacao->id . '-' . $pergunta->id);
            })
            ->values();

        foreach ($perguntas as $pergunta) {
            $pergunta->respostas = DB::table('respostas')
                ->where('pergunta_id', $pergunta->id)
                ->get()
                ->sortBy(function ($resposta) use ($alunoId, $avaliacao, $pergunta) {
                    return md5('final-resposta-' . $alunoId . '-' . $avaliacao->id . '-' . $pergunta->id . '-' . $resposta->id);
                })
                ->values();
        }

        $avaliacao->perguntas = $perguntas;

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
            ->orderBy('id', 'desc')
            ->first();

        if ($notaExistente) {
            $notaAtual = isset($notaExistente->nota) ? (float) $notaExistente->nota : 0;
            $melhorNota = max($notaAtual, (float) $nota);

            DB::table('notas')
                ->where('id', $notaExistente->id)
                ->update([
                    'nota' => $melhorNota,
                    'updated_at' => now(),
                ]);

            return;
        }

        DB::table('notas')->insert([
            'aluno_id' => $alunoId,
            'avaliacao_id' => $avaliacaoId,
            'nota' => $nota,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
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

    // =========================
    // FUNÇÃO AUXILIAR: CHAVE DE INÍCIO DA AVALIAÇÃO
    // =========================
    private function chaveInicioAvaliacao($avaliacaoId, $alunoId)
    {
        return 'avaliacao_inicio_' . $avaliacaoId . '_aluno_' . $alunoId;
    }


    // =========================
    // FUNÇÃO AUXILIAR: VALIDAR SE A AULA FOI ASSISTIDA ANTES DO PÓS-TESTE
    // =========================
    private function validarAulaAssistidaParaPosTeste($avaliacao, $alunoId)
    {
        if (!isset($avaliacao->aula_id) || !$avaliacao->aula_id) {
            return true;
        }

        if (!Schema::hasTable('aulas_assistidas')) {
            return 'Não foi possível confirmar se a videoaula foi assistida. Procure o administrador.';
        }

        $assistidaQuery = DB::table('aulas_assistidas')
            ->where('aluno_id', $alunoId)
            ->where('aula_id', $avaliacao->aula_id);

        if (Schema::hasColumn('aulas_assistidas', 'assistido')) {
            $assistidaQuery->where('assistido', true);
        }

        if (!$assistidaQuery->exists()) {
            return 'Você precisa assistir a videoaula pelo tempo mínimo definido antes de fazer o pós-teste.';
        }

        return true;
    }

    // =========================
    // FUNÇÃO AUXILIAR: VALIDAR TEMPO MÍNIMO E TEMPO MÁXIMO
    // =========================
    private function validarTempoAvaliacao($avaliacao, $avaliacaoId, $alunoId)
    {
        $chaveInicio = $this->chaveInicioAvaliacao($avaliacaoId, $alunoId);

        if (!session()->has($chaveInicio)) {
            session()->put($chaveInicio, now()->toDateTimeString());
            return 'Não foi possível confirmar o tempo de início do pós-teste. Abra o pós-teste novamente e tente finalizar depois.';
        }

        $inicio = Carbon::parse(session()->get($chaveInicio));
        $agora = now();

        $segundosDecorridos = $inicio->diffInSeconds($agora);
        $minutosDecorridos = floor($segundosDecorridos / 60);

        $tempoMinimo = 0;

        if (Schema::hasColumn('avaliacoes', 'tempo_minimo') && isset($avaliacao->tempo_minimo)) {
            $tempoMinimo = (int) $avaliacao->tempo_minimo;
        }

        $tempoMaximo = isset($avaliacao->tempo_limite) && $avaliacao->tempo_limite
            ? (int) $avaliacao->tempo_limite
            : 0;

        if ($tempoMinimo > 0 && $segundosDecorridos < ($tempoMinimo * 60)) {
            $faltamSegundos = ($tempoMinimo * 60) - $segundosDecorridos;
            $faltamMinutos = ceil($faltamSegundos / 60);

            return 'Você precisa permanecer pelo menos ' . $tempoMinimo . ' minuto(s) no pós-teste antes de finalizar. Aguarde mais aproximadamente ' . $faltamMinutos . ' minuto(s).';
        }

        /*
        |--------------------------------------------------------------------------
        | TEMPO MÁXIMO
        |--------------------------------------------------------------------------
        | Damos uma tolerância de 60 segundos para evitar erro por atraso de rede.
        */
        if ($tempoMaximo > 0 && $segundosDecorridos > (($tempoMaximo * 60) + 60)) {
            session()->forget($chaveInicio);

            return 'O tempo máximo do pós-teste foi ultrapassado. Abra novamente e tente responder dentro do tempo definido.';
        }

        return true;
    }
}