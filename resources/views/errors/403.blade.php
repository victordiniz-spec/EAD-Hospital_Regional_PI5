@extends('errors.layout')

@section('title', '403 - Acesso negado')

@section('code', '403')

@section('headline', 'Acesso negado')

@section('message')
Você não tem permissão para acessar esta área do sistema.
Se acredita que isso é um erro, entre em contato com o administrador ou aguarde a liberação de acesso.
@endsection

@section('illustration')
<div class="absolute inset-0 flex items-center justify-center">
    <div class="relative w-72 h-72">

        <div class="absolute inset-0 rounded-full bg-red-100 shadow-inner"></div>

        <div class="absolute inset-8 rounded-full bg-white shadow-xl border border-red-100 flex items-center justify-center">
            <div class="text-center">
                <div class="text-7xl mb-3">🔒</div>
                <div class="text-xl font-bold text-slate-700">Acesso restrito</div>
                <div class="text-sm text-slate-500">Você não possui autorização</div>
            </div>
        </div>

        <div class="absolute -top-3 left-10 w-6 h-6 bg-red-300 rounded-full pulseSoft"></div>
        <div class="absolute top-12 -right-2 w-4 h-4 bg-orange-300 rounded-full pulseSoft"></div>
        <div class="absolute bottom-8 -left-2 w-5 h-5 bg-red-200 rounded-full pulseSoft"></div>
        <div class="absolute bottom-0 right-12 w-3 h-3 bg-orange-400 rounded-full pulseSoft"></div>
    </div>
</div>
@endsection