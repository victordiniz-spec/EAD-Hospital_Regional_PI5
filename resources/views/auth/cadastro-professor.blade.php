@extends('layout.app')

@section('title','Cadastro de Professor')

@section('content')

<div class="max-w-md mx-auto bg-white p-8 rounded shadow">

<h2 class="text-2xl font-bold mb-6 text-center">
Cadastro de Professor
</h2>

<form method="POST" action="/salvar-professor">

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

<button class="w-full bg-blue-600 text-white p-2 rounded">

Cadastrar Professor

</button>

</form>

</div>

@endsection