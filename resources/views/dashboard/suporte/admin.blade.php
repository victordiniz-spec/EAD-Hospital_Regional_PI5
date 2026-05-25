@extends('layout.app')

@section('title', 'Gerenciar Central de Suporte')

@section('content')

@php
    $usuario = auth()->user();
@endphp

<style>
    /*
    |--------------------------------------------------------------------------
    | ADMIN CENTRAL DE SUPORTE
    |--------------------------------------------------------------------------
    | Classes próprias para evitar conflito com o CSS global do layout.app.
    */

    .faq-admin-page {
        background: #F3F7F3;
        color: #003C2F;
    }

    .faq-admin-section {
        background: #F3F7F3;
    }

    .faq-admin-title {
        color: #003C2F;
    }

    .faq-admin-muted {
        color: #60756B;
    }

    .faq-admin-card {
        background: #FFFFFF;
        border: 1px solid #E3EBE4;
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.04);
    }

    .faq-admin-soft {
        background: #F8FBF8;
        border: 1px solid #E3EBE4;
    }

    .faq-admin-input {
        background: #FFFFFF;
        border: 1px solid #DCE7DE;
        color: #003C2F;
    }

    .faq-admin-input::placeholder {
        color: #60756B;
    }

    .faq-admin-input:focus {
        outline: none;
        box-shadow: 0 0 0 2px rgba(0, 77, 58, 0.25);
        border-color: #004D3A;
    }

    .faq-admin-tag {
        background: #EAF5EF;
        color: #004D3A;
    }

    .faq-admin-success {
        background: #DCFCE7;
        border: 1px solid #86EFAC;
        color: #166534;
    }

    .faq-admin-error {
        background: #FEE2E2;
        border: 1px solid #FCA5A5;
        color: #991B1B;
    }

    html.dark .faq-admin-page,
    html.dark .faq-admin-section {
        background: #070B14 !important;
        color: #E5E7EB !important;
    }

    html.dark .faq-admin-title {
        color: #FFFFFF !important;
    }

    html.dark .faq-admin-muted {
        color: #AAB7C4 !important;
    }

    html.dark .faq-admin-card {
        background: #101827 !important;
        border-color: #243044 !important;
        box-shadow: 0 12px 30px rgba(0, 0, 0, 0.38) !important;
    }

    html.dark .faq-admin-soft {
        background: #0B1220 !important;
        border-color: #243044 !important;
    }

    html.dark .faq-admin-input {
        background: #0B1220 !important;
        border-color: #243044 !important;
        color: #F8FAFC !important;
    }

    html.dark .faq-admin-input::placeholder {
        color: #718096 !important;
    }

    html.dark .faq-admin-tag {
        background: rgba(22, 101, 52, 0.28) !important;
        color: #41B649 !important;
    }

    html.dark .faq-admin-success {
        background: rgba(22, 101, 52, 0.25) !important;
        border-color: rgba(34, 197, 94, 0.45) !important;
        color: #BBF7D0 !important;
    }

    html.dark .faq-admin-error {
        background: rgba(127, 29, 29, 0.30) !important;
        border-color: rgba(248, 113, 113, 0.45) !important;
        color: #FECACA !important;
    }

    .faq-admin-scroll {
        scrollbar-width: thin;
        scrollbar-color: rgba(0, 77, 58, 0.45) transparent;
    }

    .faq-admin-scroll::-webkit-scrollbar {
        width: 8px;
    }

    .faq-admin-scroll::-webkit-scrollbar-track {
        background: transparent;
    }

    .faq-admin-scroll::-webkit-scrollbar-thumb {
        background: rgba(0, 77, 58, 0.40);
        border-radius: 999px;
    }

    html.dark .faq-admin-scroll {
        scrollbar-color: rgba(65, 182, 73, 0.40) transparent;
    }

    html.dark .faq-admin-scroll::-webkit-scrollbar-thumb {
        background: rgba(65, 182, 73, 0.35);
    }
</style>

<div class="faq-admin-page flex min-h-screen w-full overflow-x-hidden">

    @include('partials.sidebar-professor')

    <main class="flex-1 min-w-0 w-full overflow-x-hidden">

        @include('partials.navbar')

        <section class="faq-admin-section px-4 sm:px-6 lg:px-8 py-6">

            <div class="max-w-7xl mx-auto">

                <div class="mb-6 flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4">
                    <div>
                        <span class="faq-admin-tag inline-flex items-center gap-2 px-4 py-2 rounded-full text-xs font-bold uppercase tracking-wide mb-3">
                            Administração
                        </span>

                        <h1 class="faq-admin-title text-3xl lg:text-4xl font-bold tracking-tight">
                            Gerenciar Central de Suporte
                        </h1>

                        <p class="faq-admin-muted text-sm mt-2 max-w-2xl">
                            Cadastre, edite e organize as dúvidas frequentes exibidas no assistente virtual.
                        </p>
                    </div>

                    <a href="{{ route('suporte.index') }}"
                       class="inline-flex items-center justify-center px-5 py-3 rounded-2xl bg-[#004D3A] text-white font-semibold hover:bg-[#003C2F] transition shadow-sm">
                        Visualizar chat
                    </a>
                </div>

                @if(session('success'))
                    <div class="faq-admin-success mb-6 px-5 py-4 rounded-2xl font-semibold">
                        {{ session('success') }}
                    </div>
                @endif

                @if($errors->any())
                    <div class="faq-admin-error mb-6 px-5 py-4 rounded-2xl">
                        @foreach($errors->all() as $erro)
                            <p>{{ $erro }}</p>
                        @endforeach
                    </div>
                @endif

                <div class="grid lg:grid-cols-[410px_minmax(0,1fr)] gap-6 items-start">

                    {{-- FORMULÁRIO NOVA DÚVIDA --}}
                    <section class="faq-admin-card rounded-3xl p-6 h-fit">
                        <h2 class="faq-admin-title text-xl font-semibold mb-1">
                            Nova dúvida
                        </h2>

                        <p class="faq-admin-muted text-sm mb-6">
                            Preencha a pergunta, resposta e, se quiser, um botão de direcionamento.
                        </p>

                        <form action="{{ route('suporte.store') }}" method="POST" class="space-y-5">
                            @csrf

                            <div>
                                <label class="faq-admin-title block text-sm font-semibold mb-2">
                                    Pergunta
                                </label>

                                <input
                                    type="text"
                                    name="pergunta"
                                    value="{{ old('pergunta') }}"
                                    class="faq-admin-input w-full rounded-2xl px-4 py-3"
                                    placeholder="Ex: Como acessar minhas aulas?"
                                    required
                                >
                            </div>

                            <div>
                                <label class="faq-admin-title block text-sm font-semibold mb-2">
                                    Resposta
                                </label>

                                <textarea
                                    name="resposta"
                                    rows="5"
                                    class="faq-admin-input w-full rounded-2xl px-4 py-3 resize-y"
                                    placeholder="Digite a resposta que será exibida ao usuário..."
                                    required
                                >{{ old('resposta') }}</textarea>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="faq-admin-title block text-sm font-semibold mb-2">
                                        Categoria
                                    </label>

                                    <input
                                        type="text"
                                        name="categoria"
                                        value="{{ old('categoria') }}"
                                        class="faq-admin-input w-full rounded-2xl px-4 py-3"
                                        placeholder="Aulas"
                                    >
                                </div>

                                <div>
                                    <label class="faq-admin-title block text-sm font-semibold mb-2">
                                        Ordem
                                    </label>

                                    <input
                                        type="number"
                                        name="ordem"
                                        value="{{ old('ordem', 0) }}"
                                        class="faq-admin-input w-full rounded-2xl px-4 py-3"
                                    >
                                </div>
                            </div>

                            <div>
                                <label class="faq-admin-title block text-sm font-semibold mb-2">
                                    Texto do botão
                                </label>

                                <input
                                    type="text"
                                    name="texto_botao"
                                    value="{{ old('texto_botao') }}"
                                    class="faq-admin-input w-full rounded-2xl px-4 py-3"
                                    placeholder="Ex: Ir para minhas aulas"
                                >
                            </div>

                            <div>
                                <label class="faq-admin-title block text-sm font-semibold mb-2">
                                    Nome da rota
                                </label>

                                <input
                                    type="text"
                                    name="rota_botao"
                                    value="{{ old('rota_botao') }}"
                                    class="faq-admin-input w-full rounded-2xl px-4 py-3"
                                    placeholder="Ex: aluno.aulas"
                                >

                                <p class="faq-admin-muted text-xs mt-2">
                                    Exemplos: aluno.aulas, certificado.aluno, avisos, dashboard.aluno.
                                </p>
                            </div>

                            <label class="flex items-center gap-3">
                                <input type="checkbox" name="ativo" checked class="w-5 h-5">

                                <span class="faq-admin-title font-semibold">
                                    Exibir na Central de Suporte
                                </span>
                            </label>

                            <button
                                type="submit"
                                class="w-full px-5 py-3 rounded-2xl bg-[#004D3A] text-white font-semibold hover:bg-[#003C2F] transition"
                            >
                                Cadastrar dúvida
                            </button>
                        </form>
                    </section>

                    {{-- LISTAGEM --}}
                    <section class="faq-admin-card rounded-3xl p-6">
                        <div class="mb-6">
                            <h2 class="faq-admin-title text-xl font-semibold">
                                Dúvidas cadastradas
                            </h2>

                            <p class="faq-admin-muted text-sm mt-1">
                                Edite, desative ou remova perguntas existentes.
                            </p>
                        </div>

                        <div class="space-y-4 max-h-[calc(100vh-260px)] overflow-y-auto pr-1 faq-admin-scroll">
                            @forelse($duvidas as $duvida)
                                <div class="faq-admin-soft rounded-3xl p-5">
                                    <form action="{{ route('suporte.update', $duvida->id) }}" method="POST" class="space-y-4">
                                        @csrf
                                        @method('PUT')

                                        <div class="grid md:grid-cols-[1fr_160px] gap-4">
                                            <div>
                                                <label class="faq-admin-muted block text-xs font-bold mb-1">
                                                    Pergunta
                                                </label>

                                                <input
                                                    type="text"
                                                    name="pergunta"
                                                    value="{{ $duvida->pergunta }}"
                                                    class="faq-admin-input w-full rounded-2xl px-4 py-3 font-semibold"
                                                    required
                                                >
                                            </div>

                                            <div>
                                                <label class="faq-admin-muted block text-xs font-bold mb-1">
                                                    Categoria
                                                </label>

                                                <input
                                                    type="text"
                                                    name="categoria"
                                                    value="{{ $duvida->categoria }}"
                                                    class="faq-admin-input w-full rounded-2xl px-4 py-3"
                                                >
                                            </div>
                                        </div>

                                        <div>
                                            <label class="faq-admin-muted block text-xs font-bold mb-1">
                                                Resposta
                                            </label>

                                            <textarea
                                                name="resposta"
                                                rows="3"
                                                class="faq-admin-input w-full rounded-2xl px-4 py-3 resize-y"
                                                required
                                            >{{ $duvida->resposta }}</textarea>
                                        </div>

                                        <div class="grid md:grid-cols-[1fr_1fr_110px] gap-4">
                                            <input
                                                type="text"
                                                name="texto_botao"
                                                value="{{ $duvida->texto_botao }}"
                                                class="faq-admin-input rounded-2xl px-4 py-3"
                                                placeholder="Texto do botão"
                                            >

                                            <input
                                                type="text"
                                                name="rota_botao"
                                                value="{{ $duvida->rota_botao }}"
                                                class="faq-admin-input rounded-2xl px-4 py-3"
                                                placeholder="Nome da rota"
                                            >

                                            <input
                                                type="number"
                                                name="ordem"
                                                value="{{ $duvida->ordem }}"
                                                class="faq-admin-input rounded-2xl px-4 py-3"
                                                placeholder="Ordem"
                                            >
                                        </div>

                                        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                                            <label class="flex items-center gap-3">
                                                <input
                                                    type="checkbox"
                                                    name="ativo"
                                                    class="w-5 h-5"
                                                    {{ $duvida->ativo ? 'checked' : '' }}
                                                >

                                                <span class="faq-admin-title font-semibold">
                                                    Ativo
                                                </span>
                                            </label>

                                            <button
                                                type="submit"
                                                class="px-5 py-3 rounded-2xl bg-[#004D3A] text-white font-semibold hover:bg-[#003C2F] transition"
                                            >
                                                Salvar alterações
                                            </button>
                                        </div>
                                    </form>

                                    <form
                                        action="{{ route('suporte.destroy', $duvida->id) }}"
                                        method="POST"
                                        class="mt-3"
                                        onsubmit="return confirm('Tem certeza que deseja remover esta dúvida?')"
                                    >
                                        @csrf
                                        @method('DELETE')

                                        <button
                                            type="submit"
                                            class="px-4 py-2 rounded-xl bg-red-100 text-red-700 font-semibold hover:bg-red-200 transition"
                                        >
                                            Remover dúvida
                                        </button>
                                    </form>
                                </div>
                            @empty
                                <div class="faq-admin-soft rounded-3xl p-8 text-center">
                                    <p class="faq-admin-title font-semibold">
                                        Nenhuma dúvida cadastrada ainda.
                                    </p>

                                    <p class="faq-admin-muted text-sm mt-1">
                                        Use o formulário ao lado para cadastrar a primeira pergunta.
                                    </p>
                                </div>
                            @endforelse
                        </div>
                    </section>

                </div>
            </div>
        </section>
    </main>
</div>

@endsection