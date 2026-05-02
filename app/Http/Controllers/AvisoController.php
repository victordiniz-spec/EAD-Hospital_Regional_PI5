<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Aviso;

class AvisoController extends Controller
{
    // =========================
    // LISTAR AVISOS
    // =========================
    public function index()
    {
        $avisos = Aviso::orderBy('created_at', 'desc')->get();

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
            'categoria' => 'required|in:urgente,informativo',
        ]);

        try {
            $dados = [
                'titulo' => $request->titulo,
                'mensagem' => $request->mensagem,
                'categoria' => $request->categoria,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            // Só adiciona status se a coluna existir no banco
            if (DB::getSchemaBuilder()->hasColumn('avisos', 'status')) {
                $dados['status'] = $request->has('publicar_agora') ? 'publicado' : 'rascunho';
            }

            DB::table('avisos')->insert($dados);

            return redirect()
                ->route('dashboard.professor')
                ->with('success', 'Aviso criado com sucesso!');

        } catch (\Throwable $e) {
            return back()
                ->withInput()
                ->with('error', 'Erro ao criar aviso: ' . $e->getMessage());
        }
    }

    // =========================
    // EDITAR AVISO
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
            'categoria' => 'required|in:urgente,informativo',
        ]);

        try {
            $dados = [
                'titulo' => $request->titulo,
                'mensagem' => $request->mensagem,
                'categoria' => $request->categoria,
                'updated_at' => now(),
            ];

            if (DB::getSchemaBuilder()->hasColumn('avisos', 'status')) {
                $dados['status'] = $request->has('publicar_agora') ? 'publicado' : 'rascunho';
            }

            DB::table('avisos')
                ->where('id', $id)
                ->update($dados);

            return redirect()
                ->route('dashboard.professor')
                ->with('success', 'Aviso atualizado com sucesso!');

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

            return redirect()
                ->route('dashboard.professor')
                ->with('success', 'Aviso excluído com sucesso!');

        } catch (\Throwable $e) {
            return back()
                ->with('error', 'Erro ao excluir aviso: ' . $e->getMessage());
        }
    }
}