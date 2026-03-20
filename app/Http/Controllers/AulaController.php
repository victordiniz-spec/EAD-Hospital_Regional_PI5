<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Aula;
use App\Models\Avaliacao;
use App\Models\Pergunta;
use App\Models\Resposta;

class AulaController extends Controller
{
    public function index()
    {
        $aulas = Aula::orderBy('id', 'desc')->get();
        return view('dashboard.videoaulas', compact('aulas'));
    }

    public function create()
    {
        return view('dashboard.criar-aula');
    }

    public function store(Request $request)
    {
        DB::beginTransaction();

        try {

            $request->validate([
                'titulo' => 'required',
                'descricao' => 'required',
                'video_url' => 'required|url',
                'avaliacao.titulo' => 'required',
                'perguntas' => 'required|array'
            ]);

            // 🔥 Converter YouTube
            $video = $request->video_url;

            if (str_contains($video, 'watch?v=')) {
                $video = str_replace('watch?v=', 'embed/', $video);
            }

            if (str_contains($video, 'youtu.be/')) {
                $video = str_replace('youtu.be/', 'www.youtube.com/embed/', $video);
            }

            // ✅ Criar aula
            $aula = Aula::create([
                'titulo' => $request->titulo,
                'descricao' => $request->descricao,
                'video_url' => $video,
                'curso_id' => 1
            ]);

            // ✅ Criar avaliação
            $avaliacao = Avaliacao::create([
                'titulo' => $request->avaliacao['titulo'],
                'aula_id' => $aula->id,
                'tempo_limite' => $request->avaliacao['tempo_limite'] ?? null,
                'qtd_perguntas' => count($request->perguntas)
            ]);

            // ✅ Perguntas e respostas
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
                ->with('success', 'Aula completa criada com sucesso!');

        } catch (\Exception $e) {

            DB::rollBack();

            return back()->with('error', $e->getMessage());
        }
    }

    // 🔥 MARCAR COMO ASSISTIDA
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

    // 🔥 EXCLUIR AULA COMPLETA
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

        } catch (\Exception $e) {

            DB::rollBack();

            return back()->with('error', 'Erro ao excluir aula!');
        }
    }
}