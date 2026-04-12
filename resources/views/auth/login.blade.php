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
                        class="w-full border border-gray-300 p-3 rounded-lg pr-10
                               text-gray-800 placeholder-gray-400
                               focus:outline-none focus:ring-2 focus:ring-green-600"
                        required
                    >

                    <!-- OLHO -->
                    <button 
                        type="button"
                        onclick="toggleSenha()"
                        class="absolute right-3 top-3 text-gray-500 hover:text-gray-700"
                    >
                        👁️
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

<!-- SCRIPT OLHO SENHA -->
<script>
function toggleSenha() {
    const input = document.getElementById('password');

    if (input.type === "password") {
        input.type = "text";
    } else {
        input.type = "password";
    }
}
</script>

@endsection