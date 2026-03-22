@extends('layout.app')

@section('title','Cadastro de Aluno')

@section('content')

<div class="w-full max-w-md bg-[#1E293B] p-8 rounded-2xl shadow-xl border border-slate-700">

    <h2 class="text-3xl font-bold mb-6 text-center">
        Criar Conta 👨‍🎓
    </h2>

    <p class="text-gray-400 text-center mb-6">
        Preencha os dados para se cadastrar
    </p>

    <!-- ALERTA DE SUCESSO -->
    @if(session('success'))
        <div class="bg-green-500/20 text-green-400 p-3 rounded mb-4 text-center border border-green-500">
            {{ session('success') }}
        </div>
    @endif

    <!-- ALERTA DE ERRO -->
    @if($errors->any())
        <div class="bg-red-500/20 text-red-400 p-3 rounded mb-4 border border-red-500">
            <ul class="list-disc list-inside text-sm">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="/salvar-aluno" enctype="multipart/form-data" class="space-y-4">
        @csrf

        <!-- NOME -->
        <input 
            type="text" 
            name="nome" 
            placeholder="Nome completo"
            class="w-full bg-slate-900 border border-slate-700 p-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
            required
        >

        <!-- EMAIL -->
        <input 
            type="email" 
            name="email" 
            placeholder="Email"
            class="w-full bg-slate-900 border border-slate-700 p-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
            required
        >

        <!-- SENHA -->
        <input 
            type="password" 
            name="senha" 
            placeholder="Senha"
            class="w-full bg-slate-900 border border-slate-700 p-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
            required
        >

        <!-- FOTO -->
        <div>
            <label class="block text-sm text-gray-400 mb-2">
                Foto de Perfil
            </label>

            <input 
                type="file" 
                name="foto" 
                accept="image/*"
                class="w-full text-sm text-gray-400
                file:mr-4 file:py-2 file:px-4
                file:rounded-lg file:border-0
                file:bg-blue-600 file:text-white
                hover:file:bg-blue-700"
            >
        </div>

        <!-- BOTÃO -->
        <button class="w-full bg-blue-600 hover:bg-blue-700 transition p-3 rounded-lg font-semibold">
            Criar Conta
        </button>
    </form>

</div>

@endsection