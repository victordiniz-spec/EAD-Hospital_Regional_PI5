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
            <p class="text-gray-500 text-sm">
                Preencha os dados. Enviaremos um código para validar seu e-mail.
            </p>
        </div>

        <!-- SUCESSO -->
        @if(session('success'))
            <div class="bg-green-100 text-green-700 p-3 mb-4 rounded-xl border border-green-200 text-sm">
                {{ session('success') }}
            </div>
        @endif

        <!-- ERROS -->
        @if($errors->any())
            <div class="bg-red-100 text-red-600 p-3 mb-4 rounded-xl border border-red-200 text-sm">
                @foreach($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <!-- AVISO -->
        <div id="avisoCadastro"
             class="hidden bg-red-50 text-red-700 border border-red-200 p-3 mb-4 rounded-xl text-sm">
        </div>

        <!-- FORM -->
        <form method="POST" action="{{ route('salvar.aluno') }}" class="space-y-4" id="formCadastro">
            @csrf

            <!-- NOME -->
            <div>
                <label class="text-sm text-gray-600 font-medium">Nome completo</label>

                <input type="text"
                       name="nome"
                       id="nome"
                       value="{{ old('nome') }}"
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
                       value="{{ old('cpf') }}"
                       placeholder="000.000.000-00"
                       maxlength="14"
                       class="w-full border border-gray-300 p-3 rounded-lg text-gray-800 mt-1 focus:outline-none focus:ring-2 focus:ring-green-600 focus:border-transparent"
                       required>

                <p class="text-xs text-gray-500 mt-1">
                    O sistema não permite cadastro com CPF já utilizado.
                </p>
            </div>

            <!-- EMAIL -->
            <div>
                <label class="text-sm text-gray-600 font-medium">E-mail</label>

                <input type="email"
                       name="email"
                       id="email"
                       value="{{ old('email') }}"
                       placeholder="seu@email.com"
                       class="w-full border border-gray-300 p-3 rounded-lg text-gray-800 mt-1 focus:outline-none focus:ring-2 focus:ring-green-600 focus:border-transparent"
                       required>

                <p class="text-xs text-gray-500 mt-1">
                    Você receberá um código de verificação neste e-mail.
                </p>
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
                            0/7
                        </p>
                    </div>
                </div>

                <!-- REGRAS -->
                <div class="mt-3 bg-gray-50 border border-gray-200 rounded-xl p-3">
                    <p class="text-xs font-semibold text-gray-600 mb-2">
                        Para cadastrar, a senha precisa cumprir todas as condições:
                    </p>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs">
                        <p id="regraTamanho" class="text-gray-500">○ Mínimo 8 caracteres</p>
                        <p id="regraMaiuscula" class="text-gray-500">○ Letra maiúscula</p>
                        <p id="regraMinuscula" class="text-gray-500">○ Letra minúscula</p>
                        <p id="regraNumero" class="text-gray-500">○ Número</p>
                        <p id="regraEspecial" class="text-gray-500">○ Símbolo especial, exemplo: @ # !</p>
                        <p id="regraNaoComum" class="text-gray-500">○ Não usar senha comum</p>
                        <p id="regraNaoDados" class="text-gray-500 sm:col-span-2">○ Não usar seu nome ou e-mail na senha</p>
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
                        <input type="radio"
                               name="tipo"
                               value="residente"
                               class="hidden peer"
                               {{ old('tipo') === 'residente' ? 'checked' : '' }}
                               required>

                        <div class="border rounded-xl p-4 text-center transition hover:border-green-500 peer-checked:border-green-600 peer-checked:bg-green-50 peer-checked:ring-2 peer-checked:ring-green-100">
                            <div class="text-3xl mb-2">👨‍⚕️</div>
                            <p class="text-gray-700 font-medium">Residente</p>
                        </div>
                    </label>

                    <label class="cursor-pointer">
                        <input type="radio"
                               name="tipo"
                               value="preceptor"
                               class="hidden peer"
                               {{ old('tipo') === 'preceptor' ? 'checked' : '' }}>

                        <div class="border rounded-xl p-4 text-center transition hover:border-green-500 peer-checked:border-green-600 peer-checked:bg-green-50 peer-checked:ring-2 peer-checked:ring-green-100">
                            <div class="text-3xl mb-2">🧑‍🏫</div>
                            <p class="text-gray-700 font-medium">Preceptor</p>
                        </div>
                    </label>

                </div>
            </div>

            <!-- BOTÃO -->
            <button type="submit"
                    id="btnEnviar"
                    disabled
                    class="w-full bg-gray-400 text-white p-3 rounded-lg font-semibold transition cursor-not-allowed">
                Crie uma senha válida para continuar
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

    const senhasBloqueadas = [
        '123', '1234', '12345', '123456', '1234567', '12345678', '123456789', '1234567890',
        '000000', '111111', '222222', '333333', '444444', '555555', '666666', '777777', '888888', '999999',
        'admin', 'admin123', 'teste', 'teste123', 'senha', 'senha123', 'password', 'password123',
        'qwerty', 'qwerty123', 'abc123', 'abcd1234',
        'integrar', 'integrar123', 'resaude', 'resaude123', 'integrarresaude', 'integrarresaude123'
    ];

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

    function limparTexto(texto) {
        return (texto || '')
            .toLowerCase()
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .replace(/[^a-z0-9]/g, '');
    }

    function senhaTemSequencia(senha) {
        const s = limparTexto(senha);

        const sequencias = [
            '0123456789',
            '9876543210',
            'abcdefghijklmnopqrstuvwxyz',
            'zyxwvutsrqponmlkjihgfedcba',
            'qwertyuiop',
            'poiuytrewq'
        ];

        return sequencias.some(seq => {
            for (let i = 0; i <= seq.length - 5; i++) {
                if (s.includes(seq.substring(i, i + 5))) {
                    return true;
                }
            }

            return false;
        });
    }

    function senhaTemRepeticao(senha) {
        const s = limparTexto(senha);
        return /(.)\1{4,}/.test(s);
    }

    function senhaUsaDadosPessoais(senha) {
        const senhaLimpa = limparTexto(senha);
        const nomeDigitado = document.getElementById('nome')?.value || '';
        const emailDigitado = document.getElementById('email')?.value || '';

        const nomeCompleto = limparTexto(nomeDigitado);
        const emailUsuario = limparTexto(emailDigitado.split('@')[0]);

        if (!senhaLimpa) return false;

        if (nomeCompleto.length >= 4 && senhaLimpa.includes(nomeCompleto)) {
            return true;
        }

        if (emailUsuario.length >= 4 && senhaLimpa.includes(emailUsuario)) {
            return true;
        }

        const palavrasNome = nomeDigitado
            .toLowerCase()
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .split(/\s+/)
            .map(p => p.replace(/[^a-z0-9]/g, ''))
            .filter(p => p.length >= 4);

        return palavrasNome.some(palavra => senhaLimpa.includes(palavra));
    }

    function senhaComumOuInsegura(senha) {
        const senhaLimpa = limparTexto(senha);

        if (!senhaLimpa) return false;

        if (senhasBloqueadas.includes(senhaLimpa)) return true;

        if (/^\d+$/.test(senhaLimpa)) return true;

        if (senhaTemSequencia(senhaLimpa)) return true;

        if (senhaTemRepeticao(senhaLimpa)) return true;

        return false;
    }

    function atualizarRegra(id, valido, texto) {
        const elemento = document.getElementById(id);

        if (!elemento) return;

        if (valido) {
            elemento.innerText = '✓ ' + texto;
            elemento.className = 'text-green-700 font-semibold';
        } else {
            elemento.innerText = '○ ' + texto;
            elemento.className = 'text-gray-500';
        }
    }

    function atualizarBotaoCadastro() {
        const btn = document.getElementById('btnEnviar');

        if (!btn) return;

        if (senhaAceita) {
            btn.disabled = false;
            btn.innerText = 'Enviar código de verificação';
            btn.className = 'w-full bg-green-700 text-white p-3 rounded-lg font-semibold hover:bg-green-800 transition cursor-pointer';
        } else {
            btn.disabled = true;
            btn.innerText = 'Crie uma senha válida para continuar';
            btn.className = 'w-full bg-gray-400 text-white p-3 rounded-lg font-semibold transition cursor-not-allowed';
        }
    }

    function listarRegrasPendentes(regras) {
        const pendentes = [];

        if (!regras.temTamanho) pendentes.push('mínimo 8 caracteres');
        if (!regras.temMaiuscula) pendentes.push('letra maiúscula');
        if (!regras.temMinuscula) pendentes.push('letra minúscula');
        if (!regras.temNumero) pendentes.push('número');
        if (!regras.temEspecial) pendentes.push('símbolo especial');
        if (!regras.naoComum) pendentes.push('não usar senha comum, sequência ou apenas números');
        if (!regras.naoUsaDados) pendentes.push('não usar nome ou e-mail');

        return pendentes;
    }

    function verificarForcaSenha() {
        const senha = document.getElementById('senha').value;
        const barra = document.getElementById('barraSenha');
        const texto = document.getElementById('textoForcaSenha');
        const pontuacao = document.getElementById('pontuacaoSenha');
        const aviso = document.getElementById('avisoCadastro');

        const regras = {
            temTamanho: senha.length >= 8,
            temMaiuscula: /[A-Z]/.test(senha),
            temMinuscula: /[a-z]/.test(senha),
            temNumero: /[0-9]/.test(senha),
            temEspecial: /[^A-Za-z0-9]/.test(senha),
            naoComum: senha.length > 0 && !senhaComumOuInsegura(senha),
            naoUsaDados: senha.length > 0 && !senhaUsaDadosPessoais(senha),
        };

        let pontos = 0;

        Object.values(regras).forEach(valido => {
            if (valido) pontos++;
        });

        atualizarRegra('regraTamanho', regras.temTamanho, 'Mínimo 8 caracteres');
        atualizarRegra('regraMaiuscula', regras.temMaiuscula, 'Letra maiúscula');
        atualizarRegra('regraMinuscula', regras.temMinuscula, 'Letra minúscula');
        atualizarRegra('regraNumero', regras.temNumero, 'Número');
        atualizarRegra('regraEspecial', regras.temEspecial, 'Símbolo especial, exemplo: @ # !');
        atualizarRegra('regraNaoComum', regras.naoComum, 'Não usar senha comum');
        atualizarRegra('regraNaoDados', regras.naoUsaDados, 'Não usar seu nome ou e-mail na senha');

        senhaAceita = pontos === 7;

        pontuacao.innerText = pontos + '/7';

        barra.className = 'h-full rounded-full transition-all duration-300';

        if (senha.length === 0) {
            barra.classList.add('w-0');
            texto.innerText = 'Digite uma senha';
            texto.className = 'text-xs font-semibold text-gray-500';
            aviso.classList.add('hidden');
        } else if (pontos < 7) {
            const pendentes = listarRegrasPendentes(regras);

            barra.classList.add(pontos <= 3 ? 'w-1/3' : 'w-2/3', pontos <= 3 ? 'bg-red-500' : 'bg-yellow-500');

            texto.innerText = 'Senha incompleta — ' + pontos + '/7 condições';
            texto.className = pontos <= 3
                ? 'text-xs font-semibold text-red-600'
                : 'text-xs font-semibold text-yellow-600';

            aviso.innerHTML = 'A senha ainda não atende todas as condições do sistema. Falta: <strong>' + pendentes.join(', ') + '</strong>.';
            aviso.classList.remove('hidden');
        } else {
            barra.classList.add('w-full', 'bg-green-600');
            texto.innerText = 'Senha válida — todas as condições foram atendidas';
            texto.className = 'text-xs font-semibold text-green-700';
            aviso.classList.add('hidden');
        }

        verificarConfirmacaoSenha();
        atualizarBotaoCadastro();
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
    document.getElementById('nome').addEventListener('input', verificarForcaSenha);
    document.getElementById('email').addEventListener('input', verificarForcaSenha);

    document.getElementById('cpf').addEventListener('input', function () {
        this.value = mascararCPF(this.value);
    });

    document.getElementById('formCadastro').addEventListener('submit', function (e) {
        const senha = document.getElementById('senha').value;
        const confirmarSenha = document.getElementById('confirmarSenha').value;
        const aviso = document.getElementById('avisoCadastro');

        verificarForcaSenha();

        if (!senhaAceita) {
            e.preventDefault();

            aviso.innerHTML = 'A senha não está dentro das condições do sistema. Complete todos os requisitos marcados antes de continuar.';
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
        btn.innerText = 'Enviando código...';
        btn.className = 'w-full bg-gray-400 text-white p-3 rounded-lg font-semibold transition cursor-not-allowed';
    });

    verificarForcaSenha();
</script>

@endsection