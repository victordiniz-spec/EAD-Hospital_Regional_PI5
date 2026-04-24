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
    // 🎬 TELA DE AULAS DO ALUNO (NOVO)
    // =========================
    public function aluno()
    {
        $alunoId = auth()->id();

        $modulos = Modulo::with(['aulas' => function ($q) {
            $q->orderBy('id');
        }])->orderBy('ordem')->get();

        // aulas assistidas
        $assistidas = DB::table('aulas_assistidas')
            ->where('aluno_id', $alunoId)
            ->pluck('aula_id')
            ->toArray();

        // progresso por módulo
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
            ->select('avaliacoes.*', 'aulas_assistidas.assistido')
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
    // 💾 SALVAR AULA
    // =========================
    public function store(Request $request)
    {
        DB::beginTransaction();

        try {

            $request->validate([
                'titulo' => 'required',
                'descricao' => 'required',
                'video_url' => 'required',
                'avaliacao.titulo' => 'required',
                'perguntas' => 'required|array'
            ]);

            $video = $request->video_url;

            // converter links do YouTube
            if (str_contains($video, 'watch?v=')) {
                $video = str_replace('watch?v=', 'embed/', $video);
            }

            if (str_contains($video, 'youtu.be/')) {
                $video = str_replace('youtu.be/', 'www.youtube.com/embed/', $video);
            }

            // módulo
            $moduloId = null;

            if ($request->novo_modulo) {
                $modulo = Modulo::create([
                    'nome' => $request->novo_modulo,
                    'curso_id' => 1
                ]);
                $moduloId = $modulo->id;
            } else {
                $moduloId = $request->modulo_id;
            }

            $aula = Aula::create([
                'titulo' => $request->titulo,
                'descricao' => $request->descricao,
                'video_url' => $video,
                'curso_id' => 1,
                'modulo_id' => $moduloId
            ]);

            $avaliacao = Avaliacao::create([
                'titulo' => $request->avaliacao['titulo'],
                'aula_id' => $aula->id,
                'tempo_limite' => $request->avaliacao['tempo_limite'] ?? null,
                'qtd_perguntas' => count($request->perguntas)
            ]);

            foreach ($request->perguntas as $perguntaData) {

                $pergunta = Pergunta::create([
                    'pergunta' => $perguntaData['pergunta'],
                    'avaliacao_id' => $avaliacao->id
                ]);

                foreach ($perguntaData['respostas'] as $index => $resposta) {

                    Resposta::create([
                        'resposta' => $resposta,
                        'correta' => ($perguntaData['correta'] == $index),
                        'pergunta_id' => $pergunta->id
                    ]);
                }
            }

            DB::commit();

            return redirect()->route('videoaulas')
                ->with('success', 'Aula criada com sucesso!');

        } catch (\Exception $e) {

            DB::rollBack();
            return back()->with('error', $e->getMessage());
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
                'aula_id' => $id
            ],
            [
                'assistido' => true,
                'created_at' => now()
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

        } catch (\Exception $e) {

            DB::rollBack();
            return back()->with('error', 'Erro ao excluir!');
        }
    }
}