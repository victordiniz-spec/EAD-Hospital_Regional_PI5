<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\Aviso;
use Carbon\Carbon;

class AvisoController extends Controller
{
    // =========================
    // LISTAR AVISOS
    // =========================
    public function index()
    {
        $avisos = Aviso::query()
            ->orderByRaw("
                CASE 
                    WHEN categoria = 'urgente' THEN 0
                    WHEN tipo = 'urgente' THEN 0
                    ELSE 1
                END
            ")
            ->orderByDesc('created_at')
            ->get();

        return view('dashboard.avisos', compact('avisos'));
    }

    // =========================
    // CRIAR AVISO
    // =========================
    public function store(Request $request)
    {
        $request->validate([
            'titulo' => 'required|string|max:255',
            'mensagem' => 'required|string',
            'categoria' => 'required|in:urgente,importante,informativo',
            'tempo_exibicao' => 'nullable|integer|min:1',
            'unidade_tempo' => 'nullable|in:minutos,horas,dias',
        ], [
            'titulo.required' => 'Informe o título do aviso.',
            'mensagem.required' => 'Digite a mensagem do aviso.',
            'categoria.required' => 'Escolha a categoria do aviso.',
            'categoria.in' => 'A categoria precisa ser urgente ou importante.',
            'tempo_exibicao.integer' => 'O tempo de exibição precisa ser um número.',
            'tempo_exibicao.min' => 'O tempo de exibição precisa ser maior que zero.',
            'unidade_tempo.in' => 'Escolha uma unidade válida: minutos, horas ou dias.',
        ]);

        try {
            $categoria = $this->normalizarCategoria($request->categoria);

            $tempo = $request->tempo_exibicao ?? 24;
            $unidade = $request->unidade_tempo ?? 'horas';

            $expiresAt = $this->calcularExpiracao($tempo, $unidade);

            $dados = [
                'titulo' => $request->titulo,
                'categoria' => $categoria,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            if (Schema::hasColumn('avisos', 'mensagem')) {
                $dados['mensagem'] = $request->mensagem;
            }

            if (Schema::hasColumn('avisos', 'descricao')) {
                $dados['descricao'] = $request->mensagem;
            }

            if (Schema::hasColumn('avisos', 'status')) {
                $dados['status'] = $request->has('publicar_agora') ? 'publicado' : 'publicado';
            }

            if (Schema::hasColumn('avisos', 'tipo')) {
                $dados['tipo'] = $categoria;
            }

            if (Schema::hasColumn('avisos', 'expires_at')) {
                $dados['expires_at'] = $expiresAt;
            }

            DB::table('avisos')->insert($dados);

            return back()->with('success', 'Aviso criado com sucesso!');

        } catch (\Throwable $e) {
            return back()
                ->withInput()
                ->with('error', 'Erro ao criar aviso: ' . $e->getMessage());
        }
    }

    // =========================
    // BUSCAR AVISO PARA EDITAR
    // =========================
    public function edit($id)
    {
        $aviso = Aviso::findOrFail($id);

        return response()->json($aviso);
    }

    // =========================
    // ATUALIZAR AVISO
    // =========================
    public function update(Request $request, $id)
    {
        $request->validate([
            'titulo' => 'required|string|max:255',
            'mensagem' => 'required|string',
            'categoria' => 'required|in:urgente,importante,informativo',
            'tempo_exibicao' => 'nullable|integer|min:1',
            'unidade_tempo' => 'nullable|in:minutos,horas,dias',
        ], [
            'titulo.required' => 'Informe o título do aviso.',
            'mensagem.required' => 'Digite a mensagem do aviso.',
            'categoria.required' => 'Escolha a categoria do aviso.',
            'categoria.in' => 'A categoria precisa ser urgente ou importante.',
            'tempo_exibicao.integer' => 'O tempo de exibição precisa ser um número.',
            'tempo_exibicao.min' => 'O tempo de exibição precisa ser maior que zero.',
            'unidade_tempo.in' => 'Escolha uma unidade válida: minutos, horas ou dias.',
        ]);

        try {
            $aviso = DB::table('avisos')->where('id', $id)->first();

            if (!$aviso) {
                return back()->with('error', 'Aviso não encontrado.');
            }

            $categoria = $this->normalizarCategoria($request->categoria);

            $dados = [
                'titulo' => $request->titulo,
                'categoria' => $categoria,
                'updated_at' => now(),
            ];

            if (Schema::hasColumn('avisos', 'mensagem')) {
                $dados['mensagem'] = $request->mensagem;
            }

            if (Schema::hasColumn('avisos', 'descricao')) {
                $dados['descricao'] = $request->mensagem;
            }

            if (Schema::hasColumn('avisos', 'status')) {
                $dados['status'] = $request->has('publicar_agora') ? 'publicado' : 'publicado';
            }

            if (Schema::hasColumn('avisos', 'tipo')) {
                $dados['tipo'] = $categoria;
            }

            /*
             * Se o formulário mandar novo tempo, renova a expiração.
             * Se não mandar, mantém a data antiga.
             */
            if (Schema::hasColumn('avisos', 'expires_at')) {
                if ($request->filled('tempo_exibicao') && $request->filled('unidade_tempo')) {
                    $dados['expires_at'] = $this->calcularExpiracao(
                        $request->tempo_exibicao,
                        $request->unidade_tempo
                    );
                } else {
                    $dados['expires_at'] = $aviso->expires_at ?? now()->addHours(24);
                }
            }

            DB::table('avisos')
                ->where('id', $id)
                ->update($dados);

            return back()->with('success', 'Aviso atualizado com sucesso!');

        } catch (\Throwable $e) {
            return back()
                ->withInput()
                ->with('error', 'Erro ao atualizar aviso: ' . $e->getMessage());
        }
    }

    // =========================
    // EXCLUIR AVISO
    // =========================
    public function destroy($id)
    {
        try {
            DB::table('avisos')
                ->where('id', $id)
                ->delete();

            return back()->with('success', 'Aviso excluído com sucesso!');

        } catch (\Throwable $e) {
            return back()
                ->with('error', 'Erro ao excluir aviso: ' . $e->getMessage());
        }
    }

    // =========================
    // NORMALIZAR CATEGORIA
    // =========================
    private function normalizarCategoria($categoria)
    {
        /*
         * Antes seu sistema usava "informativo".
         * Agora vamos trabalhar visualmente com "importante".
         * Para não quebrar códigos antigos, se vier informativo,
         * salvamos como importante.
         */
        if ($categoria === 'informativo') {
            return 'importante';
        }

        return $categoria;
    }

    // =========================
    // CALCULAR EXPIRAÇÃO DO AVISO
    // =========================
    private function calcularExpiracao($tempo, $unidade)
    {
        $tempo = (int) $tempo;

        if ($tempo <= 0) {
            $tempo = 24;
            $unidade = 'horas';
        }

        if ($unidade === 'minutos') {
            return now()->addMinutes($tempo);
        }

        if ($unidade === 'dias') {
            return now()->addDays($tempo);
        }

        return now()->addHours($tempo);
    }
}