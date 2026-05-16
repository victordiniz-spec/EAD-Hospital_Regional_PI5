<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    // =========================
    // TIPOS DE USUÁRIO DO SISTEMA
    // =========================
    private array $tiposAdministrativos = ['super_admin', 'admin', 'professor'];
    private array $tiposAluno = ['residente', 'preceptor'];

    // =========================
    // LIMPAR TEXTO PARA COMPARAÇÃO
    // =========================
    private function limparTextoSenha($texto)
    {
        $texto = (string) $texto;

        $texto = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $texto);

        return strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $texto));
    }

    // =========================
    // VERIFICAR SE A SENHA É INSEGURA
    // =========================
    private function senhaInsegura($senha, $nome, $email)
    {
        $senhaLimpa = $this->limparTextoSenha($senha);
        $nomeLimpo = $this->limparTextoSenha($nome);
        $emailUsuario = $this->limparTextoSenha(explode('@', $email)[0] ?? '');

        $senhasBloqueadas = [
            '123',
            '1234',
            '12345',
            '123456',
            '1234567',
            '12345678',
            '123456789',
            '1234567890',
            '000000',
            '111111',
            '222222',
            '333333',
            '444444',
            '555555',
            '666666',
            '777777',
            '888888',
            '999999',
            'admin',
            'admin123',
            'teste',
            'teste123',
            'senha',
            'senha123',
            'password',
            'password123',
            'qwerty',
            'qwerty123',
            'abc123',
            'abcd1234',
            'integrar',
            'integrar123',
            'resaude',
            'resaude123',
            'integrarresaude',
            'integrarresaude123',
        ];

        if (in_array($senhaLimpa, $senhasBloqueadas)) {
            return 'Essa senha é muito comum ou insegura. Não use 123, admin, teste, senha ou password.';
        }

        if (preg_match('/^\d+$/', $senhaLimpa)) {
            return 'A senha não pode conter apenas números.';
        }

        if (preg_match('/(.)\1{4,}/', $senhaLimpa)) {
            return 'A senha não pode ter muitos caracteres repetidos.';
        }

        $sequencias = [
            '0123456789',
            '9876543210',
            'abcdefghijklmnopqrstuvwxyz',
            'zyxwvutsrqponmlkjihgfedcba',
            'qwertyuiop',
            'poiuytrewq',
        ];

        foreach ($sequencias as $sequencia) {
            for ($i = 0; $i <= strlen($sequencia) - 5; $i++) {
                $trecho = substr($sequencia, $i, 5);

                if (str_contains($senhaLimpa, $trecho)) {
                    return 'A senha não pode conter sequências óbvias como 12345, abcde ou qwerty.';
                }
            }
        }

        if (strlen($nomeLimpo) >= 4 && str_contains($senhaLimpa, $nomeLimpo)) {
            return 'Não use seu nome completo na senha. Crie uma senha diferente e mais segura.';
        }

        $partesNome = preg_split('/\s+/', strtolower((string) $nome));

        foreach ($partesNome as $parte) {
            $parteLimpa = $this->limparTextoSenha($parte);

            if (strlen($parteLimpa) >= 4 && str_contains($senhaLimpa, $parteLimpa)) {
                return 'Não use partes do seu nome na senha. Crie uma senha diferente e mais segura.';
            }
        }

        if (strlen($emailUsuario) >= 4 && str_contains($senhaLimpa, $emailUsuario)) {
            return 'Não use seu e-mail na senha. Crie uma senha diferente e mais segura.';
        }

        return null;
    }

    // =========================
    // REDIRECIONAR USUÁRIO PELO TIPO
    // =========================
    private function redirecionarPorTipo(User $user)
    {
        /*
        |--------------------------------------------------------------------------
        | REGRAS DE ACESSO
        |--------------------------------------------------------------------------
        | super_admin: entra primeiro no painel do professor/admin.
        | Depois, pela sidebar, poderá acessar a tela do aluno para testar.
        |
        | professor: entra no painel do professor/admin.
        |
        | residente/preceptor: entram no painel do aluno.
        */

        if (in_array($user->tipo, ['super_admin', 'admin', 'professor'])) {
            return redirect()->route('dashboard.professor');
        }

        if (in_array($user->tipo, ['residente', 'preceptor'])) {
            return redirect()->route('dashboard.aluno');
        }

        Auth::logout();

        return redirect()
            ->route('login')
            ->with('erro', 'Tipo de usuário não reconhecido. Entre em contato com a administração.');
    }

    // =========================
    // VERIFICAR SE O USUÁRIO É SUPER ADMIN
    // =========================
    private function usuarioEhSuperAdmin(?User $user): bool
    {
        return $user && $user->tipo === 'super_admin';
    }

    // =========================
    // CADASTRO - ENVIAR CÓDIGO
    // =========================
    public function salvarAluno(Request $request)
    {
        $cpfLimpo = preg_replace('/\D/', '', $request->cpf);

        $request->merge([
            'cpf_limpo' => $cpfLimpo,
        ]);

        $request->validate([
            'nome' => ['required', 'string', 'max:255'],

            'cpf_limpo' => [
                'required',
                'digits:11',
                Rule::unique('users', 'cpf'),
            ],

            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email'),
            ],

            'senha' => [
                'required',
                'confirmed',
                'min:8',
                'regex:/[A-Z]/',
                'regex:/[a-z]/',
                'regex:/[0-9]/',
            ],

            /*
            |--------------------------------------------------------------------------
            | IMPORTANTE
            |--------------------------------------------------------------------------
            | O cadastro público só permite residente e preceptor.
            | Professor e super_admin devem ser criados manualmente no banco.
            */
            'tipo' => ['required', 'in:residente,preceptor'],
        ], [
            'nome.required' => 'Informe seu nome completo.',

            'cpf_limpo.required' => 'Informe seu CPF.',
            'cpf_limpo.digits' => 'O CPF precisa ter 11 números.',
            'cpf_limpo.unique' => 'Este CPF já está cadastrado no sistema.',

            'email.required' => 'Informe seu e-mail.',
            'email.email' => 'Informe um e-mail válido.',
            'email.unique' => 'Este e-mail já está cadastrado no sistema.',

            'senha.required' => 'Informe sua senha.',
            'senha.confirmed' => 'As senhas não coincidem.',
            'senha.min' => 'A senha precisa ter pelo menos 8 caracteres.',
            'senha.regex' => 'A senha precisa conter letra maiúscula, letra minúscula e número.',

            'tipo.required' => 'Escolha o tipo de usuário.',
            'tipo.in' => 'Tipo de usuário inválido.',
        ]);

        $erroSenha = $this->senhaInsegura(
            $request->senha,
            $request->nome,
            $request->email
        );

        if ($erroSenha) {
            return back()
                ->withInput()
                ->withErrors(['senha' => $erroSenha]);
        }

        $codigo = (string) random_int(100000, 999999);

        Session::put('cadastro_pendente', [
            'nome' => $request->nome,
            'cpf' => $cpfLimpo,
            'email' => strtolower($request->email),
            'senha' => Hash::make($request->senha),
            'tipo' => $request->tipo,
            'codigo' => $codigo,
            'expira_em' => now()->addMinutes(15)->timestamp,
        ]);

        Session::put('cadastro_email', strtolower($request->email));

        Mail::send('emails.codigo-cadastro', [
            'nome' => $request->nome,
            'codigo' => $codigo,
        ], function ($message) use ($request) {
            $message->to($request->email)
                ->subject('Código de verificação - Integrar ReSaúde');
        });

        return redirect()
            ->route('cadastro.verificar')
            ->with('success', 'Enviamos um código de verificação para o seu e-mail.');
    }

    // =========================
    // TELA PARA DIGITAR CÓDIGO
    // =========================
    public function telaVerificarCadastro()
    {
        if (!Session::has('cadastro_pendente')) {
            return redirect()
                ->route('cadastro.aluno')
                ->withErrors(['cadastro' => 'Preencha o cadastro primeiro.']);
        }

        return view('auth.verificar-email-cadastro');
    }

    // =========================
    // VERIFICAR CÓDIGO E CRIAR USUÁRIO
    // =========================
    public function verificarCodigoCadastro(Request $request)
    {
        $request->validate([
            'codigo' => ['required', 'digits:6'],
        ], [
            'codigo.required' => 'Digite o código recebido por e-mail.',
            'codigo.digits' => 'O código precisa ter 6 dígitos.',
        ]);

        $dados = Session::get('cadastro_pendente');

        if (!$dados) {
            return redirect()
                ->route('cadastro.aluno')
                ->withErrors(['cadastro' => 'Cadastro expirado. Preencha novamente.']);
        }

        if (now()->timestamp > $dados['expira_em']) {
            Session::forget('cadastro_pendente');
            Session::forget('cadastro_email');

            return redirect()
                ->route('cadastro.aluno')
                ->withErrors(['codigo' => 'O código expirou. Faça o cadastro novamente.']);
        }

        if ($request->codigo !== $dados['codigo']) {
            return back()->with('error', 'Código incorreto. Verifique seu e-mail e tente novamente.');
        }

        if (User::where('cpf', $dados['cpf'])->exists()) {
            Session::forget('cadastro_pendente');
            Session::forget('cadastro_email');

            return redirect()
                ->route('cadastro.aluno')
                ->withErrors(['cpf' => 'Este CPF já está cadastrado no sistema.']);
        }

        if (User::where('email', $dados['email'])->exists()) {
            Session::forget('cadastro_pendente');
            Session::forget('cadastro_email');

            return redirect()
                ->route('cadastro.aluno')
                ->withErrors(['email' => 'Este e-mail já está cadastrado no sistema.']);
        }

        User::create([
            'name' => $dados['nome'],
            'cpf' => $dados['cpf'],
            'email' => $dados['email'],
            'password' => $dados['senha'],
            'tipo' => $dados['tipo'],
            'status' => 'pendente',
            'email_verified_at' => now(),
        ]);

        Session::forget('cadastro_pendente');
        Session::forget('cadastro_email');

        return redirect('/')
            ->with('success', 'Conta criada com sucesso! Agora aguarde a aprovação do administrador.');
    }

    // =========================
    // REENVIAR CÓDIGO
    // =========================
    public function reenviarCodigoCadastro()
    {
        $dados = Session::get('cadastro_pendente');

        if (!$dados) {
            return redirect()
                ->route('cadastro.aluno')
                ->withErrors(['cadastro' => 'Preencha o cadastro novamente.']);
        }

        $codigo = (string) random_int(100000, 999999);

        $dados['codigo'] = $codigo;
        $dados['expira_em'] = now()->addMinutes(15)->timestamp;

        Session::put('cadastro_pendente', $dados);
        Session::put('cadastro_email', $dados['email']);

        Mail::send('emails.codigo-cadastro', [
            'nome' => $dados['nome'],
            'codigo' => $codigo,
        ], function ($message) use ($dados) {
            $message->to($dados['email'])
                ->subject('Código de verificação - Integrar ReSaúde');
        });

        return back()->with('success', 'Enviamos um novo código para o seu e-mail.');
    }

    // =========================
    // TELA ESQUECI MINHA SENHA
    // =========================
    public function telaEsqueciSenha()
    {
        return view('auth.esqueci-senha');
    }

    // =========================
    // ENVIAR CÓDIGO DE REDEFINIÇÃO
    // =========================
    public function enviarCodigoRedefinicaoSenha(Request $request)
    {
        $cpfLimpo = preg_replace('/\D/', '', $request->cpf);

        $request->merge([
            'cpf_limpo' => $cpfLimpo,
        ]);

        $request->validate([
            'cpf_limpo' => ['required', 'digits:11'],
            'email' => ['required', 'email', 'max:255'],
        ], [
            'cpf_limpo.required' => 'Informe seu CPF.',
            'cpf_limpo.digits' => 'O CPF precisa ter 11 números.',
            'email.required' => 'Informe seu e-mail.',
            'email.email' => 'Informe um e-mail válido.',
        ]);

        $user = User::where('cpf', $cpfLimpo)
            ->where('email', strtolower($request->email))
            ->first();

        if (!$user) {
            return back()
                ->withInput()
                ->withErrors([
                    'dados' => 'CPF e e-mail não encontrados juntos no sistema.'
                ]);
        }

        $codigo = (string) random_int(100000, 999999);

        Session::put('redefinir_senha_pendente', [
            'user_id' => $user->id,
            'nome' => $user->name,
            'cpf' => $user->cpf,
            'email' => $user->email,
            'codigo' => $codigo,
            'expira_em' => now()->addMinutes(15)->timestamp,
        ]);

        Session::put('redefinir_senha_email', $user->email);

        Mail::send('emails.codigo-redefinicao-senha', [
            'nome' => $user->name,
            'codigo' => $codigo,
        ], function ($message) use ($user) {
            $message->to($user->email)
                ->subject('Código para redefinir senha - Integrar ReSaúde');
        });

        return redirect()
            ->route('senha.redefinir')
            ->with('success', 'Enviamos um código de verificação para o seu e-mail.');
    }

    // =========================
    // TELA REDEFINIR SENHA
    // =========================
    public function telaRedefinirSenha()
    {
        if (!Session::has('redefinir_senha_pendente')) {
            return redirect()
                ->route('senha.esqueci')
                ->withErrors(['senha' => 'Informe seus dados primeiro.']);
        }

        return view('auth.redefinir-senha');
    }

    // =========================
    // REDEFINIR SENHA
    // =========================
    public function redefinirSenha(Request $request)
    {
        $request->validate([
            'codigo' => ['required', 'digits:6'],
            'senha' => [
                'required',
                'confirmed',
                'min:8',
                'regex:/[A-Z]/',
                'regex:/[a-z]/',
                'regex:/[0-9]/',
            ],
        ], [
            'codigo.required' => 'Digite o código recebido por e-mail.',
            'codigo.digits' => 'O código precisa ter 6 dígitos.',
            'senha.required' => 'Informe a nova senha.',
            'senha.confirmed' => 'As senhas não coincidem.',
            'senha.min' => 'A senha precisa ter pelo menos 8 caracteres.',
            'senha.regex' => 'A senha precisa conter letra maiúscula, letra minúscula e número.',
        ]);

        $dados = Session::get('redefinir_senha_pendente');

        if (!$dados) {
            return redirect()
                ->route('senha.esqueci')
                ->withErrors(['senha' => 'A solicitação expirou. Tente novamente.']);
        }

        if (now()->timestamp > $dados['expira_em']) {
            Session::forget('redefinir_senha_pendente');
            Session::forget('redefinir_senha_email');

            return redirect()
                ->route('senha.esqueci')
                ->withErrors(['codigo' => 'O código expirou. Solicite um novo código.']);
        }

        if ($request->codigo !== $dados['codigo']) {
            return back()
                ->withInput()
                ->with('error', 'Código incorreto. Verifique seu e-mail e tente novamente.');
        }

        $user = User::find($dados['user_id']);

        if (!$user) {
            Session::forget('redefinir_senha_pendente');
            Session::forget('redefinir_senha_email');

            return redirect()
                ->route('senha.esqueci')
                ->withErrors(['usuario' => 'Usuário não encontrado.']);
        }

        $erroSenha = $this->senhaInsegura(
            $request->senha,
            $user->name,
            $user->email
        );

        if ($erroSenha) {
            return back()
                ->withInput()
                ->withErrors(['senha' => $erroSenha]);
        }

        $user->password = Hash::make($request->senha);
        $user->save();

        Session::forget('redefinir_senha_pendente');
        Session::forget('redefinir_senha_email');

        return redirect('/')
            ->with('success', 'Senha redefinida com sucesso! Faça login com sua nova senha.');
    }

    // =========================
    // REENVIAR CÓDIGO DE REDEFINIÇÃO
    // =========================
    public function reenviarCodigoRedefinicaoSenha()
    {
        $dados = Session::get('redefinir_senha_pendente');

        if (!$dados) {
            return redirect()
                ->route('senha.esqueci')
                ->withErrors(['senha' => 'Informe seus dados novamente.']);
        }

        $codigo = (string) random_int(100000, 999999);

        $dados['codigo'] = $codigo;
        $dados['expira_em'] = now()->addMinutes(15)->timestamp;

        Session::put('redefinir_senha_pendente', $dados);
        Session::put('redefinir_senha_email', $dados['email']);

        Mail::send('emails.codigo-redefinicao-senha', [
            'nome' => $dados['nome'],
            'codigo' => $codigo,
        ], function ($message) use ($dados) {
            $message->to($dados['email'])
                ->subject('Código para redefinir senha - Integrar ReSaúde');
        });

        return back()->with('success', 'Enviamos um novo código para o seu e-mail.');
    }

    // =========================
    // LOGIN COM CPF
    // =========================
    public function login(Request $request)
    {
        $request->validate([
            'cpf' => 'required',
            'password' => 'required'
        ], [
            'cpf.required' => 'Informe seu CPF.',
            'password.required' => 'Informe sua senha.',
        ]);

        $cpf = preg_replace('/\D/', '', $request->cpf);

        $user = User::where('cpf', $cpf)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return back()->with('erro', 'CPF ou senha inválidos.');
        }

        if ($user->status === 'pendente') {
            return back()->with('erro', 'Sua conta ainda está pendente. Aguarde aprovação do administrador.');
        }

        if ($user->status === 'inutilizado') {
            return back()->with('erro',
                "Seu acesso ao sistema foi inutilizado.

Para verificar sua situação ou solicitar mais informações, entre em contato com a administração.

E-mail: administracao@seudominio.com
Telefone: (34) 00000-0000
Endereço: Hospital Regional - Setor Administrativo
Melhor horário para atendimento: segunda a sexta-feira, das 08h às 17h."
            );
        }

        if ($user->status !== 'aprovado') {
            return back()->with('erro', 'Seu usuário não está liberado para acessar o sistema.');
        }

        /*
        |--------------------------------------------------------------------------
        | SEGURANÇA DOS PAPÉIS
        |--------------------------------------------------------------------------
        | Só aceita os tipos definidos abaixo.
        | Isso impede tipo desconhecido de entrar por erro de banco.
        */
        $tiposPermitidos = array_merge($this->tiposAdministrativos, $this->tiposAluno);

        if (!in_array($user->tipo, $tiposPermitidos)) {
            return back()->with('erro', 'Tipo de usuário inválido. Entre em contato com a administração.');
        }

        Auth::login($user);
        $request->session()->regenerate();

        return $this->redirecionarPorTipo($user);
    }

    // =========================
    // APROVAR USUÁRIO
    // =========================
    public function aprovar($id)
    {
        $user = User::findOrFail($id);

        if ($this->usuarioEhSuperAdmin($user)) {
            return back()->with('error', 'O super administrador não precisa de aprovação e não pode ser alterado por esta ação.');
        }

        $user->status = 'aprovado';
        $user->save();

        return back()->with('success', 'Usuário aprovado com sucesso!');
    }

    // =========================
    // REJEITAR USUÁRIO
    // Exclui o usuário do banco.
    // =========================
    public function rejeitar($id)
    {
        $user = User::findOrFail($id);

        if (auth()->id() == $user->id) {
            return back()->with('error', 'Você não pode rejeitar/excluir o próprio usuário logado.');
        }

        if ($this->usuarioEhSuperAdmin($user)) {
            return back()->with('error', 'O super administrador não pode ser rejeitado ou excluído por esta ação.');
        }

        $user->delete();

        return back()->with('success', 'Usuário rejeitado e excluído com sucesso.');
    }

    // =========================
    // INUTILIZAR USUÁRIO
    // Bloqueia o acesso, mas mantém o usuário no banco.
    // =========================
    public function inutilizar($id)
    {
        $user = User::findOrFail($id);

        if (auth()->id() == $user->id) {
            return back()->with('error', 'Você não pode inutilizar o próprio usuário logado.');
        }

        if ($this->usuarioEhSuperAdmin($user)) {
            return back()->with('error', 'O super administrador não pode ser inutilizado por esta ação.');
        }

        if ($user->status === 'inutilizado') {
            return back()->with('error', 'Este usuário já está inutilizado.');
        }

        $user->status = 'inutilizado';
        $user->save();

        return back()->with('success', 'Usuário inutilizado com sucesso!');
    }

    // =========================
    // REATIVAR USUÁRIO
    // Volta o usuário para aprovado.
    // =========================
    public function reativar($id)
    {
        $user = User::findOrFail($id);

        if ($this->usuarioEhSuperAdmin($user)) {
            return back()->with('error', 'O super administrador já possui acesso total e não pode ser alterado por esta ação.');
        }

        if ($user->status === 'aprovado') {
            return back()->with('error', 'Este usuário já está ativo/aprovado.');
        }

        $user->status = 'aprovado';
        $user->save();

        return back()->with('success', 'Usuário reativado com sucesso!');
    }

    // =========================
    // LOGOUT
    // =========================
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
