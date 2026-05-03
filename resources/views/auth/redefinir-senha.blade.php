@extends('layout.app')

@php $noLayout = true; @endphp

@section('title','Redefinir Senha')

@section('content')

<div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-green-100 via-white to-green-200 px-4 py-8">

    <div class="w-full max-w-md bg-white p-8 rounded-2xl shadow-xl border border-gray-200">

        <!-- LOGO -->
        <div class="flex flex-col items-center mb-6">
            <img src="/logo.png" class="w-16 mb-2" alt="Logo">
            <h1 class="text-lg font-semibold text-gray-700">Integrar ReSaúde</h1>
        </div>

        <!-- TÍTULO -->
        <div class="text-center mb-6">
            <div class="w-16 h-16 mx-auto bg-green-100 rounded-full flex items-center justify-center mb-4">
                <svg xmlns="http://www.w3.org/2000/svg"
                     class="w-8 h-8 text-green-700"
                     fill="none"
                     viewBox="0 0 24 24"
                     stroke="currentColor">
                    <path stroke-linecap="round"
                          stroke-linejoin="round"
                          stroke-width="1.8"
                          d="M16.5 10.5V6.75a4.5 4.5 0 0 0-9 0v3.75m-.75 11.25h10.5A2.25 2.25 0 0 0 19.5 19.5v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75A2.25 2.25 0 0 0 4.5 12.75v6.75a2.25 2.25 0 0 0 2.25 2.25z"/>
                </svg>
            </div>

            <h2 class="text-2xl font-bold text-gray-800">Redefinir senha</h2>

            <p class="text-gray-500 text-sm mt-2">
                Enviamos um código para:
                <br>
                <strong class="text-gray-700">{{ session('redefinir_senha_email') }}</strong>
            </p>
        </div>

        <!-- SUCESSO -->
        @if(session('success'))
            <div class="bg-green-100 text-green-700 p-3 mb-4 rounded-xl border border-green-200 text-sm">
                {{ session('success') }}
            </div>
        @endif

        <!-- ERRO -->
        @if(session('error'))
            <div class="bg-red-100 text-red-600 p-3 mb-4 rounded-xl border border-red-200 text-sm">
                {{ session('error') }}
            </div>
        @endif

        <!-- VALIDAÇÕES -->
        @if($errors->any())
            <div class="bg-red-100 text-red-600 p-3 mb-4 rounded-xl border border-red-200 text-sm">
                @foreach($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <!-- AVISO -->
        <div id="avisoSenha"
             class="hidden bg-red-50 text-red-700 border border-red-200 p-3 mb-4 rounded-xl text-sm">
        </div>

        <!-- FORM -->
        <form method="POST" action="{{ route('senha.atualizar') }}" class="space-y-4" id="formRedefinirSenha">
            @csrf

            <!-- CÓDIGO -->
            <div>
                <label class="text-sm text-gray-600 font-medium">Código recebido</label>

                <input type="text"
                       name="codigo"
                       id="codigo"
                       maxlength="6"
                       inputmode="numeric"
                       autocomplete="one-time-code"
                       placeholder="Digite o código de 6 dígitos"
                       class="w-full border border-gray-300 p-3 rounded-lg text-gray-800 mt-1 text-center text-xl font-bold tracking-[0.4em] focus:outline-none focus:ring-2 focus:ring-green-600 focus:border-transparent"
                       required>
            </div>

            <!-- NOVA SENHA -->
            <div>
                <label class="text-sm text-gray-600 font-medium">Nova senha</label>

                <div class="relative mt-1">
                    <input type="password"
                           name="senha"
                           id="senha"
                           placeholder="Digite a nova senha"
                           class="w-full border border-gray-300 p-3 rounded-lg text-gray-800 pr-14 focus:outline-none focus:ring-2 focus:ring-green-600 focus:border-transparent"
                           required>

                    <button type="button"
                            onclick="toggleSenha('senha', 'iconeSenha')"
                            class="absolute right-2 top-1/2 -translate-y-1/2 w-10 h-10 rounded-lg flex items-center justify-center text-gray-500 hover:text-green-700 hover:bg-green-50 transition"
                            aria-label="Mostrar ou ocultar senha">
                        <span id="iconeSenha">
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

                <p class="text-xs text-gray-500 mt-2">
                    Use no mínimo 8 caracteres, com letra maiúscula, minúscula e número.
                </p>
            </div>

            <!-- CONFIRMAR SENHA -->
            <div>
                <label class="text-sm text-gray-600 font-medium">Confirmar nova senha</label>

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

            <!-- BOTÃO -->
            <button type="submit"
                    id="btnRedefinirSenha"
                    class="w-full bg-green-700 text-white p-3 rounded-lg font-semibold hover:bg-green-800 transition disabled:bg-gray-400 disabled:cursor-not-allowed">
                Redefinir senha
            </button>
        </form>

        <form method="POST" action="{{ route('senha.reenviar.codigo') }}" class="mt-3">
            @csrf

            <button type="submit"
                    class="w-full bg-gray-100 text-gray-700 p-3 rounded-lg font-semibold hover:bg-gray-200 transition">
                Reenviar código
            </button>
        </form>

        <p class="text-center text-sm mt-4 text-gray-500">
            <a href="{{ route('senha.esqueci') }}" class="text-green-600 font-semibold hover:underline">
                Voltar
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

    function verificarForcaSenha() {
        const senha = document.getElementById('senha').value;
        const barra = document.getElementById('barraSenha');
        const texto = document.getElementById('textoForcaSenha');
        const pontuacao = document.getElementById('pontuacaoSenha');

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

        pontuacao.innerText = pontos + '/5';
        barra.className = 'h-full rounded-full transition-all duration-300';

        if (senha.length === 0) {
            senhaAceita = false;
            barra.classList.add('w-0');
            texto.innerText = 'Digite uma senha';
            texto.className = 'text-xs font-semibold text-gray-500';
        } else if (pontos <= 2) {
            senhaAceita = false;
            barra.classList.add('w-1/3', 'bg-red-500');
            texto.innerText = 'Senha fraca';
            texto.className = 'text-xs font-semibold text-red-600';
        } else if (pontos === 3 || pontos === 4) {
            senhaAceita = true;
            barra.classList.add('w-2/3', 'bg-yellow-500');
            texto.innerText = 'Senha média — aceita';
            texto.className = 'text-xs font-semibold text-yellow-600';
        } else {
            senhaAceita = true;
            barra.classList.add('w-full', 'bg-green-600');
            texto.innerText = 'Senha forte — ótima escolha';
            texto.className = 'text-xs font-semibold text-green-700';
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

    const inputCodigo = document.getElementById('codigo');

    if (inputCodigo) {
        inputCodigo.addEventListener('input', function () {
            this.value = this.value.replace(/\D/g, '').slice(0, 6);
        });
    }

    document.getElementById('senha').addEventListener('input', verificarForcaSenha);
    document.getElementById('confirmarSenha').addEventListener('input', verificarConfirmacaoSenha);

    document.getElementById('formRedefinirSenha').addEventListener('submit', function (e) {
        const senha = document.getElementById('senha').value;
        const confirmarSenha = document.getElementById('confirmarSenha').value;
        const aviso = document.getElementById('avisoSenha');

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

        const btn = document.getElementById('btnRedefinirSenha');
        btn.disabled = true;
        btn.innerText = 'Redefinindo senha...';
    });
</script>

@endsection