@extends('layout.app')

@section('title','Login')

@section('content')

<div class="min-h-screen flex items-center justify-center bg-gray-100">

<div class="w-full max-w-md bg-white p-8 rounded-2xl shadow-lg">

<h2 class="text-3xl font-bold mb-6 text-center text-blue-600">
Sistema EAD
</h2>

<p class="text-center text-gray-500 mb-6">
Acesse sua conta
</p>

{{-- ERRO --}}
@if(session('erro'))
    <div class="bg-red-100 text-red-700 p-2 mb-4 rounded text-center">
        {{ session('erro') }}
    </div>
@endif

{{-- SUCESSO --}}
@if(session('success'))
    <div class="bg-green-100 text-green-700 p-2 mb-4 rounded text-center">
        {{ session('success') }}
    </div>
@endif

<form method="POST" action="/login">
    @csrf

    <input
    type="email"
    name="email"
    value="{{ old('email') }}"
    placeholder="Email"
    class="w-full border border-gray-300 p-3 mb-4 rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
    required
    >

    <input
    type="password"
    name="password"
    placeholder="Senha"
    class="w-full border border-gray-300 p-3 mb-4 rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
    required
    >

    <button class="w-full bg-blue-600 text-white p-3 rounded hover:bg-blue-700 transition">
        Entrar
    </button>

</form>

</div>

</div>

@endsection