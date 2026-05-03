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
    // CADASTRO - ENVIAR CÓDIGO
    // =========================
    public function salvarAluno(Request $request)
    {
        // Remove máscara do CPF antes de validar
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

        // Segurança extra: verifica de novo antes de criar
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
    // LOGIN COM CPF
    // =========================
    public function login(Request $request)
    {
        $request->validate([
            'cpf' => 'required',
            'password' => 'required'
        ]);

        $cpf = preg_replace('/\D/', '', $request->cpf);

        $user = User::where('cpf', $cpf)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return back()->with('erro', 'CPF ou senha inválidos');
        }

        if ($user->status !== 'aprovado') {
            return back()->with('erro', 'Aguarde aprovação do administrador.');
        }

        Auth::login($user);
        $request->session()->regenerate();

        if ($user->tipo === 'admin') {
            return redirect('/dashboard-professor');
        }

        return redirect('/dashboard-aluno');
    }

    // =========================
    // APROVAR USUÁRIO
    // =========================
    public function aprovar($id)
    {
        $user = User::findOrFail($id);

        $user->status = 'aprovado';
        $user->save();

        return back()->with('success', 'Usuário aprovado com sucesso!');
    }

    // =========================
    // REJEITAR USUÁRIO
    // =========================
    public function rejeitar($id)
    {
        $user = User::findOrFail($id);

        $user->delete();

        return back()->with('success', 'Usuário rejeitado.');
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