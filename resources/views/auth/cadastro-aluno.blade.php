@extends('layout.app')

@section('title','Cadastro de Aluno')

@section('content')

<div class="max-w-md mx-auto bg-white p-8 rounded shadow">

    <h2 class="text-2xl font-bold mb-6 text-center">
        Cadastro de Aluno
    </h2>

    <!-- ALERTA DE SUCESSO -->
    @if(session('success'))
        <div class="bg-green-100 text-green-800 p-2 rounded mb-4 text-center">
            {{ session('success') }}
        </div>
    @endif

    <!-- ALERTA DE ERRO -->
    @if($errors->any())
        <div class="bg-red-100 text-red-800 p-2 rounded mb-4">
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="/salvar-aluno" enctype="multipart/form-data">
        @csrf

        <input 
            type="text" 
            name="nome" 
            placeholder="Nome" 
            class="w-full border p-2 mb-4"
            required
        >

        <input 
            type="email" 
            name="email" 
            placeholder="Email" 
            class="w-full border p-2 mb-4"
            required
        >

        <input 
            type="password" 
            name="senha" 
            placeholder="Senha" 
            class="w-full border p-2 mb-4"
            required
        >

        <!-- CAMPO FOTO -->
        <label class="mb-2 block">Foto do Aluno</label>
        <input 
            type="file" 
            name="foto" 
            accept="image/*" 
            class="w-full border p-2 mb-4"
        >

        <button class="w-full bg-green-600 text-white p-2 rounded">
            Cadastrar Aluno
        </button>
    </form>
</div>

@endsection