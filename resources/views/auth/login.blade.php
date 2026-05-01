@extends('layout.app')

@php $noLayout = true; @endphp

@section('title','Login')

@section('content')

<!-- FUNDO COM DEGRADÊ -->
<div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-green-100 via-white to-green-200">

    <div class="w-full max-w-md bg-white p-8 rounded-2xl shadow-2xl border border-gray-200">

        <!-- LOGO -->
        <div class="flex flex-col items-center mb-6">
            <img src="/logo.png" alt="Logo" class="w-16 mb-2">
            <h1 class="text-lg font-semibold text-gray-700">Integrar ReSaúde</h1>
        </div>

        <!-- TÍTULO -->
        <div class="text-center mb-6">
            <h2 class="text-2xl font-bold text-gray-800">Bem-vindo</h2>
            <p class="text-gray-500 text-sm">
                Acesse sua conta para continuar sua jornada.
            </p>
        </div>

        <!-- ALERTAS -->
        @if(session('erro'))
            <div class="bg-red-100 text-red-600 p-3 mb-4 rounded text-center border border-red-300">
                {{ session('erro') }}
            </div>
        @endif

        @if(session('success'))
            <div class="bg-green-100 text-green-600 p-3 mb-4 rounded text-center border border-green-300">
                {{ session('success') }}
            </div>
        @endif

        <!-- FORM -->
        <form method="POST" action="/login" class="space-y-4">
            @csrf

            <!-- CPF -->
            <div>
                <label class="text-sm text-gray-600">CPF</label>
                <input
                    type="text"
                    name="cpf"
                    value="{{ old('cpf') }}"
                    placeholder="000.000.000-00"
                    class="w-full border border-gray-300 p-3 rounded-lg 
                           text-gray-800 placeholder-gray-400
                           focus:outline-none focus:ring-2 focus:ring-green-600"
                    required
                >
            </div>

            <!-- SENHA -->
            <div>
                <div class="flex justify-between items-center">
                    <label class="text-sm text-gray-600">Senha</label>
                    <a href="#" class="text-green-600 text-sm hover:underline">
                        Esqueci minha senha
                    </a>
                </div>

                <div class="relative">
                    <input
                        type="password"
                        name="password"
                        id="password"
                        placeholder="Digite sua senha"
                        class="w-full border border-gray-300 p-3 rounded-lg pr-12
                               text-gray-800 placeholder-gray-400
                               focus:outline-none focus:ring-2 focus:ring-green-600"
                        required
                    >

                    <!-- OLHO MODERNO -->
                    <button 
                        type="button"
                        onclick="toggleSenha()"
                        class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-700"
                    >
                        <!-- OLHO ABERTO -->
                        <svg id="eye-open" xmlns="http://www.w3.org/2000/svg" 
                            class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M2.25 12s3.75-7.5 9.75-7.5S21.75 12 21.75 12s-3.75 7.5-9.75 7.5S2.25 12 2.25 12z"/>
                            <circle cx="12" cy="12" r="3" />
                        </svg>

                        <!-- OLHO FECHADO -->
                        <svg id="eye-closed" xmlns="http://www.w3.org/2000/svg" 
                            class="w-5 h-5 hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M3 3l18 18M10.584 10.587a2 2 0 002.828 2.828"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M9.88 5.092A9.953 9.953 0 0112 4.5c6 0 9.75 7.5 9.75 7.5a15.348 15.348 0 01-4.293 5.774M6.228 6.228A15.29 15.29 0 002.25 12s3.75 7.5 9.75 7.5a9.77 9.77 0 003.195-.537"/>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- CHECKBOX -->
            <div class="flex items-center gap-2 text-sm text-gray-600">
                <input type="checkbox" class="accent-green-600">
                Manter-me conectado
            </div>

            <!-- BOTÃO -->
            <button 
                type="submit"
                class="w-full bg-green-700 hover:bg-green-800 text-white p-3 rounded-lg font-semibold transition shadow-md"
            >
                Entrar →
            </button>
        </form>

        <!-- CADASTRO -->
        <p class="text-center text-sm text-gray-500 mt-6">
            Não possui acesso?
            <a href="{{ route('cadastro.aluno') }}" class="text-green-600 font-semibold hover:underline">
                Criar Conta →
            </a>
        </p>

    </div>
</div>

<!-- SCRIPT CPF -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    const cpfInput = document.querySelector('input[name="cpf"]');

    cpfInput.addEventListener('input', function(e) {
        let v = e.target.value.replace(/\D/g, '');

        v = v.replace(/(\d{3})(\d)/, '$1.$2');
        v = v.replace(/(\d{3})(\d)/, '$1.$2');
        v = v.replace(/(\d{3})(\d{1,2})$/, '$1-$2');

        e.target.value = v;
    });
});
</script>

<!-- SCRIPT OLHO SENHA MELHORADO -->
<script>
function toggleSenha() {
    const input = document.getElementById('password');
    const eyeOpen = document.getElementById('eye-open');
    const eyeClosed = document.getElementById('eye-closed');

    if (input.type === "password") {
        input.type = "text";
        eyeOpen.classList.add("hidden");
        eyeClosed.classList.remove("hidden");
    } else {
        input.type = "password";
        eyeOpen.classList.remove("hidden");
        eyeClosed.classList.add("hidden");
    }
}
</script>

@endsection