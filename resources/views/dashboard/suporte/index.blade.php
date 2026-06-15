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

    .faq-suggestion-btn {
        background: #EAF5EF;
        color: #004D3A;
        border: 1px solid #DCE7DE;
        transition: 0.2s ease;
    }

    .faq-suggestion-btn:hover {
        background: #DFF1E5;
        transform: translateY(-1px);
    }

    .faq-typing-dot {
        width: 7px;
        height: 7px;
        border-radius: 999px;
        background: #60756B;
        display: inline-block;
        animation: faqTyping 1s infinite ease-in-out;
    }

    .faq-typing-dot:nth-child(2) {
        animation-delay: 0.15s;
    }

    .faq-typing-dot:nth-child(3) {
        animation-delay: 0.30s;
    }

    @keyframes faqTyping {
        0%, 80%, 100% {
            opacity: 0.35;
            transform: translateY(0);
        }

        40% {
            opacity: 1;
            transform: translateY(-3px);
        }
    }

    html.dark .faq-suggestion-btn {
        background: rgba(22, 101, 52, 0.28) !important;
        color: #41B649 !important;
        border-color: #243044 !important;
    }

    html.dark .faq-suggestion-btn:hover {
        background: rgba(22, 101, 52, 0.42) !important;
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
                                Clique em uma pergunta ou digite sua dúvida como se estivesse conversando.
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
                                        Digite sua dúvida ou escolha uma pergunta
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
                                        Sou o assistente virtual da plataforma. Você pode escolher uma pergunta pronta ou digitar sua dúvida abaixo.
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
                        <button type="button"
                                onclick="mostrarDuvidasCategoria('aulas')"
                                class="faq-suggestion-btn px-3 py-2 rounded-full text-xs font-extrabold">
                            Aulas
                        </button>

                        <button type="button"
                                onclick="mostrarDuvidasCategoria('pos-testes')"
                                class="faq-suggestion-btn px-3 py-2 rounded-full text-xs font-extrabold">
                            Pós-testes
                        </button>

                        <button type="button"
                                onclick="mostrarDuvidasCategoria('prova final')"
                                class="faq-suggestion-btn px-3 py-2 rounded-full text-xs font-extrabold">
                            Prova final
                        </button>

                        <button type="button"
                                onclick="mostrarDuvidasCategoria('certificado')"
                                class="faq-suggestion-btn px-3 py-2 rounded-full text-xs font-extrabold">
                            Certificados
                        </button>

                        <button type="button"
                                onclick="mostrarDuvidasCategoria('avisos')"
                                class="faq-suggestion-btn px-3 py-2 rounded-full text-xs font-extrabold">
                            Avisos
                        </button>

                        <button type="button"
                                onclick="mostrarDuvidasCategoria('progresso')"
                                class="faq-suggestion-btn px-3 py-2 rounded-full text-xs font-extrabold">
                            Progresso
                        </button>

                        <button type="button"
                                onclick="mostrarDuvidasCategoria('acesso')"
                                class="faq-suggestion-btn px-3 py-2 rounded-full text-xs font-extrabold">
                            Acesso
                        </button>
                    </div>
                                </div>
                            </div>

                        </div>

                        <div class="faq-card rounded-none border-x-0 border-b-0 p-4 shrink-0">
                            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                                <input
                                    id="campoPerguntaLivre"
                                    type="text"
                                    onkeydown="enviarPerguntaComEnter(event)"
                                    class="faq-input flex-1 px-5 py-3 rounded-2xl font-semibold focus:outline-none focus:ring-2 focus:ring-[#004D3A]"
                                    placeholder="Digite sua dúvida aqui... Ex: como vejo meu certificado?"
                                >

                                <button
                                    type="button"
                                    onclick="responderPerguntaLivre()"
                                    class="px-5 py-3 rounded-2xl bg-[#004D3A] text-white font-extrabold hover:bg-[#003C2F] transition"
                                >
                                    Perguntar
                                </button>

                                <button
                                    type="button"
                                    onclick="limparChat()"
                                    class="px-5 py-3 rounded-2xl bg-[#EAF5EF] text-[#004D3A] font-extrabold hover:bg-[#DFF1E5] transition"
                                >
                                    Limpar
                                </button>
                            </div>

                            <p class="faq-muted text-xs mt-3">
                                O assistente procura a melhor resposta dentro das dúvidas cadastradas pela administração.
                            </p>
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


    function mostrarDuvidasCategoria(categoriaEscolhida) {
        const categoriaNormalizada = normalizarTexto(categoriaEscolhida);

        const relacionadas = duvidas
            .filter((duvida) => {
                const categoria = normalizarTexto(duvida.categoria || '');
                const texto = normalizarTexto([
                    duvida.pergunta,
                    duvida.resposta,
                    duvida.categoria
                ].join(' '));

                if (categoria === categoriaNormalizada) {
                    return true;
                }

                const palavras = palavrasChaveCategoria(categoriaNormalizada);
                return palavras.some((palavra) => texto.includes(palavra));
            })
            .slice(0, 6);

        if (relacionadas.length === 0) {
            adicionarMensagemAssistente(`
                <p class="faq-title font-extrabold">
                    Ainda não há dúvidas cadastradas sobre ${escapeHtml(categoriaEscolhida)}.
                </p>

                <p class="faq-muted text-sm mt-2">
                    A administração pode cadastrar novas perguntas sobre esse assunto na Central de Suporte.
                </p>
            `);
            return;
        }

        const botoes = relacionadas.map((duvida) => `
            <button type="button"
                    onclick="selecionarDuvida(${duvida.id})"
                    class="faq-question w-full text-left p-4 rounded-2xl transition">
                <span class="faq-tag inline-flex mb-2 px-2 py-1 rounded-full text-[10px] font-extrabold uppercase tracking-wide">
                    ${escapeHtml(duvida.categoria || categoriaEscolhida)}
                </span>

                <p class="font-extrabold leading-snug">
                    ${escapeHtml(duvida.pergunta)}
                </p>
            </button>
        `).join('');

        adicionarMensagemAssistente(`
            <p class="faq-title font-extrabold">
                Encontrei estas dúvidas sobre ${escapeHtml(categoriaEscolhida)}:
            </p>

            <p class="faq-muted text-sm mt-2 mb-4">
                Clique em uma pergunta abaixo para receber a resposta automática.
            </p>

            <div class="grid grid-cols-1 gap-3">
                ${botoes}
            </div>
        `);
    }

    function selecionarDuvida(id) {
        const duvida = duvidas.find(item => item.id === id);

        if (!duvida) {
            return;
        }

        adicionarMensagemUsuario(duvida.pergunta);
        responderComDuvida(duvida);
    }

    function responderPerguntaLivre() {
        const campo = document.getElementById('campoPerguntaLivre');
        const pergunta = (campo?.value || '').trim();

        if (!pergunta) {
            return;
        }

        adicionarMensagemUsuario(pergunta);

        if (campo) {
            campo.value = '';
        }

        const resultado = encontrarMelhorResposta(pergunta);

        mostrarDigitando();

        setTimeout(() => {
            removerDigitando();

            if (resultado.melhor && resultado.pontuacao >= 4) {
                responderComDuvida(resultado.melhor, resultado.pontuacao);
            } else {
                responderSemResposta(pergunta, resultado.sugestoes);
            }
        }, 650);
    }

    function enviarPerguntaComEnter(evento) {
        if (evento.key === 'Enter') {
            evento.preventDefault();
            responderPerguntaLivre();
        }
    }

    function encontrarMelhorResposta(pergunta) {
        const perguntaNormalizada = normalizarTexto(pergunta);
        const palavrasPergunta = obterPalavrasImportantes(perguntaNormalizada);
        const intencao = detectarIntencaoSuporte(perguntaNormalizada);

        const avaliadas = duvidas.map((duvida) => {
            const categoria = normalizarTexto(duvida.categoria || '');
            const perguntaFaq = normalizarTexto(duvida.pergunta || '');
            const respostaFaq = normalizarTexto(duvida.resposta || '');

            const textoBase = [
                perguntaFaq,
                respostaFaq,
                categoria,
                palavrasChaveCategoria(categoria).join(' ')
            ].join(' ');

            let pontuacao = 0;

            // Correspondência forte: pergunta muito parecida.
            if (perguntaFaq.includes(perguntaNormalizada) || perguntaNormalizada.includes(perguntaFaq)) {
                pontuacao += 10;
            }

            // Se a pergunta do usuário indica uma categoria, prioriza essa categoria.
            if (intencao && categoria === intencao) {
                pontuacao += 8;
            }

            // Se a pergunta indica uma categoria diferente, penaliza para evitar resposta errada.
            if (intencao && categoria && categoria !== intencao) {
                pontuacao -= 5;
            }

            palavrasPergunta.forEach((palavra) => {
                if (perguntaFaq.includes(palavra)) {
                    pontuacao += 3;
                } else if (textoBase.includes(palavra)) {
                    pontuacao += 1;
                }
            });

            // Bônus por palavras equivalentes da categoria.
            if (intencao) {
                palavrasChaveCategoria(intencao).forEach((palavra) => {
                    if (perguntaNormalizada.includes(palavra) && categoria === intencao) {
                        pontuacao += 2;
                    }
                });
            }

            return {
                ...duvida,
                pontuacao
            };
        }).sort((a, b) => b.pontuacao - a.pontuacao);

        const melhor = avaliadas[0] || null;
        const melhorPontuacao = melhor?.pontuacao || 0;

        /*
        |--------------------------------------------------------------------------
        | TRAVA DE SEGURANÇA
        |--------------------------------------------------------------------------
        | Se o usuário perguntou sobre "avisos", por exemplo, o sistema não deve
        | responder com "certificado" só porque encontrou uma palavra solta.
        */
        const categoriaMelhor = normalizarTexto(melhor?.categoria || '');

        if (intencao && categoriaMelhor && categoriaMelhor !== intencao && melhorPontuacao < 12) {
            return {
                melhor: null,
                pontuacao: 0,
                sugestoes: avaliadas
                    .filter(item => normalizarTexto(item.categoria || '') === intencao)
                    .slice(0, 3)
            };
        }

        return {
            melhor,
            pontuacao: melhorPontuacao,
            sugestoes: avaliadas.filter(item => item.pontuacao > 0).slice(0, 3)
        };
    }

    function detectarIntencaoSuporte(texto) {
        const categorias = {
            'avisos': ['aviso', 'avisos', 'notificacao', 'notificacoes', 'comunicado', 'comunicados', 'alerta', 'alertas', 'sino', 'mensagem'],
            'certificado': ['certificado', 'certificados', 'certificacao', 'emitir', 'emissao', 'liberar certificado', 'meu certificado'],
            'prova final': ['prova', 'prova final', 'final', 'nota final', 'avaliacao final', 'fazer prova'],
            'pos-testes': ['pos teste', 'posteste', 'pos-testes', 'pos testes', 'teste', 'testes', 'questionario', 'atividade'],
            'aulas': ['aula', 'aulas', 'videoaula', 'videoaulas', 'video', 'assistir', 'curso', 'modulo'],
            'progresso': ['progresso', 'pendencia', 'pendente', 'faltando', 'concluir', 'conclusao', 'porcentagem'],
            'acesso': ['login', 'senha', 'entrar', 'acessar', 'cadastro', 'conta', 'usuario', 'aprovacao']
        };

        for (const [categoria, palavras] of Object.entries(categorias)) {
            if (palavras.some(palavra => texto.includes(palavra))) {
                return categoria;
            }
        }

        return null;
    }

    function palavrasChaveCategoria(categoria) {
        const mapa = {
            'avisos': ['aviso', 'avisos', 'notificacao', 'notificacoes', 'comunicado', 'alerta', 'sino', 'mensagem'],
            'certificado': ['certificado', 'certificacao', 'emissao', 'liberado', 'bloqueado'],
            'prova final': ['prova', 'final', 'nota', 'aprovacao', 'tentativa'],
            'pos-testes': ['pos teste', 'posteste', 'teste', 'questionario', 'atividade'],
            'aulas': ['aula', 'videoaula', 'curso', 'modulo', 'assistir', 'tempo'],
            'progresso': ['progresso', 'pendente', 'concluir', 'porcentagem', 'requisito'],
            'acesso': ['login', 'senha', 'cadastro', 'conta', 'aprovacao', 'acesso']
        };

        return mapa[categoria] || [];
    }

    function responderComDuvida(duvida, pontuacao = null) {
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

        const confiancaHtml = pontuacao !== null
            ? `<p class="faq-muted text-xs mt-3">Resposta encontrada automaticamente na base de dúvidas.</p>`
            : '';

        adicionarMensagemAssistente(`
            ${categoriaHtml}

            <p class="faq-title font-extrabold mb-2">
                ${escapeHtml(duvida.pergunta)}
            </p>

            <p class="leading-relaxed whitespace-pre-line">
                ${escapeHtml(duvida.resposta)}
            </p>

            ${botaoHtml}
            ${confiancaHtml}
        `);
    }

    function responderSemResposta(pergunta, sugestoes) {
        let sugestoesHtml = '';

        if (sugestoes && sugestoes.length > 0) {
            sugestoesHtml = `
                <p class="faq-title font-bold mt-4 mb-2">
                    Talvez uma dessas opções ajude:
                </p>

                <div class="flex flex-wrap gap-2">
                    ${sugestoes.map(item => `
                        <button type="button"
                                onclick="selecionarDuvida(${item.id})"
                                class="faq-suggestion-btn px-3 py-2 rounded-full text-xs font-extrabold">
                            ${escapeHtml(item.pergunta)}
                        </button>
                    `).join('')}
                </div>
            `;
        }

        adicionarMensagemAssistente(`
            <p class="faq-title font-extrabold">
                Ainda não encontrei uma resposta exata.
            </p>

            <p class="faq-muted text-sm mt-2 leading-relaxed">
                Tente escrever com outras palavras ou procure uma pergunta na lista ao lado.
                Se essa dúvida for importante, avise a administração para cadastrar uma nova resposta na Central de Suporte.
            </p>

            ${sugestoesHtml}
        `);
    }

    function adicionarMensagemUsuario(texto) {
        const chat = document.getElementById('chatMensagens');

        const html = `
            <div class="flex items-start justify-end gap-3">
                <div class="max-w-3xl bg-[#004D3A] text-white rounded-3xl rounded-tr-md px-5 py-4 shadow-sm">
                    <p class="font-extrabold">${escapeHtml(texto)}</p>
                </div>

                <div class="w-10 h-10 rounded-full bg-[#41B649] text-white flex items-center justify-center font-bold shrink-0">
                    ${escapeHtml(inicialUsuario)}
                </div>
            </div>
        `;

        chat.insertAdjacentHTML('beforeend', html);
        rolarChatParaFinal();
    }

    function adicionarMensagemAssistente(conteudoHtml) {
        const chat = document.getElementById('chatMensagens');

        const html = `
            <div class="flex items-start gap-3">
                <div class="w-10 h-10 rounded-full bg-[#004D3A] text-white flex items-center justify-center font-bold shrink-0">
                    IR
                </div>

                <div class="faq-bubble max-w-3xl rounded-3xl rounded-tl-md px-5 py-4 shadow-sm">
                    ${conteudoHtml}
                </div>
            </div>
        `;

        chat.insertAdjacentHTML('beforeend', html);
        rolarChatParaFinal();
    }

    function mostrarDigitando() {
        const chat = document.getElementById('chatMensagens');

        const html = `
            <div id="mensagemDigitandoSuporte" class="flex items-start gap-3">
                <div class="w-10 h-10 rounded-full bg-[#004D3A] text-white flex items-center justify-center font-bold shrink-0">
                    IR
                </div>

                <div class="faq-bubble rounded-3xl rounded-tl-md px-5 py-4 shadow-sm">
                    <span class="faq-typing-dot"></span>
                    <span class="faq-typing-dot"></span>
                    <span class="faq-typing-dot"></span>
                </div>
            </div>
        `;

        chat.insertAdjacentHTML('beforeend', html);
        rolarChatParaFinal();
    }

    function removerDigitando() {
        document.getElementById('mensagemDigitandoSuporte')?.remove();
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
                        Sou o assistente virtual da plataforma. Você pode escolher uma pergunta pronta ou digitar sua dúvida abaixo.
                    </p>
                </div>
            </div>

            <div class="flex items-start gap-3">
                <div class="w-10 h-10 rounded-full bg-[#004D3A] text-white flex items-center justify-center font-bold shrink-0">
                    IR
                </div>

                <div class="faq-bubble max-w-3xl rounded-3xl rounded-tl-md px-5 py-4 shadow-sm">
                    <p class="faq-title font-bold">
                        Escolha um assunto para ver dúvidas possíveis:
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

        const termo = normalizarTexto(campo.value);
        const itens = document.querySelectorAll('.duvida-item');

        itens.forEach(item => {
            const texto = normalizarTexto(item.getAttribute('data-pergunta') || '');
            item.style.display = texto.includes(termo) ? 'block' : 'none';
        });
    }

    function obterPalavrasImportantes(texto) {
        const palavrasIgnoradas = [
            'como', 'onde', 'qual', 'quais', 'para', 'porque', 'por que',
            'minha', 'meu', 'meus', 'minhas', 'uma', 'umas', 'uns', 'com',
            'que', 'tem', 'fazer', 'ver', 'abrir', 'acessar', 'preciso',
            'sobre', 'esta', 'esse', 'isso', 'de', 'do', 'da', 'dos', 'das',
            'o', 'a', 'os', 'as', 'e', 'em', 'no', 'na'
        ];

        return texto
            .split(/\s+/)
            .map(palavra => palavra.trim())
            .filter(palavra => palavra.length >= 3 && !palavrasIgnoradas.includes(palavra));
    }

    function normalizarTexto(texto) {
        return String(texto ?? '')
            .toLowerCase()
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .replace(/[^\w\s]/g, ' ')
            .replace(/\s+/g, ' ')
            .trim();
    }

    function rolarChatParaFinal() {
        const chat = document.getElementById('chatMensagens');

        setTimeout(() => {
            chat.scrollTo({
                top: chat.scrollHeight,
                behavior: 'smooth'
            });
        }, 80);
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