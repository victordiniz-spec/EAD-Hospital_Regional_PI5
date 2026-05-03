@extends('layout.app')

@php $noLayout = true; @endphp

@section('title','Esqueci Minha Senha')

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
                          d="M15.75 5.25a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.5 20.25a7.5 7.5 0 0 1 15 0"/>
                </svg>
            </div>

            <h2 class="text-2xl font-bold text-gray-800">Esqueci minha senha</h2>

            <p class="text-gray-500 text-sm mt-2">
                Informe seu CPF e e-mail cadastrado. Enviaremos um código para redefinir sua senha.
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

        <!-- FORM -->
        <form method="POST" action="{{ route('senha.enviar.codigo') }}" class="space-y-4" id="formEsqueciSenha">
            @csrf

            <!-- CPF -->
            <div>
                <label class="text-sm text-gray-600 font-medium">CPF</label>

                <input type="text"
                       name="cpf"
                       id="cpf"
                       value="{{ old('cpf') }}"
                       maxlength="14"
                       placeholder="000.000.000-00"
                       class="w-full border border-gray-300 p-3 rounded-lg text-gray-800 mt-1 focus:outline-none focus:ring-2 focus:ring-green-600 focus:border-transparent"
                       required>
            </div>

            <!-- EMAIL -->
            <div>
                <label class="text-sm text-gray-600 font-medium">E-mail cadastrado</label>

                <input type="email"
                       name="email"
                       value="{{ old('email') }}"
                       placeholder="seu@email.com"
                       class="w-full border border-gray-300 p-3 rounded-lg text-gray-800 mt-1 focus:outline-none focus:ring-2 focus:ring-green-600 focus:border-transparent"
                       required>
            </div>

            <!-- BOTÃO -->
            <button type="submit"
                    id="btnEnviarCodigoSenha"
                    class="w-full bg-green-700 text-white p-3 rounded-lg font-semibold hover:bg-green-800 transition disabled:bg-gray-400 disabled:cursor-not-allowed">
                Enviar código
            </button>
        </form>

        <p class="text-center text-sm mt-4 text-gray-500">
            Lembrou sua senha?
            <a href="/" class="text-green-600 font-semibold hover:underline">
                Voltar ao login
            </a>
        </p>

    </div>

</div>

<script>
    function mascararCPF(valor) {
        return valor
            .replace(/\D/g, '')
            .replace(/(\d{3})(\d)/, '$1.$2')
            .replace(/(\d{3})(\d)/, '$1.$2')
            .replace(/(\d{3})(\d{1,2})$/, '$1-$2')
            .slice(0, 14);
    }

    const cpfInput = document.getElementById('cpf');

    if (cpfInput) {
        cpfInput.addEventListener('input', function () {
            this.value = mascararCPF(this.value);
        });
    }

    const formEsqueciSenha = document.getElementById('formEsqueciSenha');

    if (formEsqueciSenha) {
        formEsqueciSenha.addEventListener('submit', function () {
            const btn = document.getElementById('btnEnviarCodigoSenha');

            if (btn) {
                btn.disabled = true;
                btn.innerText = 'Enviando código...';
            }
        });
    }
</script>

@endsection