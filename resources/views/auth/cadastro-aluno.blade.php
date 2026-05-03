@extends('layout.app')

@php $noLayout = true; @endphp

@section('title','Criar Conta')

@section('content')

<div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-green-100 via-white to-green-200 px-4 py-8">

    <div class="w-full max-w-lg bg-white p-8 rounded-2xl shadow-xl border border-gray-200">

        <!-- LOGO -->
        <div class="flex flex-col items-center mb-6">
            <img src="/logo.png" class="w-16 mb-2" alt="Logo">
            <h1 class="text-lg font-semibold text-gray-700">Integrar ReSaúde</h1>
        </div>

        <!-- TÍTULO -->
        <div class="text-center mb-6">
            <h2 class="text-2xl font-bold text-gray-800">Criar nova conta</h2>
            <p class="text-gray-500 text-sm">Preencha os dados e crie sua conta</p>
        </div>

        <!-- ERROS -->
        @if($errors->any())
            <div class="bg-red-100 text-red-600 p-3 mb-4 rounded-xl border border-red-200 text-sm">
                @foreach($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <!-- AVISO SENHA FRACA -->
        <div id="avisoSenhaFraca"
             class="hidden bg-red-50 text-red-700 border border-red-200 p-3 mb-4 rounded-xl text-sm">
            A senha está fraca. Crie uma senha média ou forte para continuar.
        </div>

        <!-- FORM -->
        <form method="POST" action="/salvar-aluno" class="space-y-4" id="formCadastro">
            @csrf

            <!-- NOME -->
            <div>
                <label class="text-sm text-gray-600 font-medium">Nome completo</label>
                <input type="text"
                       name="nome"
                       placeholder="Digite seu nome"
                       class="w-full border border-gray-300 p-3 rounded-lg text-gray-800 mt-1 focus:outline-none focus:ring-2 focus:ring-green-600 focus:border-transparent"
                       required>
            </div>

            <!-- CPF -->
            <div>
                <label class="text-sm text-gray-600 font-medium">CPF</label>
                <input type="text"
                       name="cpf"
                       id="cpf"
                       placeholder="000.000.000-00"
                       maxlength="14"
                       class="w-full border border-gray-300 p-3 rounded-lg text-gray-800 mt-1 focus:outline-none focus:ring-2 focus:ring-green-600 focus:border-transparent"
                       required>
            </div>

            <!-- EMAIL -->
            <div>
                <label class="text-sm text-gray-600 font-medium">E-mail</label>
                <input type="email"
                       name="email"
                       placeholder="seu@email.com"
                       class="w-full border border-gray-300 p-3 rounded-lg text-gray-800 mt-1 focus:outline-none focus:ring-2 focus:ring-green-600 focus:border-transparent"
                       required>
            </div>

            <!-- SENHA -->
            <div>
                <label class="text-sm text-gray-600 font-medium">Senha</label>

                <div class="relative mt-1">
                    <input type="password"
                           name="senha"
                           id="senha"
                           placeholder="Digite sua senha"
                           class="w-full border border-gray-300 p-3 rounded-lg text-gray-800 pr-14 focus:outline-none focus:ring-2 focus:ring-green-600 focus:border-transparent"
                           required>

                    <button type="button"
                            onclick="toggleSenha('senha', 'iconeSenha')"
                            class="absolute right-2 top-1/2 -translate-y-1/2 w-10 h-10 rounded-lg flex items-center justify-center text-gray-500 hover:text-green-700 hover:bg-green-50 transition"
                            aria-label="Mostrar ou ocultar senha">
                        <span id="iconeSenha">
                            <!-- OLHO ABERTO -->
                            <svg xmlns="http://www.w3.org/2000/svg"
                                 class="w-5 h-5"
                                 fill="none"
                                 viewBox="0 0 24 24"
                                 stroke="currentColor"
                                 stroke-width="1.8">
                                <path stroke-linecap="round"
                                      stroke-linejoin="round"
                                      d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                <path stroke-linecap="round"
                                      stroke-linejoin="round"
                                      d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                            </svg>
                        </span>
                    </button>
                </div>

                <!-- BARRA DE FORÇA -->
                <div class="mt-3">
                    <div class="w-full h-2 bg-gray-200 rounded-full overflow-hidden">
                        <div id="barraSenha"
                             class="h-full w-0 rounded-full transition-all duration-300">
                        </div>
                    </div>

                    <div class="flex justify-between items-center mt-2">
                        <p id="textoForcaSenha" class="text-xs font-semibold text-gray-500">
                            Digite uma senha
                        </p>

                        <p id="pontuacaoSenha" class="text-xs text-gray-400">
                            0/5
                        </p>
                    </div>
                </div>

                <!-- REGRAS -->
                <div class="mt-3 bg-gray-50 border border-gray-200 rounded-xl p-3">
                    <p class="text-xs font-semibold text-gray-600 mb-2">
                        Para sua segurança, use:
                    </p>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs">
                        <p id="regraTamanho" class="text-gray-500">○ Mínimo 8 caracteres</p>
                        <p id="regraMaiuscula" class="text-gray-500">○ Letra maiúscula</p>
                        <p id="regraMinuscula" class="text-gray-500">○ Letra minúscula</p>
                        <p id="regraNumero" class="text-gray-500">○ Número</p>
                        <p id="regraEspecial" class="text-gray-500 sm:col-span-2">○ Símbolo especial, exemplo: @ # !</p>
                    </div>
                </div>
            </div>

            <!-- CONFIRMAR SENHA -->
            <div>
                <label class="text-sm text-gray-600 font-medium">Confirmar senha</label>

                <div class="relative mt-1">
                    <input type="password"
                           name="senha_confirmation"
                           id="confirmarSenha"
                           placeholder="Digite a senha novamente"
                           class="w-full border border-gray-300 p-3 rounded-lg text-gray-800 pr-14 focus:outline-none focus:ring-2 focus:ring-green-600 focus:border-transparent"
                           required>

                    <button type="button"
                            onclick="toggleSenha('confirmarSenha', 'iconeConfirmarSenha')"
                            class="absolute right-2 top-1/2 -translate-y-1/2 w-10 h-10 rounded-lg flex items-center justify-center text-gray-500 hover:text-green-700 hover:bg-green-50 transition"
                            aria-label="Mostrar ou ocultar confirmação da senha">
                        <span id="iconeConfirmarSenha">
                            <!-- OLHO ABERTO -->
                            <svg xmlns="http://www.w3.org/2000/svg"
                                 class="w-5 h-5"
                                 fill="none"
                                 viewBox="0 0 24 24"
                                 stroke="currentColor"
                                 stroke-width="1.8">
                                <path stroke-linecap="round"
                                      stroke-linejoin="round"
                                      d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                <path stroke-linecap="round"
                                      stroke-linejoin="round"
                                      d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                            </svg>
                        </span>
                    </button>
                </div>

                <p id="mensagemConfirmacao" class="text-xs mt-2 hidden"></p>
            </div>

            <!-- TIPO -->
            <div>
                <label class="text-sm text-gray-600 font-medium">Tipo de usuário</label>

                <div class="grid grid-cols-2 gap-4 mt-3">

                    <label class="cursor-pointer">
                        <input type="radio" name="tipo" value="residente" class="hidden peer" required>

                        <div class="border rounded-xl p-4 text-center transition
                                    hover:border-green-500
                                    peer-checked:border-green-600
                                    peer-checked:bg-green-50
                                    peer-checked:ring-2
                                    peer-checked:ring-green-100">

                            <div class="text-3xl mb-2">👨‍⚕️</div>
                            <p class="text-gray-700 font-medium">Residente</p>
                        </div>
                    </label>

                    <label class="cursor-pointer">
                        <input type="radio" name="tipo" value="preceptor" class="hidden peer">

                        <div class="border rounded-xl p-4 text-center transition
                                    hover:border-green-500
                                    peer-checked:border-green-600
                                    peer-checked:bg-green-50
                                    peer-checked:ring-2
                                    peer-checked:ring-green-100">

                            <div class="text-3xl mb-2">🧑‍🏫</div>
                            <p class="text-gray-700 font-medium">Preceptor</p>
                        </div>
                    </label>

                </div>
            </div>

            <!-- BOTÃO -->
            <button type="submit"
                    id="btnEnviar"
                    class="w-full bg-green-700 text-white p-3 rounded-lg font-semibold hover:bg-green-800 transition disabled:bg-gray-400 disabled:cursor-not-allowed">
                Enviar solicitação
            </button>
        </form>

        <p class="text-center text-sm mt-4 text-gray-500">
            Já possui acesso?
            <a href="/" class="text-green-600 font-semibold hover:underline">
                Voltar ao login
            </a>
        </p>

    </div>
</div>

<script>
    let senhaAceita = false;

    const iconeOlhoAberto = `
        <svg xmlns="http://www.w3.org/2000/svg"
             class="w-5 h-5"
             fill="none"
             viewBox="0 0 24 24"
             stroke="currentColor"
             stroke-width="1.8">
            <path stroke-linecap="round"
                  stroke-linejoin="round"
                  d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
            <path stroke-linecap="round"
                  stroke-linejoin="round"
                  d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
        </svg>
    `;

    const iconeOlhoFechado = `
        <svg xmlns="http://www.w3.org/2000/svg"
             class="w-5 h-5"
             fill="none"
             viewBox="0 0 24 24"
             stroke="currentColor"
             stroke-width="1.8">
            <path stroke-linecap="round"
                  stroke-linejoin="round"
                  d="M3.98 8.223A10.477 10.477 0 0 0 2.036 12.322a1.012 1.012 0 0 0 0 .639C3.423 17.49 7.36 20.5 12 20.5c1.518 0 2.954-.314 4.25-.879" />
            <path stroke-linecap="round"
                  stroke-linejoin="round"
                  d="M6.228 6.228A10.45 10.45 0 0 1 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639a10.502 10.502 0 0 1-4.293 5.774" />
            <path stroke-linecap="round"
                  stroke-linejoin="round"
                  d="M9.88 9.88A3 3 0 0 0 14.12 14.12" />
            <path stroke-linecap="round"
                  stroke-linejoin="round"
                  d="M3 3l18 18" />
        </svg>
    `;

    function toggleSenha(inputId, iconeId) {
        const input = document.getElementById(inputId);
        const icone = document.getElementById(iconeId);

        if (!input || !icone) return;

        if (input.type === 'password') {
            input.type = 'text';
            icone.innerHTML = iconeOlhoFechado;
        } else {
            input.type = 'password';
            icone.innerHTML = iconeOlhoAberto;
        }
    }

    function atualizarRegra(id, valido, texto) {
        const elemento = document.getElementById(id);

        if (!elemento) return;

        if (valido) {
            elemento.innerText = '✓ ' + texto;
            elemento.classList.remove('text-gray-500', 'text-red-500');
            elemento.classList.add('text-green-700', 'font-semibold');
        } else {
            elemento.innerText = '○ ' + texto;
            elemento.classList.remove('text-green-700', 'font-semibold');
            elemento.classList.add('text-gray-500');
        }
    }

    function verificarForcaSenha() {
        const senha = document.getElementById('senha').value;
        const barra = document.getElementById('barraSenha');
        const texto = document.getElementById('textoForcaSenha');
        const pontuacao = document.getElementById('pontuacaoSenha');
        const aviso = document.getElementById('avisoSenhaFraca');

        let pontos = 0;

        const temTamanho = senha.length >= 8;
        const temMaiuscula = /[A-Z]/.test(senha);
        const temMinuscula = /[a-z]/.test(senha);
        const temNumero = /[0-9]/.test(senha);
        const temEspecial = /[^A-Za-z0-9]/.test(senha);

        if (temTamanho) pontos++;
        if (temMaiuscula) pontos++;
        if (temMinuscula) pontos++;
        if (temNumero) pontos++;
        if (temEspecial) pontos++;

        atualizarRegra('regraTamanho', temTamanho, 'Mínimo 8 caracteres');
        atualizarRegra('regraMaiuscula', temMaiuscula, 'Letra maiúscula');
        atualizarRegra('regraMinuscula', temMinuscula, 'Letra minúscula');
        atualizarRegra('regraNumero', temNumero, 'Número');
        atualizarRegra('regraEspecial', temEspecial, 'Símbolo especial, exemplo: @ # !');

        pontuacao.innerText = pontos + '/5';

        barra.className = 'h-full rounded-full transition-all duration-300';

        if (senha.length === 0) {
            senhaAceita = false;
            barra.classList.add('w-0');
            texto.innerText = 'Digite uma senha';
            texto.className = 'text-xs font-semibold text-gray-500';
            aviso.classList.add('hidden');
        } else if (pontos <= 2) {
            senhaAceita = false;
            barra.classList.add('w-1/3', 'bg-red-500');
            texto.innerText = 'Senha fraca';
            texto.className = 'text-xs font-semibold text-red-600';
            aviso.classList.add('hidden');
        } else if (pontos === 3 || pontos === 4) {
            senhaAceita = true;
            barra.classList.add('w-2/3', 'bg-yellow-500');
            texto.innerText = 'Senha média — aceita';
            texto.className = 'text-xs font-semibold text-yellow-600';
            aviso.classList.add('hidden');
        } else {
            senhaAceita = true;
            barra.classList.add('w-full', 'bg-green-600');
            texto.innerText = 'Senha forte — ótima escolha';
            texto.className = 'text-xs font-semibold text-green-700';
            aviso.classList.add('hidden');
        }

        verificarConfirmacaoSenha();
    }

    function verificarConfirmacaoSenha() {
        const senha = document.getElementById('senha').value;
        const confirmarSenha = document.getElementById('confirmarSenha').value;
        const mensagem = document.getElementById('mensagemConfirmacao');

        if (!confirmarSenha) {
            mensagem.classList.add('hidden');
            return;
        }

        mensagem.classList.remove('hidden');

        if (senha === confirmarSenha) {
            mensagem.innerText = '✓ As senhas coincidem';
            mensagem.className = 'text-xs mt-2 text-green-700 font-semibold';
        } else {
            mensagem.innerText = 'As senhas não coincidem';
            mensagem.className = 'text-xs mt-2 text-red-600 font-semibold';
        }
    }

    function mascararCPF(valor) {
        return valor
            .replace(/\D/g, '')
            .replace(/(\d{3})(\d)/, '$1.$2')
            .replace(/(\d{3})(\d)/, '$1.$2')
            .replace(/(\d{3})(\d{1,2})$/, '$1-$2')
            .slice(0, 14);
    }

    document.getElementById('senha').addEventListener('input', verificarForcaSenha);
    document.getElementById('confirmarSenha').addEventListener('input', verificarConfirmacaoSenha);

    document.getElementById('cpf').addEventListener('input', function () {
        this.value = mascararCPF(this.value);
    });

    document.getElementById('formCadastro').addEventListener('submit', function (e) {
        const senha = document.getElementById('senha').value;
        const confirmarSenha = document.getElementById('confirmarSenha').value;
        const aviso = document.getElementById('avisoSenhaFraca');

        verificarForcaSenha();

        if (!senhaAceita) {
            e.preventDefault();

            aviso.innerText = 'A senha está fraca. Use pelo menos uma senha média para continuar.';
            aviso.classList.remove('hidden');

            document.getElementById('senha').focus();

            return;
        }

        if (senha !== confirmarSenha) {
            e.preventDefault();

            aviso.innerText = 'As senhas não coincidem. Confira a confirmação da senha.';
            aviso.classList.remove('hidden');

            document.getElementById('confirmarSenha').focus();

            return;
        }

        aviso.classList.add('hidden');

        const btn = document.getElementById('btnEnviar');
        btn.disabled = true;
        btn.innerText = 'Enviando solicitação...';
    });
</script>

@endsection