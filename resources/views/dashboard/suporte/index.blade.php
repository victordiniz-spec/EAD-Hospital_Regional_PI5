@extends('layout.app')

@section('title', 'Central de Dúvidas')

@section('content')

@php
    $usuario = auth()->user();
    $tipoUsuario = $usuario->tipo ?? null;
    $ehAdministrativo = in_array($tipoUsuario, ['super_admin', 'admin', 'professor']);

    $rotaPainel = $ehAdministrativo
        ? (Route::has('dashboard.professor') ? route('dashboard.professor') : url('/dashboard-professor'))
        : (Route::has('dashboard.aluno') ? route('dashboard.aluno') : url('/dashboard-aluno'));

    $duvidasChat = $duvidas->map(function ($duvida) {
        return [
            'id' => $duvida->id,
            'pergunta' => $duvida->pergunta,
            'resposta' => $duvida->resposta,
            'categoria' => $duvida->categoria,
            'texto_botao' => $duvida->texto_botao,
            'url_botao' => ($duvida->rota_botao && Route::has($duvida->rota_botao))
                ? route($duvida->rota_botao)
                : null,
        ];
    })->values();

    $nomeUsuario = $usuario->name ?? 'Usuário';
    $inicialUsuario = strtoupper(substr($nomeUsuario, 0, 1));
@endphp

<style>
    /*
    |--------------------------------------------------------------------------
    | CENTRAL DE DÚVIDAS
    |--------------------------------------------------------------------------
    | Classes próprias para evitar conflito com o CSS global do layout.app.
    | Não usamos bg-white/dark:bg-* nos cards principais para não bater com
    | regras globais com !important.
    */

    .faq-page {
        background: #F3F7F3;
        color: #003C2F;
    }

    .faq-section {
        background: #F3F7F3;
    }

    .faq-title {
        color: #003C2F;
    }

    .faq-muted {
        color: #60756B;
    }

    .faq-card {
        background: #FFFFFF;
        border: 1px solid #E3EBE4;
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.04);
    }

    .faq-soft {
        background: #F8FBF8;
    }

    .faq-question {
        background: #FFFFFF;
        border: 1px solid #E3EBE4;
        color: #003C2F;
    }

    .faq-question:hover {
        background: #F1F6F2;
        border-color: #004D3A;
    }

    .faq-bubble {
        background: #FFFFFF;
        border: 1px solid #E3EBE4;
        color: #34463B;
    }

    .faq-input {
        background: #F8FBF8;
        border: 1px solid #DCE7DE;
        color: #003C2F;
    }

    .faq-input::placeholder {
        color: #60756B;
    }

    .faq-tag {
        background: #EAF5EF;
        color: #004D3A;
    }

    .faq-footer-input {
        background: #F4F7F5;
        border: 1px solid #E3EBE4;
        color: #60756B;
    }

    html.dark .faq-page,
    html.dark .faq-section {
        background: #070B14 !important;
        color: #E5E7EB !important;
    }

    html.dark .faq-title {
        color: #FFFFFF !important;
    }

    html.dark .faq-muted {
        color: #AAB7C4 !important;
    }

    html.dark .faq-card {
        background: #101827 !important;
        border-color: #243044 !important;
        box-shadow: 0 12px 30px rgba(0, 0, 0, 0.38) !important;
    }

    html.dark .faq-soft {
        background: #0B1220 !important;
    }

    html.dark .faq-question {
        background: #0B1220 !important;
        border-color: #243044 !important;
        color: #FFFFFF !important;
    }

    html.dark .faq-question:hover {
        background: #111C2E !important;
        border-color: #00A63E !important;
        color: #FFFFFF !important;
    }

    html.dark .faq-bubble {
        background: #101827 !important;
        border-color: #243044 !important;
        color: #E5E7EB !important;
    }

    html.dark .faq-input {
        background: #0B1220 !important;
        border-color: #243044 !important;
        color: #F8FAFC !important;
    }

    html.dark .faq-input::placeholder {
        color: #718096 !important;
    }

    html.dark .faq-tag {
        background: rgba(22, 101, 52, 0.28) !important;
        color: #41B649 !important;
    }

    html.dark .faq-footer-input {
        background: #0B1220 !important;
        border-color: #243044 !important;
        color: #AAB7C4 !important;
    }

    .faq-scroll {
        scrollbar-width: thin;
        scrollbar-color: rgba(0, 77, 58, 0.45) transparent;
    }

    .faq-scroll::-webkit-scrollbar {
        width: 8px;
    }

    .faq-scroll::-webkit-scrollbar-track {
        background: transparent;
    }

    .faq-scroll::-webkit-scrollbar-thumb {
        background: rgba(0, 77, 58, 0.40);
        border-radius: 999px;
    }

    html.dark .faq-scroll {
        scrollbar-color: rgba(65, 182, 73, 0.40) transparent;
    }

    html.dark .faq-scroll::-webkit-scrollbar-thumb {
        background: rgba(65, 182, 73, 0.35);
    }

    @media (max-width: 1024px) {
        .faq-panel-height {
            height: auto !important;
            min-height: auto !important;
            max-height: none !important;
        }

        .faq-mobile-scroll {
            max-height: 420px;
        }
    }
</style>

<div class="faq-page flex min-h-screen w-full overflow-x-hidden">

    @if($ehAdministrativo)
        @include('partials.sidebar-professor')
    @else
        @include('partials.sidebar-aluno')
    @endif

    <main class="flex-1 min-w-0 w-full overflow-x-hidden">

        @include('partials.navbar')

        <section class="faq-section px-4 sm:px-6 lg:px-8 py-6">

            <div class="max-w-7xl mx-auto">

                <div class="mb-6 flex flex-col xl:flex-row xl:items-end xl:justify-between gap-4">

                    <div>
                        <span class="faq-tag inline-flex items-center gap-2 px-4 py-2 rounded-full text-xs font-extrabold uppercase tracking-wide mb-3">
                            Assistente virtual
                        </span>

                        <h1 class="faq-title text-3xl sm:text-4xl font-extrabold tracking-tight">
                            Central de dúvidas
                        </h1>

                        <p class="faq-muted text-sm mt-2 max-w-2xl">
                            Encontre respostas rápidas sobre aulas, avaliações, certificados, acesso e uso da plataforma.
                        </p>
                    </div>

                    <div class="flex flex-col sm:flex-row gap-3">
                        @if($ehAdministrativo)
                            <a href="{{ route('suporte.admin') }}"
                               class="bg-[#004D3A] hover:bg-[#003C2F] text-white px-5 py-3 rounded-2xl transition flex items-center justify-center gap-2 text-sm font-extrabold shadow-sm">
                                Gerenciar dúvidas
                            </a>
                        @endif

                        <a href="{{ $rotaPainel }}"
                           class="faq-card px-5 py-3 rounded-2xl transition flex items-center justify-center gap-2 text-sm font-extrabold text-[#004D3A]">
                            Voltar ao painel
                        </a>
                    </div>

                </div>

                <div class="grid lg:grid-cols-[350px_minmax(0,1fr)] gap-6 items-start">

                    {{-- LISTA DE DÚVIDAS --}}
                    <aside class="faq-card faq-panel-height rounded-3xl p-5 h-[calc(100vh-250px)] min-h-[440px] max-h-[600px] flex flex-col">

                        <div class="shrink-0 mb-5">
                            <h2 class="faq-title text-xl font-extrabold">
                                Dúvidas rápidas
                            </h2>

                            <p class="faq-muted text-sm mt-1">
                                Clique em uma pergunta para receber uma resposta automática.
                            </p>
                        </div>

                        <div class="shrink-0 mb-4">
                            <input
                                id="campoBuscaDuvida"
                                type="text"
                                oninput="filtrarDuvidas()"
                                placeholder="Buscar dúvida..."
                                class="faq-input w-full px-4 py-3 rounded-2xl focus:outline-none focus:ring-2 focus:ring-[#004D3A]"
                            >
                        </div>

                        <div id="listaDuvidas" class="faq-mobile-scroll faq-scroll flex-1 overflow-y-auto pr-1 space-y-3">
                            @forelse($duvidas as $duvida)
                                <button
                                    type="button"
                                    data-pergunta="{{ strtolower($duvida->pergunta . ' ' . $duvida->categoria) }}"
                                    onclick="selecionarDuvida({{ $duvida->id }})"
                                    class="faq-question duvida-item w-full text-left p-4 rounded-2xl transition group"
                                >
                                    @if($duvida->categoria)
                                        <span class="faq-tag inline-flex mb-2 px-2 py-1 rounded-full text-[10px] font-extrabold uppercase tracking-wide">
                                            {{ $duvida->categoria }}
                                        </span>
                                    @endif

                                    <p class="font-extrabold leading-snug">
                                        {{ $duvida->pergunta }}
                                    </p>
                                </button>
                            @empty
                                <div class="faq-soft p-5 rounded-2xl text-center">
                                    <p class="faq-title font-extrabold">
                                        Nenhuma dúvida cadastrada.
                                    </p>

                                    <p class="faq-muted text-sm mt-1">
                                        A administração ainda não cadastrou perguntas frequentes.
                                    </p>
                                </div>
                            @endforelse
                        </div>

                    </aside>

                    {{-- CHAT --}}
                    <section class="faq-card faq-panel-height rounded-3xl overflow-hidden h-[calc(100vh-250px)] min-h-[440px] max-h-[600px] flex flex-col">

                        <div class="bg-[#004D3A] px-6 py-4 text-white shrink-0">
                            <div class="flex items-center gap-4">
                                <div class="w-11 h-11 rounded-full bg-white text-[#004D3A] flex items-center justify-center font-extrabold shadow-sm">
                                    IR
                                </div>

                                <div class="min-w-0">
                                    <h2 class="text-lg sm:text-xl font-extrabold leading-tight">
                                        Assistente Integrar ReSaúde
                                    </h2>

                                    <p class="text-sm text-white/80">
                                        FAQ interativo com respostas automáticas
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div id="chatMensagens" class="faq-soft faq-mobile-scroll faq-scroll flex-1 p-5 lg:p-6 space-y-5 overflow-y-auto">

                            <div class="flex items-start gap-3">
                                <div class="w-10 h-10 rounded-full bg-[#004D3A] text-white flex items-center justify-center font-bold shrink-0">
                                    IR
                                </div>

                                <div class="faq-bubble max-w-3xl rounded-3xl rounded-tl-md px-5 py-4 shadow-sm">
                                    <p class="faq-title font-extrabold">
                                        Olá, {{ $nomeUsuario }}!
                                    </p>

                                    <p class="faq-muted text-sm mt-1">
                                        Sou o assistente virtual da plataforma. Escolha uma pergunta ao lado para que eu possa te orientar.
                                    </p>
                                </div>
                            </div>

                            <div class="flex items-start gap-3">
                                <div class="w-10 h-10 rounded-full bg-[#004D3A] text-white flex items-center justify-center font-bold shrink-0">
                                    IR
                                </div>

                                <div class="faq-bubble max-w-3xl rounded-3xl rounded-tl-md px-5 py-4 shadow-sm">
                                    <p class="faq-title font-bold">
                                        Posso ajudar com:
                                    </p>

                                    <div class="flex flex-wrap gap-2 mt-3">
                                        <span class="faq-tag px-3 py-2 rounded-full text-xs font-extrabold">Aulas</span>
                                        <span class="faq-tag px-3 py-2 rounded-full text-xs font-extrabold">Avaliações</span>
                                        <span class="faq-tag px-3 py-2 rounded-full text-xs font-extrabold">Certificados</span>
                                        <span class="faq-tag px-3 py-2 rounded-full text-xs font-extrabold">Acesso</span>
                                    </div>
                                </div>
                            </div>

                        </div>

                        <div class="faq-card rounded-none border-x-0 border-b-0 p-4 shrink-0">
                            <div class="flex items-center gap-3">
                                <div class="faq-footer-input flex-1 px-5 py-3 rounded-2xl font-semibold">
                                    Selecione uma dúvida na lista para iniciar...
                                </div>

                                <button
                                    type="button"
                                    onclick="limparChat()"
                                    class="px-5 py-3 rounded-2xl bg-[#004D3A] text-white font-extrabold hover:bg-[#003C2F] transition"
                                >
                                    Limpar
                                </button>
                            </div>
                        </div>

                    </section>

                </div>

            </div>

        </section>

    </main>

</div>

<script>
    const duvidas = @json($duvidasChat);
    const nomeUsuario = @json($nomeUsuario);
    const inicialUsuario = @json($inicialUsuario);

    function selecionarDuvida(id) {
        const duvida = duvidas.find(item => item.id === id);

        if (!duvida) {
            return;
        }

        const chat = document.getElementById('chatMensagens');

        const perguntaHtml = `
            <div class="flex items-start justify-end gap-3">
                <div class="max-w-3xl bg-[#004D3A] text-white rounded-3xl rounded-tr-md px-5 py-4 shadow-sm">
                    <p class="font-extrabold">${escapeHtml(duvida.pergunta)}</p>
                </div>

                <div class="w-10 h-10 rounded-full bg-[#41B649] text-white flex items-center justify-center font-bold shrink-0">
                    ${escapeHtml(inicialUsuario)}
                </div>
            </div>
        `;

        let botaoHtml = '';

        if (duvida.url_botao && duvida.texto_botao) {
            botaoHtml = `
                <a href="${duvida.url_botao}"
                   class="inline-flex mt-4 px-5 py-3 rounded-2xl bg-[#004D3A] text-white font-extrabold hover:bg-[#003C2F] transition">
                    ${escapeHtml(duvida.texto_botao)}
                </a>
            `;
        }

        const categoriaHtml = duvida.categoria
            ? `
                <span class="faq-tag inline-flex mb-3 px-3 py-1 rounded-full text-[10px] font-extrabold uppercase tracking-wide">
                    ${escapeHtml(duvida.categoria)}
                </span>
            `
            : '';

        const respostaHtml = `
            <div class="flex items-start gap-3">
                <div class="w-10 h-10 rounded-full bg-[#004D3A] text-white flex items-center justify-center font-bold shrink-0">
                    IR
                </div>

                <div class="faq-bubble max-w-3xl rounded-3xl rounded-tl-md px-5 py-4 shadow-sm">
                    ${categoriaHtml}

                    <p class="leading-relaxed whitespace-pre-line">
                        ${escapeHtml(duvida.resposta)}
                    </p>

                    ${botaoHtml}
                </div>
            </div>
        `;

        chat.insertAdjacentHTML('beforeend', perguntaHtml);
        chat.insertAdjacentHTML('beforeend', respostaHtml);

        setTimeout(() => {
            chat.scrollTo({
                top: chat.scrollHeight,
                behavior: 'smooth'
            });
        }, 80);
    }

    function limparChat() {
        const chat = document.getElementById('chatMensagens');

        chat.innerHTML = `
            <div class="flex items-start gap-3">
                <div class="w-10 h-10 rounded-full bg-[#004D3A] text-white flex items-center justify-center font-bold shrink-0">
                    IR
                </div>

                <div class="faq-bubble max-w-3xl rounded-3xl rounded-tl-md px-5 py-4 shadow-sm">
                    <p class="faq-title font-extrabold">
                        Olá, ${escapeHtml(nomeUsuario)}!
                    </p>

                    <p class="faq-muted text-sm mt-1">
                        Sou o assistente virtual da plataforma. Escolha uma pergunta ao lado para que eu possa te orientar.
                    </p>
                </div>
            </div>

            <div class="flex items-start gap-3">
                <div class="w-10 h-10 rounded-full bg-[#004D3A] text-white flex items-center justify-center font-bold shrink-0">
                    IR
                </div>

                <div class="faq-bubble max-w-3xl rounded-3xl rounded-tl-md px-5 py-4 shadow-sm">
                    <p class="faq-title font-bold">
                        Posso ajudar com:
                    </p>

                    <div class="flex flex-wrap gap-2 mt-3">
                        <span class="faq-tag px-3 py-2 rounded-full text-xs font-extrabold">Aulas</span>
                        <span class="faq-tag px-3 py-2 rounded-full text-xs font-extrabold">Avaliações</span>
                        <span class="faq-tag px-3 py-2 rounded-full text-xs font-extrabold">Certificados</span>
                        <span class="faq-tag px-3 py-2 rounded-full text-xs font-extrabold">Acesso</span>
                    </div>
                </div>
            </div>
        `;
    }

    function filtrarDuvidas() {
        const campo = document.getElementById('campoBuscaDuvida');

        if (!campo) {
            return;
        }

        const termo = campo.value.toLowerCase();
        const itens = document.querySelectorAll('.duvida-item');

        itens.forEach(item => {
            const texto = item.getAttribute('data-pergunta') || '';
            item.style.display = texto.includes(termo) ? 'block' : 'none';
        });
    }

    function escapeHtml(texto) {
        return String(texto ?? '')
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;');
    }
</script>

@endsection