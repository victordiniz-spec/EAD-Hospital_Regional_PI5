@extends('layout.app')

@section('title','Cadastro de Aluno')

@section('content')

<div class="w-full max-w-md bg-[#1E293B] p-8 rounded-2xl shadow-xl border border-slate-700">

    <div class="flex justify-center mb-4">
        <div class="bg-green-600 p-3 rounded-full text-xl">
            👨‍🎓
        </div>
    </div>

    <h2 class="text-2xl font-bold mb-2 text-center">
        Cadastro de Aluno
    </h2>

    <p class="text-gray-400 text-center mb-6">
        Preencha os dados abaixo
    </p>

    @if(session('success'))
        <div class="bg-green-500/20 text-green-400 p-3 mb-4 rounded text-center border border-green-500">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="bg-red-500/20 text-red-400 p-3 mb-4 rounded border border-red-500">
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="/salvar-aluno" enctype="multipart/form-data" class="space-y-4">
        @csrf

        <input
            type="text"
            name="nome"
            placeholder="Nome completo"
            class="w-full bg-slate-900 text-white border border-slate-700 p-3 rounded-lg 
                   focus:outline-none focus:ring-2 focus:ring-green-500"
            required
        >

        <input
            type="email"
            name="email"
            placeholder="Email"
            class="w-full bg-slate-900 text-white border border-slate-700 p-3 rounded-lg 
                   focus:outline-none focus:ring-2 focus:ring-green-500"
            required
        >

        <input
            type="password"
            name="senha"
            placeholder="Senha"
            class="w-full bg-slate-900 text-white border border-slate-700 p-3 rounded-lg 
                   focus:outline-none focus:ring-2 focus:ring-green-500"
            required
        >

        <div>
            <label class="block mb-2 text-gray-300">Foto do Aluno</label>
            <input
                type="file"
                name="foto"
                accept="image/*"
                class="w-full bg-slate-900 text-white border border-slate-700 p-2 rounded-lg"
            >
        </div>

        <button class="w-full bg-green-600 hover:bg-green-700 transition p-3 rounded-lg font-semibold">
            Cadastrar Aluno
        </button>

    </form>

</div>

@endsection