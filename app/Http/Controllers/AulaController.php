<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use App\Models\Curso;
use App\Models\Aula;
use App\Models\Avaliacao;
use App\Models\Pergunta;
use App\Models\Resposta;
use App\Models\Modulo;

class AulaController extends Controller
{
    // =========================
    // 📺 LISTA DE AULAS / CURSOS (PROFESSOR)
    // =========================
    public function index(Request $request)
    {
        $cursos = Curso::orderBy('id', 'desc')->get();

        if ($cursos->count() === 0) {
            $curso = Curso::create([
                'nome' => 'Curso Principal',
                'descricao' => 'Curso padrão do sistema',
                'professor_id' => auth()->id(),
            ]);

            $cursos = Curso::orderBy('id', 'desc')->get();
            $cursoAtual = $curso;
        } else {
            $cursoAtual = null;

            if ($request->filled('curso_id')) {
                $cursoAtual = Curso::where('id', $request->curso_id)->first();
            }

            if (!$cursoAtual) {
                $cursoAtual = $cursos->first();
            }
        }

        $modulos = Modulo::where('curso_id', $cursoAtual->id)
            ->orderBy('ordem')
            ->orderBy('id')
            ->get();

        $aulas = Aula::with('modulo')
            ->where('curso_id', $cursoAtual->id)
            ->orderBy('modulo_id')
            ->orderBy('id')
            ->get();

        return view('dashboard.videoaulas', compact(
            'cursos',
            'cursoAtual',
            'aulas',
            'modulos'
        ));
    }

    // =========================
    // 📚 BIBLIOTECA DE CURSOS
    // =========================
    public function bibliotecaCursos(Request $request)
    {
        $query = Curso::with(['modulos.aulas'])
            ->orderBy('id', 'desc');

        if ($request->filled('pesquisa')) {
            $pesquisa = $request->pesquisa;

            $query->where(function ($q) use ($pesquisa) {
                $q->where('nome', 'like', "%{$pesquisa}%")
                    ->orWhere('descricao', 'like', "%{$pesquisa}%");
            });
        }

        if ($request->filled('data_inicio')) {
            $query->whereDate('created_at', '>=', $request->data_inicio);
        }

        if ($request->filled('data_fim')) {
            $query->whereDate('created_at', '<=', $request->data_fim);
        }

        $cursos = $query->get();

        return view('dashboard.biblioteca-cursos', compact('cursos'));
    }

    // =========================
    // 🔁 USAR CURSO NOVAMENTE
    // =========================
    public function duplicarCurso($id)
    {
        DB::beginTransaction();

        try {
            $cursoOriginal = Curso::findOrFail($id);

            $novoCurso = Curso::create([
                'nome' => $cursoOriginal->nome . ' - Cópia ' . now()->format('d/m/Y H:i'),
                'descricao' => $cursoOriginal->descricao,
                'professor_id' => auth()->id(),
            ]);

            $this->copiarEstruturaCurso($cursoOriginal->id, $novoCurso->id);

            DB::commit();

            return redirect()
                ->route('videoaulas', ['curso_id' => $novoCurso->id])
                ->with('success', 'Curso duplicado com sucesso! Agora você pode editar e reutilizar o conteúdo.');

        } catch (\Throwable $e) {
            DB::rollBack();

            return back()
                ->with('error', 'Erro ao duplicar curso: ' . $e->getMessage());
        }
    }

    // =========================
    // 🧩 DUPLICAR APENAS UM MÓDULO
    // =========================
    public function duplicarModulo(Request $request, $id)
    {
        $request->validate([
            'curso_destino_id' => 'required|integer',
        ]);

        DB::beginTransaction();

        try {
            $moduloOriginal = Modulo::findOrFail($id);
            $cursoDestino = Curso::findOrFail($request->curso_destino_id);

            $ultimaOrdem = Modulo::where('curso_id', $cursoDestino->id)->max('ordem') ?? 0;

            $novoModulo = Modulo::create([
                'nome' => $moduloOriginal->nome . ' - Importado',
                'curso_id' => $cursoDestino->id,
                'ordem' => $ultimaOrdem + 1,
            ]);

            $aulasOriginais = Aula::where('modulo_id', $moduloOriginal->id)
                ->orderBy('id')
                ->get();

            foreach ($aulasOriginais as $aulaOriginal) {
                $novaAula = Aula::create([
                    'titulo' => $aulaOriginal->titulo,
                    'descricao' => $aulaOriginal->descricao,
                    'video_url' => $aulaOriginal->video_url,
                    'curso_id' => $cursoDestino->id,
                    'modulo_id' => $novoModulo->id,
                ]);

                $this->copiarAvaliacoesDaAula($aulaOriginal->id, $novaAula->id);
            }

            DB::commit();

            return redirect()
                ->route('videoaulas', ['curso_id' => $cursoDestino->id])
                ->with('success', 'Módulo importado com sucesso para o curso selecionado.');

        } catch (\Throwable $e) {
            DB::rollBack();

            return back()
                ->with('error', 'Erro ao importar módulo: ' . $e->getMessage());
        }
    }

    // =========================
    // ❌ EXCLUIR CURSO DA BIBLIOTECA
    // =========================
    public function excluirCurso($id)
    {
        DB::beginTransaction();

        try {
            $curso = Curso::findOrFail($id);

            $aulasIds = Aula::where('curso_id', $curso->id)->pluck('id')->toArray();
            $modulosIds = Modulo::where('curso_id', $curso->id)->pluck('id')->toArray();

            $avaliacoesIds = [];
            $perguntasIds = [];

            if (!empty($aulasIds) && Schema::hasTable('avaliacoes')) {
                $avaliacoesIds = Avaliacao::whereIn('aula_id', $aulasIds)
                    ->pluck('id')
                    ->toArray();
            }

            if (!empty($avaliacoesIds) && Schema::hasTable('perguntas')) {
                $perguntasIds = Pergunta::whereIn('avaliacao_id', $avaliacoesIds)
                    ->pluck('id')
                    ->toArray();
            }

            $this->deletarSeTabelaColunaExiste('matriculas', 'curso_id', [$curso->id]);
            $this->deletarSeTabelaColunaExiste('curso_user', 'curso_id', [$curso->id]);
            $this->deletarSeTabelaColunaExiste('curso_usuario', 'curso_id', [$curso->id]);
            $this->deletarSeTabelaColunaExiste('inscricoes', 'curso_id', [$curso->id]);
            $this->deletarSeTabelaColunaExiste('certificados_alunos', 'curso_id', [$curso->id]);
            $this->deletarSeTabelaColunaExiste('progresso_cursos', 'curso_id', [$curso->id]);
            $this->deletarSeTabelaColunaExiste('curso_aluno', 'curso_id', [$curso->id]);
            $this->deletarSeTabelaColunaExiste('aluno_curso', 'curso_id', [$curso->id]);

            if (!empty($aulasIds)) {
                $this->deletarSeTabelaColunaExiste('aulas_assistidas', 'aula_id', $aulasIds);
                $this->deletarSeTabelaColunaExiste('resultados', 'aula_id', $aulasIds);
                $this->deletarSeTabelaColunaExiste('respostas_alunos', 'aula_id', $aulasIds);
                $this->deletarSeTabelaColunaExiste('tentativas_avaliacoes', 'aula_id', $aulasIds);
                $this->deletarSeTabelaColunaExiste('progresso_aulas', 'aula_id', $aulasIds);
                $this->deletarSeTabelaColunaExiste('aula_user', 'aula_id', $aulasIds);
                $this->deletarSeTabelaColunaExiste('aula_usuario', 'aula_id', $aulasIds);
                $this->deletarSeTabelaColunaExiste('visualizacoes_aulas', 'aula_id', $aulasIds);
                $this->deletarSeTabelaColunaExiste('historico_aulas', 'aula_id', $aulasIds);
            }

            if (!empty($avaliacoesIds)) {
                $this->deletarSeTabelaColunaExiste('resultados_avaliacoes', 'avaliacao_id', $avaliacoesIds);
                $this->deletarSeTabelaColunaExiste('tentativas', 'avaliacao_id', $avaliacoesIds);
                $this->deletarSeTabelaColunaExiste('tentativas_avaliacoes', 'avaliacao_id', $avaliacoesIds);
                $this->deletarSeTabelaColunaExiste('respostas_alunos', 'avaliacao_id', $avaliacoesIds);
                $this->deletarSeTabelaColunaExiste('avaliacao_user', 'avaliacao_id', $avaliacoesIds);
                $this->deletarSeTabelaColunaExiste('avaliacao_usuario', 'avaliacao_id', $avaliacoesIds);
            }

            if (!empty($perguntasIds)) {
                $this->deletarSeTabelaColunaExiste('respostas_alunos', 'pergunta_id', $perguntasIds);
                $this->deletarSeTabelaColunaExiste('respostas_usuario', 'pergunta_id', $perguntasIds);
                $this->deletarSeTabelaColunaExiste('alternativas_marcadas', 'pergunta_id', $perguntasIds);

                Resposta::whereIn('pergunta_id', $perguntasIds)->delete();
                Pergunta::whereIn('avaliacao_id', $avaliacoesIds)->delete();
            }

            if (!empty($avaliacoesIds)) {
                Avaliacao::whereIn('id', $avaliacoesIds)->delete();
            }

            if (!empty($aulasIds)) {
                Aula::whereIn('id', $aulasIds)->delete();
            }

            if (!empty($modulosIds)) {
                Modulo::whereIn('id', $modulosIds)->delete();
            }

            $curso->delete();

            DB::commit();

            return redirect()
                ->route('biblioteca.cursos')
                ->with('success', 'Curso excluído da biblioteca com sucesso.');

        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error('ERRO AO EXCLUIR CURSO DA BIBLIOTECA', [
                'curso_id' => $id,
                'mensagem' => $e->getMessage(),
                'arquivo' => $e->getFile(),
                'linha' => $e->getLine(),
            ]);

            return back()->with('error',
                'Erro ao excluir curso: ' . $e->getMessage() .
                ' | Arquivo: ' . $e->getFile() .
                ' | Linha: ' . $e->getLine()
            );
        }
    }

    // =========================
    // 🧠 BANCO DE PERGUNTAS ANTIGAS
    // =========================
    public function bancoPerguntas(Request $request)
    {
        $query = Pergunta::query()
            ->join('avaliacoes', 'perguntas.avaliacao_id', '=', 'avaliacoes.id')
            ->leftJoin('aulas', 'avaliacoes.aula_id', '=', 'aulas.id')
            ->leftJoin('modulos', 'aulas.modulo_id', '=', 'modulos.id')
            ->leftJoin('cursos', 'aulas.curso_id', '=', 'cursos.id')
            ->select(
                'perguntas.id',
                'perguntas.pergunta',
                'perguntas.avaliacao_id',
                'avaliacoes.titulo as avaliacao_titulo',
                'aulas.titulo as aula_titulo',
                'modulos.nome as modulo_nome',
                'cursos.nome as curso_nome',
                'perguntas.created_at'
            )
            ->orderBy('perguntas.id', 'desc');

        if ($request->filled('pesquisa')) {
            $pesquisa = $request->pesquisa;

            $query->where(function ($q) use ($pesquisa) {
                $q->where('perguntas.pergunta', 'like', "%{$pesquisa}%")
                    ->orWhere('avaliacoes.titulo', 'like', "%{$pesquisa}%")
                    ->orWhere('aulas.titulo', 'like', "%{$pesquisa}%")
                    ->orWhere('modulos.nome', 'like', "%{$pesquisa}%")
                    ->orWhere('cursos.nome', 'like', "%{$pesquisa}%");
            });
        }

        if ($request->filled('curso_id')) {
            $query->where('cursos.id', $request->curso_id);
        }

        if ($request->filled('data_inicio')) {
            $query->whereDate('perguntas.created_at', '>=', $request->data_inicio);
        }

        if ($request->filled('data_fim')) {
            $query->whereDate('perguntas.created_at', '<=', $request->data_fim);
        }

        $perguntas = $query->limit(80)->get();

        foreach ($perguntas as $pergunta) {
            $pergunta->respostas = Resposta::where('pergunta_id', $pergunta->id)
                ->select('id', 'resposta', 'correta')
                ->get();
        }

        return response()->json([
            'success' => true,
            'perguntas' => $perguntas,
        ]);
    }

    // =========================
    // 🎬 TELA DE AULAS DO ALUNO
    // =========================
    public function aluno()
    {
        $alunoId = auth()->id();

        $cursoId = null;

        if (Schema::hasTable('matriculas')) {
            $cursoId = DB::table('matriculas')
                ->where('aluno_id', $alunoId)
                ->value('curso_id');
        }

        if (!$cursoId) {
            $cursoId = Curso::orderBy('id')->value('id');
        }

        $modulos = Modulo::with(['aulas' => function ($q) {
            $q->orderBy('id');
        }])
            ->when($cursoId, function ($query) use ($cursoId) {
                $query->where('curso_id', $cursoId);
            })
            ->orderBy('ordem')
            ->orderBy('id')
            ->get();

        $assistidas = DB::table('aulas_assistidas')
            ->where('aluno_id', $alunoId)
            ->pluck('aula_id')
            ->toArray();

        foreach ($modulos as $modulo) {
            $total = $modulo->aulas->count();

            $assistidasModulo = $modulo->aulas
                ->whereIn('id', $assistidas)
                ->count();

            $modulo->progresso = $total > 0
                ? ($assistidasModulo / $total) * 100
                : 0;
        }

        return view('aluno.fazer-avaliacao', compact('modulos', 'assistidas'));
    }

    // =========================
    // 🏠 DASHBOARD DO ALUNO
    // =========================
    public function dashboardAluno()
    {
        $alunoId = auth()->id();

        $cursoId = null;

        if (Schema::hasTable('matriculas')) {
            $cursoId = DB::table('matriculas')
                ->where('aluno_id', $alunoId)
                ->value('curso_id');
        }

        if (!$cursoId) {
            $cursoId = Curso::orderBy('id')->value('id');
        }

        $modulos = Modulo::with(['aulas' => function ($q) {
            $q->orderBy('id');
        }])
            ->when($cursoId, function ($query) use ($cursoId) {
                $query->where('curso_id', $cursoId);
            })
            ->orderBy('ordem')
            ->orderBy('id')
            ->get();

        $assistidas = DB::table('aulas_assistidas')
            ->where('aluno_id', $alunoId)
            ->pluck('aula_id')
            ->toArray();

        foreach ($modulos as $modulo) {
            $total = $modulo->aulas->count();

            $assistidasModulo = $modulo->aulas
                ->whereIn('id', $assistidas)
                ->count();

            $modulo->progresso = $total > 0
                ? ($assistidasModulo / $total) * 100
                : 0;
        }

        $proximasAulas = Aula::when($cursoId, function ($query) use ($cursoId) {
                $query->where('curso_id', $cursoId);
            })
            ->orderBy('id')
            ->get();

        $listaTestes = Avaliacao::leftJoin('aulas_assistidas', function ($join) use ($alunoId) {
            $join->on('avaliacoes.aula_id', '=', 'aulas_assistidas.aula_id')
                ->where('aulas_assistidas.aluno_id', $alunoId);
        })
            ->join('aulas', 'avaliacoes.aula_id', '=', 'aulas.id')
            ->when($cursoId, function ($query) use ($cursoId) {
                $query->where('aulas.curso_id', $cursoId);
            })
            ->select(
                'avaliacoes.*',
                DB::raw('CASE WHEN aulas_assistidas.assistido = true THEN true ELSE false END as assistido')
            )
            ->get();

        return view('dashboard.aluno', compact(
            'modulos',
            'assistidas',
            'proximasAulas',
            'listaTestes'
        ));
    }

    // =========================
    // ➕ TELA CRIAR AULA
    // =========================
    public function create()
    {
        $cursos = Curso::orderBy('nome')->get();
        $modulos = Modulo::orderBy('ordem')->orderBy('id')->get();

        return view('dashboard.criar-aula', compact('cursos', 'modulos'));
    }

    // =========================
    // 💾 SALVAR AULA COMPLETA / IMPORTAR CURSO PRONTO
    // =========================
    public function store(Request $request)
    {
        $request->validate([
            'curso_modelo_id' => 'nullable|integer|exists:cursos,id',
            'nome_copia_curso' => 'nullable|string|max:255',

            'curso_id' => 'nullable|integer',
            'novo_curso' => 'nullable|string|max:255',
            'descricao_curso' => 'nullable|string|max:2000',

            'modulo_id' => 'nullable|integer',
            'novo_modulo' => 'nullable|string|max:255',

            'titulo' => 'nullable|string|max:255',
            'descricao' => 'nullable|string|max:5000',
            'video_url' => 'nullable|string|max:1000',
            'tempo_minimo_video' => 'nullable|integer|min:0',
            'tempo_maximo_video' => 'nullable|integer|min:0',

            'avaliacao.titulo' => 'nullable|string|max:255',
            'avaliacao.tempo_minimo' => 'nullable|integer|min:0',
            'avaliacao.tempo_limite' => 'nullable|integer|min:1',

            'perguntas' => 'nullable|array',
            'perguntas_importadas' => 'nullable|array',
        ]);

        DB::beginTransaction();

        try {
            // =========================
            // 1. USAR CURSO PRONTO COMO MODELO
            // =========================
            if ($request->filled('curso_modelo_id')) {
                $cursoOriginal = Curso::findOrFail($request->curso_modelo_id);

                $nomeCopia = $request->filled('nome_copia_curso')
                    ? trim($request->nome_copia_curso)
                    : $cursoOriginal->nome . ' - Nova turma ' . now()->format('d/m/Y H:i');

                $novoCurso = Curso::create([
                    'nome' => $nomeCopia,
                    'descricao' => $cursoOriginal->descricao,
                    'professor_id' => auth()->id(),
                ]);

                $this->copiarEstruturaCurso($cursoOriginal->id, $novoCurso->id);

                DB::commit();

                return redirect()
                    ->route('videoaulas', ['curso_id' => $novoCurso->id])
                    ->with('success', 'Curso pronto importado com sucesso! Todos os módulos, aulas e pós-testes foram copiados.');
            }

            // =========================
            // 2. VALIDAÇÃO FORTE DA CRIAÇÃO MANUAL
            // =========================
            $this->validarCriacaoManualCompleta($request);

            $cursoId = $request->input('curso_id');

            if ($request->filled('novo_curso')) {
                $curso = Curso::create([
                    'nome' => trim($request->input('novo_curso')),
                    'descricao' => $request->input('descricao_curso'),
                    'professor_id' => auth()->id(),
                ]);

                $cursoId = $curso->id;
            }

            if (!$cursoId) {
                return back()
                    ->withInput()
                    ->with('error', 'Selecione um curso existente ou informe o nome de um novo curso.');
            }

            $moduloId = $request->input('modulo_id');

            if ($request->filled('novo_modulo')) {
                $ultimaOrdem = Modulo::where('curso_id', $cursoId)->max('ordem') ?? 0;

                $modulo = Modulo::create([
                    'nome' => trim($request->input('novo_modulo')),
                    'curso_id' => $cursoId,
                    'ordem' => $ultimaOrdem + 1,
                ]);

                $moduloId = $modulo->id;
            }

            if (!$moduloId) {
                return back()
                    ->withInput()
                    ->with('error', 'Selecione um módulo existente ou informe o nome de um novo módulo.');
            }

            $modulo = Modulo::find($moduloId);

            if (!$modulo) {
                return back()
                    ->withInput()
                    ->with('error', 'Módulo informado não foi encontrado.');
            }

            $video = $this->formatarLinkVideo($request->input('video_url'));

            $aula = Aula::create([
                'titulo' => trim($request->input('titulo')),
                'descricao' => trim($request->input('descricao')),
                'video_url' => $video,
                'curso_id' => $cursoId,
                'modulo_id' => $moduloId,
            ]);

            if (Schema::hasColumn('aulas', 'tempo_minimo_video') || Schema::hasColumn('aulas', 'tempo_maximo_video')) {
                $dadosTempoAula = [];

                if (Schema::hasColumn('aulas', 'tempo_minimo_video')) {
                    $dadosTempoAula['tempo_minimo_video'] = (int) $request->input('tempo_minimo_video', 0);
                }

                if (Schema::hasColumn('aulas', 'tempo_maximo_video')) {
                    $dadosTempoAula['tempo_maximo_video'] = (int) $request->input('tempo_maximo_video', 0);
                }

                if (!empty($dadosTempoAula)) {
                    $aula->update($dadosTempoAula);
                }
            }

            $dadosAvaliacao = [
                'titulo' => trim($request->input('avaliacao.titulo')),
                'aula_id' => $aula->id,
                'tipo' => 'normal',
                'tempo_limite' => (int) $request->input('avaliacao.tempo_limite'),
                'qtd_perguntas' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            if (Schema::hasColumn('avaliacoes', 'tempo_minimo')) {
                $dadosAvaliacao['tempo_minimo'] = (int) $request->input('avaliacao.tempo_minimo', 0);
            }

            $avaliacaoId = DB::table('avaliacoes')->insertGetId($dadosAvaliacao);

            $this->salvarPerguntasNovas($avaliacaoId, $request->input('perguntas', []));

            if ($request->has('perguntas_importadas') && count($request->input('perguntas_importadas', [])) > 0) {
                $this->importarPerguntasParaAvaliacao($avaliacaoId, $request->input('perguntas_importadas', []));
            }

            DB::table('avaliacoes')
                ->where('id', $avaliacaoId)
                ->update([
                    'qtd_perguntas' => Pergunta::where('avaliacao_id', $avaliacaoId)->count(),
                    'updated_at' => now(),
                ]);

            DB::commit();

            return redirect()
                ->route('videoaulas', ['curso_id' => $cursoId])
                ->with('success', 'Aula, módulo e pós-teste criados com sucesso!');

        } catch (\Throwable $e) {
            DB::rollBack();

            return back()
                ->withInput()
                ->with('error',
                    'Erro ao criar aula: ' . $e->getMessage() .
                    ' | Arquivo: ' . $e->getFile() .
                    ' | Linha: ' . $e->getLine()
                );
        }
    }

    // =========================
    // ✏️ ATUALIZAR AULA
    // =========================
    public function update(Request $request, $id)
    {
        $request->validate([
            'titulo' => 'required|string|max:255',
            'descricao' => 'required|string|max:5000',
            'video_url' => 'required|string|max:1000',
            'modulo_id' => 'required|integer',
            'tempo_minimo_video' => 'nullable|integer|min:0',
            'tempo_maximo_video' => 'nullable|integer|min:0',
        ], [
            'titulo.required' => 'Informe o título da aula.',
            'descricao.required' => 'Informe a descrição da aula.',
            'video_url.required' => 'Informe o link do vídeo.',
            'modulo_id.required' => 'Selecione o módulo da aula.',
        ]);

        DB::beginTransaction();

        try {
            $aula = Aula::findOrFail($id);

            $video = $this->formatarLinkVideo($request->input('video_url'));

            $modulo = Modulo::find($request->modulo_id);

            if (!$modulo) {
                return back()->with('error', 'Módulo informado não foi encontrado.');
            }

            $aula->update([
                'titulo' => trim($request->titulo),
                'descricao' => trim($request->descricao),
                'video_url' => $video,
                'modulo_id' => $request->modulo_id,
                'curso_id' => $modulo->curso_id ?? $aula->curso_id,
            ]);

            if (Schema::hasColumn('aulas', 'tempo_minimo_video') || Schema::hasColumn('aulas', 'tempo_maximo_video')) {
                $dadosTempoAula = [];

                if (Schema::hasColumn('aulas', 'tempo_minimo_video')) {
                    $dadosTempoAula['tempo_minimo_video'] = (int) $request->input('tempo_minimo_video', 0);
                }

                if (Schema::hasColumn('aulas', 'tempo_maximo_video')) {
                    $dadosTempoAula['tempo_maximo_video'] = (int) $request->input('tempo_maximo_video', 0);
                }

                if (!empty($dadosTempoAula)) {
                    $aula->update($dadosTempoAula);
                }
            }

            DB::commit();

            return back()->with('success', 'Aula atualizada com sucesso!');

        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->with('error', 'Erro ao atualizar aula: ' . $e->getMessage());
        }
    }

    // =========================
    // ▶ MARCAR AULA COMO ASSISTIDA
    // =========================
    public function assistir(Request $request, $id)
    {
        $alunoId = auth()->id();

        if (!$alunoId) {
            return response()->json([
                'success' => false,
                'message' => 'Usuário não autenticado.',
            ], 401);
        }

        $aula = Aula::find($id);

        if (!$aula) {
            return response()->json([
                'success' => false,
                'message' => 'Aula não encontrada.',
            ], 404);
        }

        $tempoAssistidoSegundos = (int) $request->input('tempo_assistido_segundos', 0);
        $tempoMinimoMinutos = Schema::hasColumn('aulas', 'tempo_minimo_video')
            ? (int) ($aula->tempo_minimo_video ?? 0)
            : 0;

        $tempoMaximoMinutos = Schema::hasColumn('aulas', 'tempo_maximo_video')
            ? (int) ($aula->tempo_maximo_video ?? 0)
            : 0;

        $tempoMinimoSegundos = $tempoMinimoMinutos * 60;
        $tempoMaximoSegundos = $tempoMaximoMinutos * 60;

        if ($tempoMinimoSegundos > 0 && $tempoAssistidoSegundos < $tempoMinimoSegundos) {
            $faltamSegundos = $tempoMinimoSegundos - $tempoAssistidoSegundos;
            $faltamMinutos = ceil($faltamSegundos / 60);

            return response()->json([
                'success' => false,
                'message' => 'Você precisa assistir pelo menos ' . $tempoMinimoMinutos . ' minuto(s) desta videoaula para liberar o pós-teste. Ainda falta aproximadamente ' . $faltamMinutos . ' minuto(s).',
                'tempo_minimo_segundos' => $tempoMinimoSegundos,
                'tempo_assistido_segundos' => $tempoAssistidoSegundos,
                'faltam_segundos' => $faltamSegundos,
            ], 422);
        }

        $dadosAssistida = [
            'assistido' => true,
            'updated_at' => now(),
        ];

        if (Schema::hasColumn('aulas_assistidas', 'tempo_assistido_segundos')) {
            $dadosAssistida['tempo_assistido_segundos'] = $tempoAssistidoSegundos;
        }

        if (Schema::hasColumn('aulas_assistidas', 'tempo_minimo_atingido')) {
            $dadosAssistida['tempo_minimo_atingido'] = true;
        }

        if (Schema::hasColumn('aulas_assistidas', 'created_at')) {
            $dadosAssistida['created_at'] = now();
        }

        DB::table('aulas_assistidas')->updateOrInsert(
            [
                'aluno_id' => $alunoId,
                'aula_id' => $id,
            ],
            $dadosAssistida
        );

        return response()->json([
            'success' => true,
            'message' => 'Aula concluída com sucesso.',
            'tempo_assistido_segundos' => $tempoAssistidoSegundos,
            'tempo_minimo_segundos' => $tempoMinimoSegundos,
            'tempo_maximo_segundos' => $tempoMaximoSegundos,
        ]);
    }

    // =========================
    // ❌ EXCLUIR AULA
    // =========================
    public function destroy($id)
    {
        DB::beginTransaction();

        try {
            $this->excluirAvaliacoesDaAula($id);

            Aula::destroy($id);

            DB::commit();

            return back()->with('success', 'Aula excluída com sucesso!');

        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->with('error', 'Erro ao excluir aula: ' . $e->getMessage());
        }
    }

    // =========================
    // 🔐 VALIDAÇÕES FORTES
    // =========================

    private function validarCriacaoManualCompleta(Request $request)
    {
        if (!$request->filled('curso_id') && !$request->filled('novo_curso')) {
            throw new \Exception('Selecione um curso existente ou informe o nome de um novo curso.');
        }

        if (!$request->filled('modulo_id') && !$request->filled('novo_modulo')) {
            throw new \Exception('Selecione um módulo existente ou informe o nome de um novo módulo.');
        }

        if (!$request->filled('titulo')) {
            throw new \Exception('Informe o título da aula.');
        }

        if (!$request->filled('descricao')) {
            throw new \Exception('Informe a descrição da aula.');
        }

        if (!$request->filled('video_url')) {
            throw new \Exception('Informe o link do vídeo da aula.');
        }

        $tempoMinimoVideo = (int) $request->input('tempo_minimo_video', 0);
        $tempoMaximoVideo = (int) $request->input('tempo_maximo_video', 0);

        if ($tempoMinimoVideo < 0) {
            throw new \Exception('O tempo mínimo da videoaula não pode ser negativo.');
        }

        if ($tempoMaximoVideo < 0) {
            throw new \Exception('O tempo máximo da videoaula não pode ser negativo.');
        }

        if ($tempoMaximoVideo > 0 && $tempoMinimoVideo > $tempoMaximoVideo) {
            throw new \Exception('O tempo mínimo da videoaula não pode ser maior que o tempo máximo da videoaula.');
        }

        if (!$request->filled('avaliacao.titulo')) {
            throw new \Exception('Informe o título do pós-teste.');
        }

        if (!$request->filled('avaliacao.tempo_limite')) {
            throw new \Exception('Informe o tempo máximo do pós-teste.');
        }

        $tempoMinimo = (int) $request->input('avaliacao.tempo_minimo', 0);
        $tempoMaximo = (int) $request->input('avaliacao.tempo_limite', 0);

        if ($tempoMinimo < 0) {
            throw new \Exception('O tempo mínimo não pode ser negativo.');
        }

        if ($tempoMaximo <= 0) {
            throw new \Exception('O tempo máximo deve ser maior que zero.');
        }

        if ($tempoMinimo > $tempoMaximo) {
            throw new \Exception('O tempo mínimo não pode ser maior que o tempo máximo.');
        }

        $perguntas = $request->input('perguntas', []);
        $perguntasImportadas = $request->input('perguntas_importadas', []);

        if (empty($perguntas) && empty($perguntasImportadas)) {
            throw new \Exception('Adicione pelo menos uma pergunta no pós-teste.');
        }

        $this->validarPerguntasNovas($perguntas);
    }

    private function validarPerguntasNovas(array $perguntas)
    {
        foreach ($perguntas as $indexPergunta => $perguntaData) {
            $numeroPergunta = $indexPergunta + 1;

            if (empty(trim($perguntaData['pergunta'] ?? ''))) {
                throw new \Exception("A pergunta {$numeroPergunta} está sem enunciado.");
            }

            $respostas = $perguntaData['respostas'] ?? [];
            $correta = $perguntaData['correta'] ?? null;

            if ($correta === null || $correta === '') {
                throw new \Exception("Selecione a alternativa correta da pergunta {$numeroPergunta}.");
            }

            $alternativasPreenchidas = [];

            foreach ($respostas as $indexResposta => $respostaTexto) {
                if (trim((string) $respostaTexto) !== '') {
                    $alternativasPreenchidas[$indexResposta] = trim((string) $respostaTexto);
                }
            }

            if (count($alternativasPreenchidas) < 2) {
                throw new \Exception("A pergunta {$numeroPergunta} precisa ter pelo menos 2 alternativas preenchidas.");
            }

            if (!array_key_exists((int) $correta, $alternativasPreenchidas)) {
                throw new \Exception("A alternativa marcada como correta na pergunta {$numeroPergunta} está vazia.");
            }
        }
    }

    // =========================
    // 🔧 FUNÇÕES AUXILIARES
    // =========================

    private function deletarSeTabelaColunaExiste($tabela, $coluna, array $valores)
    {
        if (empty($valores)) {
            return;
        }

        if (!Schema::hasTable($tabela)) {
            return;
        }

        if (!Schema::hasColumn($tabela, $coluna)) {
            return;
        }

        DB::table($tabela)->whereIn($coluna, $valores)->delete();
    }

    private function copiarEstruturaCurso($cursoOriginalId, $novoCursoId)
    {
        $mapaModulos = [];
        $mapaAulas = [];

        $modulosOriginais = Modulo::where('curso_id', $cursoOriginalId)
            ->orderBy('ordem')
            ->orderBy('id')
            ->get();

        foreach ($modulosOriginais as $moduloOriginal) {
            $novoModulo = Modulo::create([
                'nome' => $moduloOriginal->nome,
                'curso_id' => $novoCursoId,
                'ordem' => $moduloOriginal->ordem ?? 0,
            ]);

            $mapaModulos[$moduloOriginal->id] = $novoModulo->id;
        }

        $aulasOriginais = Aula::where('curso_id', $cursoOriginalId)
            ->orderBy('modulo_id')
            ->orderBy('id')
            ->get();

        foreach ($aulasOriginais as $aulaOriginal) {
            $novoModuloId = $mapaModulos[$aulaOriginal->modulo_id] ?? null;

            if (!$novoModuloId) {
                $novoModulo = Modulo::firstOrCreate(
                    [
                        'nome' => 'Módulo Importado',
                        'curso_id' => $novoCursoId,
                    ],
                    [
                        'ordem' => 1,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );

                $novoModuloId = $novoModulo->id;
            }

            $novaAula = Aula::create([
                'titulo' => $aulaOriginal->titulo,
                'descricao' => $aulaOriginal->descricao,
                'video_url' => $aulaOriginal->video_url,
                'curso_id' => $novoCursoId,
                'modulo_id' => $novoModuloId,
            ]);

            $mapaAulas[$aulaOriginal->id] = $novaAula->id;
        }

        foreach ($mapaAulas as $aulaOriginalId => $novaAulaId) {
            $this->copiarAvaliacoesDaAula($aulaOriginalId, $novaAulaId);
        }
    }

    private function copiarAvaliacoesDaAula($aulaOriginalId, $novaAulaId)
    {
        $avaliacoesOriginais = Avaliacao::where('aula_id', $aulaOriginalId)->get();

        foreach ($avaliacoesOriginais as $avaliacaoOriginal) {
            $dadosAvaliacao = [
                'titulo' => $avaliacaoOriginal->titulo,
                'aula_id' => $novaAulaId,
                'tipo' => $avaliacaoOriginal->tipo ?? 'normal',
                'tempo_limite' => $avaliacaoOriginal->tempo_limite,
                'qtd_perguntas' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            if (Schema::hasColumn('avaliacoes', 'tempo_minimo')) {
                $dadosAvaliacao['tempo_minimo'] = $avaliacaoOriginal->tempo_minimo ?? 0;
            }

            $novaAvaliacaoId = DB::table('avaliacoes')->insertGetId($dadosAvaliacao);

            $perguntasOriginais = Pergunta::where('avaliacao_id', $avaliacaoOriginal->id)->get();

            foreach ($perguntasOriginais as $perguntaOriginal) {
                $this->copiarPerguntaParaAvaliacao($perguntaOriginal->id, $novaAvaliacaoId);
            }

            DB::table('avaliacoes')
                ->where('id', $novaAvaliacaoId)
                ->update([
                    'qtd_perguntas' => Pergunta::where('avaliacao_id', $novaAvaliacaoId)->count(),
                    'updated_at' => now(),
                ]);
        }
    }

    private function copiarPerguntaParaAvaliacao($perguntaOriginalId, $avaliacaoDestinoId)
    {
        $perguntaOriginal = Pergunta::find($perguntaOriginalId);

        if (!$perguntaOriginal) {
            return;
        }

        $novaPergunta = Pergunta::create([
            'pergunta' => $perguntaOriginal->pergunta,
            'avaliacao_id' => $avaliacaoDestinoId,
        ]);

        $respostasOriginais = Resposta::where('pergunta_id', $perguntaOriginal->id)->get();

        foreach ($respostasOriginais as $respostaOriginal) {
            Resposta::create([
                'resposta' => $respostaOriginal->resposta,
                'correta' => $respostaOriginal->correta,
                'pergunta_id' => $novaPergunta->id,
            ]);
        }
    }

    private function importarPerguntasParaAvaliacao($avaliacaoId, array $perguntasIds)
    {
        foreach ($perguntasIds as $perguntaId) {
            $this->copiarPerguntaParaAvaliacao($perguntaId, $avaliacaoId);
        }
    }

    private function salvarPerguntasNovas($avaliacaoId, array $perguntas)
    {
        $this->validarPerguntasNovas($perguntas);

        foreach ($perguntas as $perguntaData) {
            if (empty(trim($perguntaData['pergunta'] ?? ''))) {
                continue;
            }

            $pergunta = Pergunta::create([
                'pergunta' => trim($perguntaData['pergunta']),
                'avaliacao_id' => $avaliacaoId,
            ]);

            $respostas = $perguntaData['respostas'] ?? [];
            $correta = isset($perguntaData['correta'])
                ? (int) $perguntaData['correta']
                : null;

            foreach ($respostas as $index => $respostaTexto) {
                if (trim((string) $respostaTexto) === '') {
                    continue;
                }

                Resposta::create([
                    'resposta' => trim((string) $respostaTexto),
                    'correta' => $correta === (int) $index,
                    'pergunta_id' => $pergunta->id,
                ]);
            }
        }
    }

    private function excluirAvaliacoesDaAula($aulaId)
    {
        $avaliacoes = Avaliacao::where('aula_id', $aulaId)->get();

        foreach ($avaliacoes as $avaliacao) {
            $perguntas = Pergunta::where('avaliacao_id', $avaliacao->id)->get();

            foreach ($perguntas as $pergunta) {
                Resposta::where('pergunta_id', $pergunta->id)->delete();
            }

            Pergunta::where('avaliacao_id', $avaliacao->id)->delete();
        }

        Avaliacao::where('aula_id', $aulaId)->delete();
    }

    private function formatarLinkVideo($video)
    {
        if (!$video) {
            return null;
        }

        $video = trim($video);

        if (str_contains($video, 'watch?v=')) {
            $video = str_replace('watch?v=', 'embed/', $video);
        }

        if (str_contains($video, 'youtu.be/')) {
            $video = str_replace('youtu.be/', 'www.youtube.com/embed/', $video);
        }

        if (str_contains($video, '&')) {
            $video = explode('&', $video)[0];
        }

        return $video;
    }
}