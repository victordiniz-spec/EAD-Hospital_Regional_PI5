@extends('errors.layout')

@section('title', '500 - Erro interno')

@section('code', '500')

@section('headline', 'Erro interno do servidor')

@section('message')
Ocorreu um problema inesperado ao processar sua solicitação.
Nossa equipe pode verificar isso em breve. Tente novamente em alguns instantes.
@endsection

@section('illustration')
<div class="absolute inset-0 flex items-center justify-center">
    <div class="relative w-72 h-72">

        <div class="absolute inset-0 rounded-full bg-amber-100 shadow-inner"></div>

        <div class="absolute inset-8 rounded-full bg-white shadow-xl border border-amber-100 flex items-center justify-center">
            <div class="text-center">
                <div class="text-7xl mb-3">⚙️</div>
                <div class="text-xl font-bold text-slate-700">Algo deu errado</div>
                <div class="text-sm text-slate-500">Estamos trabalhando nisso</div>
            </div>
        </div>

        <div class="absolute -top-3 left-10 w-6 h-6 bg-amber-300 rounded-full pulseSoft"></div>
        <div class="absolute top-12 -right-2 w-4 h-4 bg-yellow-300 rounded-full pulseSoft"></div>
        <div class="absolute bottom-8 -left-2 w-5 h-5 bg-orange-300 rounded-full pulseSoft"></div>
        <div class="absolute bottom-0 right-12 w-3 h-3 bg-amber-400 rounded-full pulseSoft"></div>
    </div>
</div>
@endsection