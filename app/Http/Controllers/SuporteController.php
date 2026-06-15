<?php

namespace App\Http\Controllers;

use App\Models\DuvidaFrequente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

class SuporteController extends Controller
{
    public function index()
    {
        $duvidas = DuvidaFrequente::where('ativo', true)
            ->orderBy('ordem')
            ->orderBy('pergunta')
            ->get();

        return view('dashboard.suporte.index', compact('duvidas'));
    }

    public function admin()
    {
        $this->verificarPermissaoAdministrativa();

        $duvidas = DuvidaFrequente::orderBy('ordem')
            ->orderBy('pergunta')
            ->get();

        return view('dashboard.suporte.admin', compact('duvidas'));
    }

    public function store(Request $request)
    {
        $this->verificarPermissaoAdministrativa();

        $dados = $this->validarDadosDuvida($request);

        DuvidaFrequente::create($dados);

        return back()->with('success', 'Dúvida cadastrada com sucesso! Ela já pode ser usada pelo assistente virtual.');
    }

    public function update(Request $request, $id)
    {
        $this->verificarPermissaoAdministrativa();

        $duvida = DuvidaFrequente::findOrFail($id);

        $dados = $this->validarDadosDuvida($request);

        $duvida->update($dados);

        return back()->with('success', 'Dúvida atualizada com sucesso!');
    }

    public function destroy($id)
    {
        $this->verificarPermissaoAdministrativa();

        $duvida = DuvidaFrequente::findOrFail($id);
        $duvida->delete();

        return back()->with('success', 'Dúvida removida com sucesso!');
    }

    private function validarDadosDuvida(Request $request): array
    {
        $dados = $request->validate([
            'pergunta' => ['required', 'string', 'max:255'],
            'resposta' => ['required', 'string'],
            'categoria' => ['nullable', 'string', 'max:100'],
            'texto_botao' => ['nullable', 'string', 'max:100'],
            'rota_botao' => ['nullable', 'string', 'max:100'],
            'ordem' => ['nullable', 'integer'],
        ]);

        $dados['ativo'] = $request->has('ativo');
        $dados['ordem'] = $dados['ordem'] ?? 0;

        /*
        |--------------------------------------------------------------------------
        | Segurança da rota do botão
        |--------------------------------------------------------------------------
        | Se a pessoa preencher uma rota que não existe, limpamos o campo para
        | evitar erro na tela do suporte.
        */
        if (!empty($dados['rota_botao']) && !Route::has($dados['rota_botao'])) {
            $dados['rota_botao'] = null;
        }

        if (empty($dados['texto_botao'])) {
            $dados['texto_botao'] = null;
            $dados['rota_botao'] = null;
        }

        return $dados;
    }

    private function verificarPermissaoAdministrativa(): void
    {
        $tipo = auth()->user()->tipo ?? null;

        if (!in_array($tipo, ['super_admin', 'admin', 'professor'])) {
            abort(403, 'Você não tem permissão para acessar esta área.');
        }
    }
}
