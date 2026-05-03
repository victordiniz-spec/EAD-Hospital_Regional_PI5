@extends('layout.app')

@php $noLayout = true; @endphp

@section('title','Verificar E-mail')

@section('content')

<div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-green-100 via-white to-green-200 px-4 py-8">

    <div class="w-full max-w-md bg-white p-8 rounded-2xl shadow-xl border border-gray-200">

        <div class="flex flex-col items-center mb-6">
            <img src="/logo.png" class="w-16 mb-2" alt="Logo">
            <h1 class="text-lg font-semibold text-gray-700">Integrar ReSaúde</h1>
        </div>

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
                          d="M21.75 6.75v10.5A2.25 2.25 0 0 1 19.5 19.5h-15A2.25 2.25 0 0 1 2.25 17.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15A2.25 2.25 0 0 0 2.25 6.75m19.5 0-9.75 6.75L2.25 6.75"/>
                </svg>
            </div>

            <h2 class="text-2xl font-bold text-gray-800">Verifique seu e-mail</h2>

            <p class="text-gray-500 text-sm mt-2">
                Enviamos um código para:
                <br>
                <strong class="text-gray-700">{{ session('cadastro_email') }}</strong>
            </p>
        </div>

        @if(session('success'))
            <div class="bg-green-100 text-green-700 p-3 mb-4 rounded-xl border border-green-200 text-sm">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-100 text-red-600 p-3 mb-4 rounded-xl border border-red-200 text-sm">
                {{ session('error') }}
            </div>
        @endif

        @if($errors->any())
            <div class="bg-red-100 text-red-600 p-3 mb-4 rounded-xl border border-red-200 text-sm">
                @foreach($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('cadastro.verificar.codigo') }}" class="space-y-4">
            @csrf

            <div>
                <label class="text-sm text-gray-600 font-medium">Código de verificação</label>
                <input type="text"
                       name="codigo"
                       maxlength="6"
                       placeholder="Digite o código de 6 dígitos"
                       class="w-full border border-gray-300 p-3 rounded-lg text-gray-800 mt-1 text-center text-xl font-bold tracking-[0.4em] focus:outline-none focus:ring-2 focus:ring-green-600 focus:border-transparent"
                       required>
            </div>

            <button type="submit"
                    class="w-full bg-green-700 text-white p-3 rounded-lg font-semibold hover:bg-green-800 transition">
                Verificar e criar conta
            </button>
        </form>

        <form method="POST" action="{{ route('cadastro.reenviar.codigo') }}" class="mt-3">
            @csrf

            <button type="submit"
                    class="w-full bg-gray-100 text-gray-700 p-3 rounded-lg font-semibold hover:bg-gray-200 transition">
                Reenviar código
            </button>
        </form>

        <p class="text-center text-sm mt-4 text-gray-500">
            <a href="{{ route('cadastro') }}" class="text-green-600 font-semibold hover:underline">
                Voltar ao cadastro
            </a>
        </p>

    </div>

</div>

@endsection