@extends('layout.app')

@section('title','Login')

@section('content')

<div class="w-full max-w-md bg-[#1E293B] p-8 rounded-2xl shadow-xl border border-slate-700">

    <div class="flex justify-center mb-4">
        <div class="bg-blue-600 p-3 rounded-full text-xl">
            🔐
        </div>
    </div>

    <h2 class="text-3xl font-bold mb-2 text-center">
        Sistema EAD
    </h2>

    <p class="text-gray-400 text-center mb-6">
        Acesse sua conta
    </p>

    @if(session('erro'))
        <div class="bg-red-500/20 text-red-400 p-3 mb-4 rounded text-center border border-red-500">
            {{ session('erro') }}
        </div>
    @endif

    @if(session('success'))
        <div class="bg-green-500/20 text-green-400 p-3 mb-4 rounded text-center border border-green-500">
            {{ session('success') }}
        </div>
    @endif

    <form method="POST" action="/login" class="space-y-4">
        @csrf

        <input
            type="email"
            name="email"
            value="{{ old('email') }}"
            placeholder="Digite seu email"
            class="w-full bg-slate-900 text-white border border-slate-700 p-3 rounded-lg 
                   focus:outline-none focus:ring-2 focus:ring-blue-500 placeholder-gray-400"
            required
        >

        <input
            type="password"
            name="password"
            placeholder="Digite sua senha"
            class="w-full bg-slate-900 text-white border border-slate-700 p-3 rounded-lg 
                   focus:outline-none focus:ring-2 focus:ring-blue-500 placeholder-gray-400"
            required
        >

        <button class="w-full bg-blue-600 hover:bg-blue-700 transition p-3 rounded-lg font-semibold">
            Entrar
        </button>
    </form>

</div>

@endsection