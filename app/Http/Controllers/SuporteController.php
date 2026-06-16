<?php

namespace App\Http\Controllers;

use App\Models\DuvidaFrequente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Http;

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


    public function perguntarIa(Request $request)
    {
        $dados = $request->validate([
            'pergunta' => ['required', 'string', 'min:3', 'max:500'],
        ]);

        $apiKey = env('GEMINI_API_KEY');

        if (empty($apiKey)) {
            return response()->json([
                'ok' => false,
                'message' => 'A chave da IA não está configurada.',
            ], 503);
        }

        $pergunta = trim($dados['pergunta']);
        $usuario = auth()->user();

        $duvidas = DuvidaFrequente::where('ativo', true)
            ->orderBy('ordem')
            ->orderBy('pergunta')
            ->limit(60)
            ->get(['pergunta', 'resposta', 'categoria'])
            ->map(function ($duvida) {
                return "- Categoria: {$duvida->categoria}\n  Pergunta: {$duvida->pergunta}\n  Resposta cadastrada: {$duvida->resposta}";
            })
            ->implode("\n\n");

        $prompt = <<<PROMPT
Você é o Assistente Virtual do sistema Integrar ReSaúde, uma plataforma EAD hospitalar para residentes e preceptores.

REGRAS IMPORTANTES:
- Responda em português do Brasil.
- Seja educado, claro e objetivo.
- Responda somente sobre o uso da plataforma Integrar ReSaúde.
- Use como base as informações cadastradas abaixo.
- Não invente regras, prazos, notas, nomes de telas ou funcionalidades que não estejam no contexto.
- Se a pergunta não tiver relação com a plataforma, diga que só pode ajudar com dúvidas do sistema.
- Se não souber responder com segurança, diga para procurar a administração da plataforma.
- Responda em no máximo 5 linhas.
- Não use markdown pesado. Pode usar poucos emojis se ajudar.

DADOS DO USUÁRIO:
Nome: {$usuario->name}
Perfil: {$usuario->tipo}
Status: {$usuario->status}

INFORMAÇÕES CADASTRADAS NO SUPORTE:
{$duvidas}

PERGUNTA DO USUÁRIO:
{$pergunta}
PROMPT;

        try {
            $modelo = env('GEMINI_MODEL', 'gemini-2.5-flash-lite');

            $response = Http::timeout(15)->post(
                "https://generativelanguage.googleapis.com/v1beta/models/{$modelo}:generateContent?key={$apiKey}",
                [
                    'contents' => [
                        [
                            'parts' => [
                                ['text' => $prompt],
                            ],
                        ],
                    ],
                    'generationConfig' => [
                        'temperature' => 0.25,
                        'maxOutputTokens' => 350,
                    ],
                ]
            );

            if (!$response->successful()) {
                return response()->json([
                    'ok' => false,
                    'message' => 'A IA não conseguiu responder agora.',
                    'status' => $response->status(),
                ], 503);
            }

            $resposta = data_get($response->json(), 'candidates.0.content.parts.0.text');

            if (!$resposta) {
                return response()->json([
                    'ok' => false,
                    'message' => 'A IA não retornou uma resposta válida.',
                ], 503);
            }

            return response()->json([
                'ok' => true,
                'resposta' => trim($resposta),
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'ok' => false,
                'message' => 'Não foi possível consultar a IA neste momento.',
            ], 503);
        }
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
