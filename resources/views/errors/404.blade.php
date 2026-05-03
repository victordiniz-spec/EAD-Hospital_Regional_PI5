@extends('errors.layout')

@section('title', '404 - Página não encontrada')

@section('code', '404')

@section('headline', 'Página não encontrada')

@section('message')
A página que você tentou acessar não existe, foi movida ou o endereço digitado está incorreto.
Mas fique tranquilo: você pode voltar ao início e continuar navegando normalmente.
@endsection

@section('illustration')
<div class="absolute inset-0 flex items-center justify-center">
    <div class="relative w-72 h-72">

        <div class="absolute inset-0 rounded-full bg-emerald-100 shadow-inner"></div>

        <div class="absolute inset-8 rounded-full bg-white shadow-xl border border-emerald-100 flex items-center justify-center">
            <div class="text-center">
                <div class="text-7xl mb-3">🔍</div>
                <div class="text-xl font-bold text-slate-700">Oops!</div>
                <div class="text-sm text-slate-500">Não encontramos essa página</div>
            </div>
        </div>

        <div class="absolute -top-3 left-10 w-6 h-6 bg-emerald-300 rounded-full pulseSoft"></div>
        <div class="absolute top-12 -right-2 w-4 h-4 bg-cyan-300 rounded-full pulseSoft"></div>
        <div class="absolute bottom-8 -left-2 w-5 h-5 bg-teal-300 rounded-full pulseSoft"></div>
        <div class="absolute bottom-0 right-12 w-3 h-3 bg-emerald-400 rounded-full pulseSoft"></div>
    </div>
</div>
@endsection