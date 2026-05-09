<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

        // Se ainda não existir nenhum curso, cria um curso inicial
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
    public function bibliotecaCursos()
    {
        $cursos = Curso::with(['modulos.aulas'])
            ->orderBy('id', 'desc')
            ->get();

        return view('dashboard.biblioteca-cursos', compact('cursos'));
    }

    // =========================
    // 🔁 USAR CURSO NOVAMENTE
    // =========================
    public function duplicarCurso($id)
    {
        DB::beginTransaction();

        try {
            $cursoOriginal = Curso::with(['modulos.aulas'])->findOrFail($id);

            $novoCurso = Curso::create([
                'nome' => $cursoOriginal->nome . ' - Cópia ' . now()->format('d/m/Y H:i'),
                'descricao' => $cursoOriginal->descricao,
                'professor_id' => auth()->id(),
            ]);

            $mapaModulos = [];
            $mapaAulas = [];

            // =========================
            // 1. COPIAR MÓDULOS
            // =========================
            $modulosOriginais = Modulo::where('curso_id', $cursoOriginal->id)
                ->orderBy('ordem')
                ->get();

            foreach ($modulosOriginais as $moduloOriginal) {
                $novoModulo = Modulo::create([
                    'nome' => $moduloOriginal->nome,
                    'curso_id' => $novoCurso->id,
                    'ordem' => $moduloOriginal->ordem ?? 0,
                ]);

                $mapaModulos[$moduloOriginal->id] = $novoModulo->id;
            }

            // =========================
            // 2. COPIAR AULAS
            // =========================
            $aulasOriginais = Aula::where('curso_id', $cursoOriginal->id)
                ->orderBy('modulo_id')
                ->orderBy('id')
                ->get();

            foreach ($aulasOriginais as $aulaOriginal) {
                $novoModuloId = $mapaModulos[$aulaOriginal->modulo_id] ?? null;

                $novaAula = Aula::create([
                    'titulo' => $aulaOriginal->titulo,
                    'descricao' => $aulaOriginal->descricao,
                    'video_url' => $aulaOriginal->video_url,
                    'curso_id' => $novoCurso->id,
                    'modulo_id' => $novoModuloId,
                ]);

                $mapaAulas[$aulaOriginal->id] = $novaAula->id;
            }

            // =========================
            // 3. COPIAR AVALIAÇÕES, PERGUNTAS E RESPOSTAS
            // =========================
            foreach ($mapaAulas as $aulaOriginalId => $novaAulaId) {
                $avaliacoesOriginais = Avaliacao::where('aula_id', $aulaOriginalId)->get();

                foreach ($avaliacoesOriginais as $avaliacaoOriginal) {
                    $novaAvaliacao = Avaliacao::create([
                        'titulo' => $avaliacaoOriginal->titulo,
                        'aula_id' => $novaAulaId,
                        'tipo' => $avaliacaoOriginal->tipo ?? 'normal',
                        'tempo_limite' => $avaliacaoOriginal->tempo_limite,
                        'qtd_perguntas' => $avaliacaoOriginal->qtd_perguntas,
                    ]);

                    $perguntasOriginais = Pergunta::where('avaliacao_id', $avaliacaoOriginal->id)->get();

                    foreach ($perguntasOriginais as $perguntaOriginal) {
                        $novaPergunta = Pergunta::create([
                            'pergunta' => $perguntaOriginal->pergunta,
                            'avaliacao_id' => $novaAvaliacao->id,
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
                }
            }

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
    // 🎬 TELA DE AULAS DO ALUNO
    // =========================
    public function aluno()
    {
        $alunoId = auth()->id();

        $cursoId = DB::table('matriculas')
            ->where('aluno_id', $alunoId)
            ->value('curso_id');

        if (!$cursoId) {
            $cursoId = Curso::orderBy('id')->value('id');
        }

        $modulos = Modulo::with(['aulas' => function ($q) {
            $q->orderBy('id');
        }])
            ->where('curso_id', $cursoId)
            ->orderBy('ordem')
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

        $cursoId = DB::table('matriculas')
            ->where('aluno_id', $alunoId)
            ->value('curso_id');

        if (!$cursoId) {
            $cursoId = Curso::orderBy('id')->value('id');
        }

        $modulos = Modulo::with(['aulas' => function ($q) {
            $q->orderBy('id');
        }])
            ->where('curso_id', $cursoId)
            ->orderBy('ordem')
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

        $proximasAulas = Aula::where('curso_id', $cursoId)
            ->orderBy('id')
            ->get();

        $listaTestes = Avaliacao::leftJoin('aulas_assistidas', function ($join) use ($alunoId) {
            $join->on('avaliacoes.aula_id', '=', 'aulas_assistidas.aula_id')
                ->where('aulas_assistidas.aluno_id', $alunoId);
        })
            ->join('aulas', 'avaliacoes.aula_id', '=', 'aulas.id')
            ->where('aulas.curso_id', $cursoId)
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
        $modulos = Modulo::orderBy('ordem')->get();

        return view('dashboard.criar-aula', compact('cursos', 'modulos'));
    }

    // =========================
    // 💾 SALVAR AULA COMPLETA
    // =========================
    public function store(Request $request)
    {
        $request->validate([
            'curso_id' => 'nullable|integer',
            'novo_curso' => 'nullable|string|max:255',
            'descricao_curso' => 'nullable|string',
            'titulo' => 'required|string|max:255',
            'descricao' => 'nullable|string',
            'video_url' => 'required|string',
            'modulo_id' => 'nullable|integer',
            'novo_modulo' => 'nullable|string|max:255',
            'avaliacao.titulo' => 'nullable|string|max:255',
            'avaliacao.tempo_limite' => 'nullable|integer|min:1',
            'perguntas' => 'nullable|array',
        ]);

        DB::beginTransaction();

        try {
            // =========================
            // 1. Criar ou selecionar curso
            // =========================
            $cursoId = $request->input('curso_id');

            if ($request->filled('novo_curso')) {
                $curso = Curso::create([
                    'nome' => $request->input('novo_curso'),
                    'descricao' => $request->input('descricao_curso'),
                    'professor_id' => auth()->id(),
                ]);

                $cursoId = $curso->id;
            }

            if (!$cursoId) {
                $curso = Curso::orderBy('id')->first();

                if (!$curso) {
                    $curso = Curso::create([
                        'nome' => 'Curso Principal',
                        'descricao' => 'Curso padrão do sistema',
                        'professor_id' => auth()->id(),
                    ]);
                }

                $cursoId = $curso->id;
            }

            // =========================
            // 2. Criar ou selecionar módulo
            // =========================
            $moduloId = $request->input('modulo_id');

            if ($request->filled('novo_modulo')) {
                $ultimaOrdem = Modulo::where('curso_id', $cursoId)->max('ordem') ?? 0;

                $modulo = Modulo::create([
                    'nome' => $request->input('novo_modulo'),
                    'curso_id' => $cursoId,
                    'ordem' => $ultimaOrdem + 1,
                ]);

                $moduloId = $modulo->id;
            }

            if (!$moduloId) {
                $modulo = Modulo::firstOrCreate(
                    [
                        'nome' => 'Módulo Principal',
                        'curso_id' => $cursoId,
                    ],
                    [
                        'ordem' => 1,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );

                $moduloId = $modulo->id;
            }

            // =========================
            // 3. Converter link do YouTube
            // =========================
            $video = $request->input('video_url');

            if (str_contains($video, 'watch?v=')) {
                $video = str_replace('watch?v=', 'embed/', $video);
            }

            if (str_contains($video, 'youtu.be/')) {
                $video = str_replace('youtu.be/', 'www.youtube.com/embed/', $video);
            }

            if (str_contains($video, '&')) {
                $video = explode('&', $video)[0];
            }

            // =========================
            // 4. Criar aula
            // =========================
            $aula = Aula::create([
                'titulo' => $request->input('titulo'),
                'descricao' => $request->input('descricao'),
                'video_url' => $video,
                'curso_id' => $cursoId,
                'modulo_id' => $moduloId,
            ]);

            // =========================
            // 5. Criar avaliação, se houver
            // =========================
            $avaliacao = null;

            if ($request->filled('avaliacao.titulo')) {
                $avaliacao = Avaliacao::create([
                    'titulo' => $request->input('avaliacao.titulo'),
                    'aula_id' => $aula->id,
                    'tipo' => 'normal',
                    'tempo_limite' => $request->input('avaliacao.tempo_limite'),
                    'qtd_perguntas' => count($request->input('perguntas', [])),
                ]);
            }

            // =========================
            // 6. Criar perguntas e respostas
            // =========================
            if ($avaliacao && $request->has('perguntas')) {
                foreach ($request->input('perguntas', []) as $perguntaData) {
                    if (empty($perguntaData['pergunta'])) {
                        continue;
                    }

                    $pergunta = Pergunta::create([
                        'pergunta' => $perguntaData['pergunta'],
                        'avaliacao_id' => $avaliacao->id,
                    ]);

                    $respostas = $perguntaData['respostas'] ?? [];
                    $correta = isset($perguntaData['correta'])
                        ? (int) $perguntaData['correta']
                        : null;

                    foreach ($respostas as $index => $respostaTexto) {
                        if (empty($respostaTexto)) {
                            continue;
                        }

                        Resposta::create([
                            'resposta' => $respostaTexto,
                            'correta' => $correta === (int) $index,
                            'pergunta_id' => $pergunta->id,
                        ]);
                    }
                }
            }

            DB::commit();

            return redirect()
                ->route('videoaulas', ['curso_id' => $cursoId])
                ->with('success', 'Aula criada com sucesso!');

        } catch (\Throwable $e) {
            DB::rollBack();

            return back()
                ->withInput()
                ->with('error', 'Erro ao criar aula: ' . $e->getMessage());
        }
    }

    // =========================
    // ✏️ ATUALIZAR AULA
    // =========================
    public function update(Request $request, $id)
    {
        $request->validate([
            'titulo' => 'required|string|max:255',
            'descricao' => 'nullable|string',
            'video_url' => 'required|string',
            'modulo_id' => 'required|integer',
        ]);

        DB::beginTransaction();

        try {
            $aula = Aula::findOrFail($id);

            $video = $request->input('video_url');

            if (str_contains($video, 'watch?v=')) {
                $video = str_replace('watch?v=', 'embed/', $video);
            }

            if (str_contains($video, 'youtu.be/')) {
                $video = str_replace('youtu.be/', 'www.youtube.com/embed/', $video);
            }

            if (str_contains($video, '&')) {
                $video = explode('&', $video)[0];
            }

            $modulo = Modulo::find($request->modulo_id);

            $aula->update([
                'titulo' => $request->titulo,
                'descricao' => $request->descricao,
                'video_url' => $video,
                'modulo_id' => $request->modulo_id,
                'curso_id' => $modulo->curso_id ?? $aula->curso_id,
            ]);

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
    public function assistir($id)
    {
        DB::table('aulas_assistidas')->updateOrInsert(
            [
                'aluno_id' => auth()->id(),
                'aula_id' => $id,
            ],
            [
                'assistido' => true,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        return response()->json(['success' => true]);
    }

    // =========================
    // ❌ EXCLUIR AULA
    // =========================
    public function destroy($id)
    {
        DB::beginTransaction();

        try {
            $avaliacoes = Avaliacao::where('aula_id', $id)->get();

            foreach ($avaliacoes as $avaliacao) {
                $perguntas = Pergunta::where('avaliacao_id', $avaliacao->id)->get();

                foreach ($perguntas as $pergunta) {
                    Resposta::where('pergunta_id', $pergunta->id)->delete();
                }

                Pergunta::where('avaliacao_id', $avaliacao->id)->delete();
            }

            Avaliacao::where('aula_id', $id)->delete();

            Aula::destroy($id);

            DB::commit();

            return back()->with('success', 'Aula excluída com sucesso!');

        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->with('error', 'Erro ao excluir aula: ' . $e->getMessage());
        }
    }
}