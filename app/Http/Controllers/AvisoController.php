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

        return view('dashboard.avisos', compact('avisos'));
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
}
