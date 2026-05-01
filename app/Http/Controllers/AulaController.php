<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Aula;
use App\Models\Avaliacao;
use App\Models\Pergunta;
use App\Models\Resposta;
use App\Models\Modulo;

class AulaController extends Controller
{
    // =========================
    // 📺 LISTA DE AULAS (PROFESSOR)
    // =========================
    public function index()
    {
        $aulas = Aula::with('modulo')->orderBy('id', 'desc')->get();
        $modulos = Modulo::orderBy('ordem')->get();

        return view('dashboard.videoaulas', compact('aulas', 'modulos'));
    }

    // =========================
    // 🎬 TELA DE AULAS DO ALUNO
    // =========================
    public function aluno()
    {
        $alunoId = auth()->id();

        $modulos = Modulo::with(['aulas' => function ($q) {
            $q->orderBy('id');
        }])->orderBy('ordem')->get();

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

        $modulos = Modulo::with(['aulas' => function ($q) {
            $q->orderBy('id');
        }])->get();

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

        $proximasAulas = Aula::orderBy('id')->get();

        $listaTestes = Avaliacao::leftJoin('aulas_assistidas', function ($join) use ($alunoId) {
            $join->on('avaliacoes.aula_id', '=', 'aulas_assistidas.aula_id')
                ->where('aulas_assistidas.aluno_id', $alunoId);
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
    // ➕ CRIAR AULA
    // =========================
    public function create()
    {
        $modulos = Modulo::all();
        return view('dashboard.criar-aula', compact('modulos'));
    }

    // =========================
    // 💾 SALVAR AULA COMPLETA
    // =========================
    public function store(Request $request)
    {
        $request->validate([
            'titulo' => 'required|string|max:255',
            'descricao' => 'nullable|string',
            'video_url' => 'required|string',
            'modulo_id' => 'nullable',
            'novo_modulo' => 'nullable|string|max:255',
            'avaliacao.titulo' => 'nullable|string|max:255',
            'avaliacao.tempo_limite' => 'nullable|integer|min:1',
            'perguntas' => 'nullable|array',
        ]);

        DB::beginTransaction();

        try {
            // =========================
            // 1. Garantir curso padrão
            // =========================
            $curso = DB::table('cursos')->orderBy('id')->first();

            if (!$curso) {
                $professorId = auth()->id();

                $cursoId = DB::table('cursos')->insertGetId([
                    'nome' => 'Curso Principal',
                    'descricao' => 'Curso padrão do sistema',
                    'professor_id' => $professorId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } else {
                $cursoId = $curso->id;
            }

            // =========================
            // 2. Criar ou pegar módulo
            // =========================
            $moduloId = $request->input('modulo_id');

            if ($request->filled('novo_modulo')) {
                $ultimaOrdem = Modulo::max('ordem') ?? 0;

                $modulo = Modulo::create([
                    'nome' => $request->input('novo_modulo'),
                    'curso_id' => $cursoId,
                    'ordem' => $ultimaOrdem + 1,
                ]);

                $moduloId = $modulo->id;
            }

            // Se não escolheu módulo nem criou novo, cria um módulo padrão
            if (!$moduloId) {
                $modulo = Modulo::firstOrCreate(
                    ['nome' => 'Módulo Principal'],
                    [
                        'curso_id' => $cursoId,
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

            // Remove parâmetros extras simples do YouTube embed
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
                ->route('videoaulas')
                ->with('success', 'Aula criada com sucesso!');

        } catch (\Throwable $e) {
            DB::rollBack();

            return back()
                ->withInput()
                ->with('error', 'Erro ao criar aula: ' . $e->getMessage());
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

            return back()->with('success', 'Aula excluída!');

        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->with('error', 'Erro ao excluir aula: ' . $e->getMessage());
        }
    }
}