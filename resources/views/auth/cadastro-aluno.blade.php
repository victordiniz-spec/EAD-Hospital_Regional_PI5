@extends('layout.app')

@php $noLayout = true; @endphp

@section('title','Criar Conta')

@section('content')

<div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-green-100 via-white to-green-200">

    <div class="w-full max-w-lg bg-white p-8 rounded-2xl shadow-xl border border-gray-200">

        <!-- LOGO -->
        <div class="flex flex-col items-center mb-6">
            <img src="/logo.png" class="w-16 mb-2">
            <h1 class="text-lg font-semibold text-gray-700">Integrar ReSaúde</h1>
        </div>

        <!-- TÍTULO -->
        <div class="text-center mb-6">
            <h2 class="text-2xl font-bold text-gray-800">Criar nova conta</h2>
            <p class="text-gray-500 text-sm">Preencha os dados e crie sua conta</p>
        </div>

        <!-- ERROS -->
        @if($errors->any())
            <div class="bg-red-100 text-red-600 p-3 mb-4 rounded">
                @foreach($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <!-- FORM -->
        <form method="POST" action="/salvar-aluno" class="space-y-4">
            @csrf

            <!-- NOME -->
            <input type="text" name="nome" placeholder="Digite seu nome"
                class="w-full border p-3 rounded-lg text-gray-800" required>

            <!-- CPF -->
            <input type="text" name="cpf" placeholder="000.000.000-00"
                class="w-full border p-3 rounded-lg text-gray-800" required>

            <!-- EMAIL -->
            <input type="email" name="email" placeholder="seu@email.com"
                class="w-full border p-3 rounded-lg text-gray-800" required>

            <!-- SENHA -->
            <div class="relative">
                <input type="password" name="senha" id="senha"
                    placeholder="Digite sua senha"
                    class="w-full border p-3 rounded-lg text-gray-800 pr-10" required>

                <button type="button" onclick="toggleSenha()"
                    class="absolute right-3 top-3 text-gray-500">
                    👁️
                </button>
            </div>

            <!-- TIPO -->
            <div>
                <label class="text-sm text-gray-600">Tipo de usuário</label>

                <div class="grid grid-cols-2 gap-4 mt-3">

                    <label class="cursor-pointer">
                        <input type="radio" name="tipo" value="residente" class="hidden peer" required>

                        <div class="border rounded-xl p-4 text-center transition
                                    hover:border-green-500
                                    peer-checked:border-green-600 
                                    peer-checked:bg-green-50">

                            <div class="text-3xl mb-2">👨‍⚕️</div>
                            <p class="text-gray-700 font-medium">Residente</p>
                        </div>
                    </label>

                    <label class="cursor-pointer">
                        <input type="radio" name="tipo" value="preceptor" class="hidden peer">

                        <div class="border rounded-xl p-4 text-center transition
                                    hover:border-green-500
                                    peer-checked:border-green-600 
                                    peer-checked:bg-green-50">

                            <div class="text-3xl mb-2">🧑‍🏫</div>
                            <p class="text-gray-700 font-medium">Preceptor</p>
                        </div>
                    </label>

                </div>
            </div>

            <!-- BOTÃO -->
            <button class="w-full bg-green-700 text-white p-3 rounded-lg font-semibold hover:bg-green-800 transition">
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
function toggleSenha() {
    let s = document.getElementById('senha');
    s.type = s.type === 'password' ? 'text' : 'password';
}
</script>

@endsection