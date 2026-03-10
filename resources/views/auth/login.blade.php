@extends('layout.app')

@section('title','Login')

@section('content')

<div class="max-w-md mx-auto bg-white p-8 rounded shadow">

<h2 class="text-2xl font-bold mb-6 text-center">
Login
</h2>

<form>

<input
type="email"
placeholder="Email"
class="w-full border p-2 mb-4"
>

<input
type="password"
placeholder="Senha"
class="w-full border p-2 mb-4"
>

<button class="w-full bg-blue-600 text-white p-2 rounded">

Entrar

</button>

</form>

</div>

@endsection