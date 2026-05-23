<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\Aviso;
use Carbon\Carbon;

class AvisoController extends Controller
{
    public function index()
    {
        $query = Aviso::query();

        if (Schema::hasColumn('avisos', 'favorito')) {
            $query->orderByDesc('favorito');
        }

        $avisos = $query
            ->orderByDesc('created_at')
            ->get();

        $alertasSistema = $this->gerarAlertasProfessor();

        return view('dashboard.avisos', compact('avisos', 'alertasSistema'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'titulo' => 'required|string|max:255',
            'mensagem' => 'required|string',
            'categoria' => 'required|in:urgente,informativo,importante',
            'tempo_exibicao' => 'required|integer|min:1',
            'unidade_tempo' => 'required|in:minutos,horas,dias',
        ], [
            'titulo.required' => 'Informe o título do aviso.',
            'mensagem.required' => 'Informe a mensagem do aviso.',
            'categoria.required' => 'Selecione a categoria do aviso.',
            'tempo_exibicao.required' => 'Informe o tempo de exibição.',
            'tempo_exibicao.min' => 'O tempo de exibição precisa ser maior que zero.',
        ]);

        try {
            $categoria = $this->normalizarCategoria($request->categoria);
            $expiresAt = $this->calcularExpiracao(
                (int) $request->tempo_exibicao,
                $request->unidade_tempo
            );

            $dados = [
                'titulo' => trim($request->titulo),
                'categoria' => $categoria,
            ];

            if (Schema::hasColumn('avisos', 'mensagem')) {
                $dados['mensagem'] = trim($request->mensagem);
            }

            if (Schema::hasColumn('avisos', 'descricao')) {
                $dados['descricao'] = trim($request->mensagem);
            }

            if (Schema::hasColumn('avisos', 'status')) {
                $dados['status'] = $request->has('publicar_agora') ? 'publicado' : 'rascunho';
            }

            if (Schema::hasColumn('avisos', 'tipo')) {
                $dados['tipo'] = $categoria;
            }

            if (Schema::hasColumn('avisos', 'expires_at')) {
                $dados['expires_at'] = $expiresAt;
            }

            if (Schema::hasColumn('avisos', 'favorito')) {
                $dados['favorito'] = $request->boolean('favorito');
            }

            if (Schema::hasColumn('avisos', 'created_at')) {
                $dados['created_at'] = now();
            }

            if (Schema::hasColumn('avisos', 'updated_at')) {
                $dados['updated_at'] = now();
            }

            DB::table('avisos')->insert($dados);

            return redirect()
                ->route('avisos')
                ->with('success', 'Aviso criado com sucesso!');

        } catch (\Throwable $e) {
            return back()
                ->withInput()
                ->with('error', 'Erro ao criar aviso: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $aviso = Aviso::findOrFail($id);

        return response()->json($aviso);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'titulo' => 'required|string|max:255',
            'mensagem' => 'required|string',
            'categoria' => 'required|in:urgente,informativo,importante',
            'tempo_exibicao' => 'nullable|integer|min:1',
            'unidade_tempo' => 'nullable|in:minutos,horas,dias',
        ], [
            'titulo.required' => 'Informe o título do aviso.',
            'mensagem.required' => 'Informe a mensagem do aviso.',
            'categoria.required' => 'Selecione a categoria do aviso.',
        ]);

        try {
            $categoria = $this->normalizarCategoria($request->categoria);

            $dados = [
                'titulo' => trim($request->titulo),
                'categoria' => $categoria,
            ];

            if (Schema::hasColumn('avisos', 'mensagem')) {
                $dados['mensagem'] = trim($request->mensagem);
            }

            if (Schema::hasColumn('avisos', 'descricao')) {
                $dados['descricao'] = trim($request->mensagem);
            }

            if (Schema::hasColumn('avisos', 'status')) {
                $dados['status'] = $request->has('publicar_agora') ? 'publicado' : 'rascunho';
            }

            if (Schema::hasColumn('avisos', 'tipo')) {
                $dados['tipo'] = $categoria;
            }

            if (Schema::hasColumn('avisos', 'expires_at') && $request->filled('tempo_exibicao') && $request->filled('unidade_tempo')) {
                $dados['expires_at'] = $this->calcularExpiracao(
                    (int) $request->tempo_exibicao,
                    $request->unidade_tempo
                );
            }

            if (Schema::hasColumn('avisos', 'favorito')) {
                $dados['favorito'] = $request->boolean('favorito');
            }

            if (Schema::hasColumn('avisos', 'updated_at')) {
                $dados['updated_at'] = now();
            }

            DB::table('avisos')
                ->where('id', $id)
                ->update($dados);

            return redirect()
                ->route('avisos')
                ->with('success', 'Aviso atualizado com sucesso!');

        } catch (\Throwable $e) {
            return back()
                ->withInput()
                ->with('error', 'Erro ao atualizar aviso: ' . $e->getMessage());
        }
    }

    public function toggleFavorito($id)
    {
        try {
            if (!Schema::hasColumn('avisos', 'favorito')) {
                return back()->with('error', 'A coluna favorito ainda não existe. Rode a migration primeiro.');
            }

            $aviso = Aviso::findOrFail($id);
            $novoStatus = ! (bool) ($aviso->favorito ?? false);

            DB::table('avisos')
                ->where('id', $id)
                ->update([
                    'favorito' => $novoStatus,
                    'updated_at' => now(),
                ]);

            return back()->with('success', $novoStatus
                ? 'Aviso marcado como favorito!'
                : 'Aviso removido dos favoritos!'
            );

        } catch (\Throwable $e) {
            return back()->with('error', 'Erro ao alterar favorito: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            DB::table('avisos')
                ->where('id', $id)
                ->delete();

            return redirect()
                ->route('avisos')
                ->with('success', 'Aviso excluído com sucesso!');

        } catch (\Throwable $e) {
            return back()
                ->with('error', 'Erro ao excluir aviso: ' . $e->getMessage());
        }
    }

    private function normalizarCategoria($categoria)
    {
        if ($categoria === 'informativo') {
            return 'importante';
        }

        return $categoria ?: 'importante';
    }

    private function calcularExpiracao($tempo, $unidade)
    {
        $data = Carbon::now();

        return match ($unidade) {
            'minutos' => $data->addMinutes($tempo),
            'dias' => $data->addDays($tempo),
            default => $data->addHours($tempo),
        };
    }

    private function gerarAlertasProfessor()
    {
        $alertas = collect();

        if (!Schema::hasTable('users')) {
            return $alertas;
        }

        $tiposAlunos = ['residente', 'preceptor'];

        $pendentes = DB::table('users')
            ->where(function ($query) {
                $query->where('status', 'pendente')
                    ->orWhere('status', 'aguardando')
                    ->orWhere('status', 'aguardando_aprovacao')
                    ->orWhere('status', 'aguardando aprovação');
            })
            ->whereIn('tipo', $tiposAlunos)
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        foreach ($pendentes as $usuario) {
            $alertas->push((object) [
                'prioridade' => 1,
                'tipo' => 'aprovação',
                'categoria' => 'urgente',
                'titulo' => 'Novo usuário aguardando aprovação',
                'mensagem' => ($usuario->name ?? 'Usuário') . ' solicitou acesso como ' . ucfirst($usuario->tipo ?? 'usuário') . '.',
                'aluno' => $usuario->name ?? 'Usuário sem nome',
                'email' => $usuario->email ?? null,
                'acao_url' => route('controle.usuarios'),
                'acao_texto' => 'Abrir controle',
                'data' => $usuario->created_at ?? now(),
            ]);
        }

        $totalAulas = Schema::hasTable('aulas') ? DB::table('aulas')->count() : 0;

        $totalAvaliacoes = Schema::hasTable('avaliacoes')
            ? DB::table('avaliacoes')
                ->where(function ($query) {
                    $query->where('tipo', 'normal')
                        ->orWhere('tipo', 'pos_teste')
                        ->orWhere('tipo', 'pós-teste')
                        ->orWhereNull('tipo');
                })
                ->count()
            : 0;

        $alunos = DB::table('users')
            ->whereIn('tipo', $tiposAlunos)
            ->where('status', 'aprovado')
            ->orderBy('name')
            ->get();

        foreach ($alunos as $aluno) {
            $aulasAssistidas = 0;
            $avaliacoesFeitas = 0;
            $media = 0;
            $ultimaAtividade = null;

            if (Schema::hasTable('aulas_assistidas')) {
                $aulasAssistidas = DB::table('aulas_assistidas')
                    ->where('aluno_id', $aluno->id)
                    ->where('assistido', true)
                    ->distinct('aula_id')
                    ->count('aula_id');

                $ultimaAtividade = DB::table('aulas_assistidas')
                    ->where('aluno_id', $aluno->id)
                    ->max('updated_at');
            }

            if (Schema::hasTable('notas')) {
                $avaliacoesFeitas = DB::table('notas')
                    ->where('aluno_id', $aluno->id)
                    ->distinct('avaliacao_id')
                    ->count('avaliacao_id');

                $media = DB::table('notas')
                    ->where('aluno_id', $aluno->id)
                    ->avg('nota') ?? 0;

                $ultimaNota = DB::table('notas')
                    ->where('aluno_id', $aluno->id)
                    ->max('created_at');

                if (!$ultimaAtividade || ($ultimaNota && Carbon::parse($ultimaNota)->gt(Carbon::parse($ultimaAtividade)))) {
                    $ultimaAtividade = $ultimaNota;
                }
            }

            $progresso = $totalAulas > 0 ? (int) round(($aulasAssistidas / $totalAulas) * 100) : 0;
            $pendentesPosteste = max($totalAvaliacoes - $avaliacoesFeitas, 0);

            $diasSemProgresso = null;
            if ($ultimaAtividade) {
                try {
                    $diasSemProgresso = (int) floor(
                        Carbon::parse($ultimaAtividade)
                            ->timezone('America/Sao_Paulo')
                            ->startOfDay()
                            ->diffInDays(now()->timezone('America/Sao_Paulo')->startOfDay())
                    );
                } catch (\Throwable $e) {
                    $diasSemProgresso = null;
                }
            }

            if ($diasSemProgresso === null) {
                $alertas->push((object) [
                    'prioridade' => 2,
                    'tipo' => 'sem início',
                    'categoria' => 'urgente',
                    'titulo' => 'Aluno ainda não iniciou o curso',
                    'mensagem' => ($aluno->name ?? 'Aluno') . ' ainda não possui aula assistida ou pós-teste realizado.',
                    'aluno' => $aluno->name ?? 'Aluno sem nome',
                    'email' => $aluno->email ?? null,
                    'acao_url' => route('acompanhamento.residentes'),
                    'acao_texto' => 'Ver acompanhamento',
                    'data' => $aluno->created_at ?? now(),
                ]);
            } elseif ($diasSemProgresso >= 7) {
                $alertas->push((object) [
                    'prioridade' => 2,
                    'tipo' => 'sem progresso',
                    'categoria' => 'urgente',
                    'titulo' => 'Aluno sem progresso há ' . $diasSemProgresso . ' dia(s)',
                    'mensagem' => ($aluno->name ?? 'Aluno') . ' está há ' . $diasSemProgresso . ' dia(s) sem registrar progresso.',
                    'aluno' => $aluno->name ?? 'Aluno sem nome',
                    'email' => $aluno->email ?? null,
                    'acao_url' => route('acompanhamento.residentes'),
                    'acao_texto' => 'Ver acompanhamento',
                    'data' => $ultimaAtividade,
                ]);
            }

            if ($totalAulas > 0 && $progresso < 50) {
                $alertas->push((object) [
                    'prioridade' => 3,
                    'tipo' => 'baixo progresso',
                    'categoria' => 'importante',
                    'titulo' => 'Baixo progresso no curso',
                    'mensagem' => ($aluno->name ?? 'Aluno') . ' concluiu apenas ' . $progresso . '% das aulas cadastradas.',
                    'aluno' => $aluno->name ?? 'Aluno sem nome',
                    'email' => $aluno->email ?? null,
                    'acao_url' => route('acompanhamento.residentes'),
                    'acao_texto' => 'Ver acompanhamento',
                    'data' => $ultimaAtividade ?? $aluno->created_at ?? now(),
                ]);
            }

            if ($pendentesPosteste > 0) {
                $alertas->push((object) [
                    'prioridade' => 4,
                    'tipo' => 'pós-teste',
                    'categoria' => 'importante',
                    'titulo' => 'Pós-teste pendente',
                    'mensagem' => ($aluno->name ?? 'Aluno') . ' possui ' . $pendentesPosteste . ' pós-teste(s) pendente(s).',
                    'aluno' => $aluno->name ?? 'Aluno sem nome',
                    'email' => $aluno->email ?? null,
                    'acao_url' => route('acompanhamento.residentes'),
                    'acao_texto' => 'Ver acompanhamento',
                    'data' => $ultimaAtividade ?? $aluno->created_at ?? now(),
                ]);
            }

            if ($avaliacoesFeitas > 0 && round($media, 1) < 7) {
                $alertas->push((object) [
                    'prioridade' => 5,
                    'tipo' => 'média baixa',
                    'categoria' => 'urgente',
                    'titulo' => 'Média abaixo do esperado',
                    'mensagem' => ($aluno->name ?? 'Aluno') . ' está com média ' . number_format($media, 1, ',', '.') . '.',
                    'aluno' => $aluno->name ?? 'Aluno sem nome',
                    'email' => $aluno->email ?? null,
                    'acao_url' => route('acompanhamento.residentes'),
                    'acao_texto' => 'Ver acompanhamento',
                    'data' => $ultimaAtividade ?? $aluno->created_at ?? now(),
                ]);
            }

            if ($totalAulas > 0 && $progresso >= 70 && round($media, 1) >= 7) {
                $alertas->push((object) [
                    'prioridade' => 6,
                    'tipo' => 'certificado',
                    'categoria' => 'positivo',
                    'titulo' => 'Aluno próximo do certificado',
                    'mensagem' => ($aluno->name ?? 'Aluno') . ' já possui progresso e média favoráveis para emissão do certificado.',
                    'aluno' => $aluno->name ?? 'Aluno sem nome',
                    'email' => $aluno->email ?? null,
                    'acao_url' => route('certificados.criar'),
                    'acao_texto' => 'Ver certificados',
                    'data' => $ultimaAtividade ?? $aluno->created_at ?? now(),
                ]);
            }
        }

        return $alertas
            ->sortBy('prioridade')
            ->values()
            ->take(60);
    }
}
