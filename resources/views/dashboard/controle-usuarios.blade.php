@extends('layout.app')

@section('title', 'Controle de Usuários')

@section('content')

@php
    $timezoneSistema = config('app.timezone', 'America/Sao_Paulo');
    $timezoneBrasil = 'America/Sao_Paulo';

    $totalUsuarios = $usuarios->count();

    $usuariosAtivos = $usuarios->where('status', 'aprovado')->count();
    $usuariosPendentes = $usuarios->where('status', 'pendente')->count();
    $usuariosInutilizados = $usuarios->where('status', 'inutilizado')->count();

    $preceptoresAtivos = $usuarios->filter(function ($user) {
        return strtolower($user->tipo ?? '') === 'preceptor' && $user->status === 'aprovado';
    })->count();

    $residentesAtivos = $usuarios->filter(function ($user) {
        return strtolower($user->tipo ?? '') === 'residente' && $user->status === 'aprovado';
    })->count();
@endphp

<style>
    html, body {
        background: #F3F7F3 !important;
        margin: 0;
        padding: 0;
        width: 100%;
        min-height: 100%;
    }

    #app {
        background: #F3F7F3 !important;
        min-height: 100vh;
        width: 100%;
    }

    .usuario-selecionado {
        border-color: #00A63E !important;
        box-shadow: 0 18px 45px rgba(0, 166, 62, 0.18) !important;
        transform: translateY(-2px);
    }

    .usuario-card {
        user-select: none;
        -webkit-user-select: none;
        -webkit-touch-callout: none;
    }

    .menu-contexto-usuario {
        position: fixed;
        z-index: 9999;
        display: none;
    }

    .modal-confirmacao-icone {
        box-shadow: 0 14px 30px rgba(0, 77, 58, 0.14);
    }

    .modal-confirmacao-entrada {
        animation: modalConfirmacaoEntrada 0.22s ease-out;
    }

    @keyframes modalConfirmacaoEntrada {
        from {
            opacity: 0;
            transform: translateY(14px) scale(0.97);
        }

        to {
            opacity: 1;
            transform: translateY(0) scale(1);
        }
    }

</style>

<div class="flex min-h-screen w-full bg-[#F3F7F3] text-[#003C2F] overflow-x-hidden">

    @include('partials.sidebar-professor')

    <main class="flex-1 min-w-0 w-full bg-[#F3F7F3] overflow-x-hidden">

        @include('partials.navbar')

        <section class="p-4 sm:p-6 lg:p-8">

            <!-- CABEÇALHO -->
            <div class="mb-8">

                <div class="max-w-4xl mx-auto text-center">

                    <h1 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold text-[#003C2F] tracking-tight leading-tight">
                        Controle de Usuários
                    </h1>

                    <p class="text-base sm:text-lg text-[#60756B] mt-4 max-w-2xl mx-auto leading-relaxed">
                        Administre acessos, perfis, datas de cadastro, aprovações, inutilizações e permissões da instituição.
                    </p>

                    <p class="text-xs sm:text-sm text-[#8A9B92] mt-3">
                        Dica: clique em um usuário para destacar. No computador, use o botão direito. No celular, pressione e segure para abrir as ações.
                    </p>

                </div>

                <!-- PESQUISA E BOTÕES -->
                <div class="mt-8 max-w-6xl mx-auto">

                    <div class="flex flex-col xl:flex-row items-stretch justify-center gap-4">

                        <div class="relative flex-1 max-w-3xl">
                            <span class="absolute inset-y-0 left-5 flex items-center text-[#8A9B92] pointer-events-none">
                                <svg xmlns="http://www.w3.org/2000/svg"
                                     class="w-6 h-6 sm:w-7 sm:h-7"
                                     fill="none"
                                     viewBox="0 0 24 24"
                                     stroke="currentColor">
                                    <path stroke-linecap="round"
                                          stroke-linejoin="round"
                                          stroke-width="1.8"
                                          d="m21 21-4.35-4.35m1.35-5.65a7 7 0 1 1-14 0 7 7 0 0 1 14 0z"/>
                                </svg>
                            </span>

                            <input
                                type="text"
                                id="pesquisaUsuarios"
                                onkeyup="filtrarUsuarios()"
                                placeholder="Pesquisar aluno, CPF, e-mail, tipo ou status..."
                                class="w-full h-[64px] sm:h-[72px] bg-white border-2 border-[#00A63E] text-[#003C2F] placeholder-[#8A9B92] rounded-[24px] sm:rounded-[26px] pl-14 sm:pl-17 pr-5 sm:pr-6 text-sm sm:text-lg shadow-sm focus:outline-none focus:ring-4 focus:ring-green-100 focus:border-[#00A63E] transition"
                            >
                        </div>

                        <button type="button"
                                onclick="abrirModalFiltros()"
                                class="h-[60px] sm:h-[72px] bg-[#EAF5EF] border border-[#DCE7DE] text-[#004D3A] px-6 sm:px-7 rounded-[22px] sm:rounded-[26px] hover:bg-[#DFF1E5] transition flex items-center justify-center gap-3 text-sm sm:text-base font-extrabold shadow-sm min-w-full sm:min-w-[230px] xl:min-w-[230px]">

                            <svg xmlns="http://www.w3.org/2000/svg"
                                 class="w-5 h-5 sm:w-6 sm:h-6"
                                 fill="none"
                                 viewBox="0 0 24 24"
                                 stroke="currentColor">
                                <path stroke-linecap="round"
                                      stroke-linejoin="round"
                                      stroke-width="1.8"
                                      d="M10.5 6h9.75M10.5 12h9.75M10.5 18h9.75M3.75 6h.008v.008H3.75V6zm0 6h.008v.008H3.75V12zm0 6h.008v.008H3.75V18z"/>
                            </svg>

                            <span>Filtros Avançados</span>
                        </button>

                        <button type="button"
                                onclick="limparPesquisa()"
                                class="h-[60px] sm:h-[72px] bg-white border border-[#DCE7DE] text-[#60756B] px-6 sm:px-7 rounded-[22px] sm:rounded-[26px] hover:bg-[#F8FBF8] transition flex items-center justify-center gap-3 text-sm sm:text-base font-bold shadow-sm min-w-full sm:min-w-[160px] xl:min-w-[160px]">

                            <svg xmlns="http://www.w3.org/2000/svg"
                                 class="w-5 h-5 sm:w-6 sm:h-6"
                                 fill="none"
                                 viewBox="0 0 24 24"
                                 stroke="currentColor">
                                <path stroke-linecap="round"
                                      stroke-linejoin="round"
                                      stroke-width="1.8"
                                      d="M16.023 9.348h4.992M2.985 19.644v-4.992m0 0h4.992m-4.992 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M7.977 14.652H2.985m18.03-9.296v4.992m0 0h-4.992m4.992 0-3.181-3.183a8.25 8.25 0 0 0-13.803 3.7"/>
                            </svg>

                            <span>Limpar</span>
                        </button>

                    </div>

                </div>

            </div>

            <!-- ALERTAS -->
            @if(session('success'))
                <div class="mb-5 bg-green-100 text-green-700 px-4 py-3 rounded-2xl border border-green-200 shadow-sm">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="mb-5 bg-red-100 text-red-700 px-4 py-3 rounded-2xl border border-red-200 shadow-sm">
                    {{ session('error') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-5 bg-red-100 text-red-700 px-4 py-3 rounded-2xl border border-red-200 shadow-sm">
                    <p class="font-bold mb-2">Corrija os campos abaixo:</p>

                    <ul class="list-disc pl-5 text-sm">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- DADOS GERAIS -->
            <div class="mb-8">

                <div class="flex items-center gap-2 mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg"
                         class="w-5 h-5 text-[#004D3A]"
                         fill="none"
                         viewBox="0 0 24 24"
                         stroke="currentColor">
                        <path stroke-linecap="round"
                              stroke-linejoin="round"
                              stroke-width="1.8"
                              d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 .504 1.125 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125z"/>
                    </svg>

                    <h2 class="text-xl font-extrabold text-[#003C2F]">
                        Dados Gerais de Usuários
                    </h2>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-6 gap-4">

                    <div class="bg-white rounded-3xl p-5 shadow-sm border border-[#E3EBE4] border-l-4 border-l-[#004D3A] hover:shadow-lg transition">
                        <p class="text-[11px] uppercase tracking-widest text-[#60756B] font-extrabold">
                            Total de usuários
                        </p>

                        <h3 class="text-3xl font-extrabold mt-2 text-[#003C2F]">
                            {{ $totalUsuarios }}
                        </h3>

                        <p class="text-xs text-[#60756B] mt-2">
                            Todos os registros do sistema.
                        </p>
                    </div>

                    <div class="bg-white rounded-3xl p-5 shadow-sm border border-[#E3EBE4] border-l-4 border-l-[#00A63E] hover:shadow-lg transition">
                        <p class="text-[11px] uppercase tracking-widest text-[#60756B] font-extrabold">
                            Usuários ativos
                        </p>

                        <h3 class="text-3xl font-extrabold mt-2 text-green-700">
                            {{ $usuariosAtivos }}
                        </h3>

                        <p class="text-xs text-[#60756B] mt-2">
                            Usuários aprovados.
                        </p>
                    </div>

                    <div class="bg-white rounded-3xl p-5 shadow-sm border border-[#E3EBE4] border-l-4 border-l-yellow-500 hover:shadow-lg transition">
                        <p class="text-[11px] uppercase tracking-widest text-[#60756B] font-extrabold">
                            Pendentes
                        </p>

                        <h3 class="text-3xl font-extrabold mt-2 text-yellow-600">
                            {{ $usuariosPendentes }}
                        </h3>

                        <p class="text-xs text-[#60756B] mt-2">
                            Aguardando aprovação.
                        </p>
                    </div>

                    <div class="bg-white rounded-3xl p-5 shadow-sm border border-[#E3EBE4] border-l-4 border-l-red-600 hover:shadow-lg transition">
                        <p class="text-[11px] uppercase tracking-widest text-[#60756B] font-extrabold">
                            Inutilizados
                        </p>

                        <h3 class="text-3xl font-extrabold mt-2 text-red-600">
                            {{ $usuariosInutilizados }}
                        </h3>

                        <p class="text-xs text-[#60756B] mt-2">
                            Sem acesso ao sistema.
                        </p>
                    </div>

                    <div class="bg-white rounded-3xl p-5 shadow-sm border border-[#E3EBE4] border-l-4 border-l-[#7EDB90] hover:shadow-lg transition">
                        <p class="text-[11px] uppercase tracking-widest text-[#60756B] font-extrabold">
                            Residentes ativos
                        </p>

                        <h3 class="text-3xl font-extrabold mt-2 text-[#003C2F]">
                            {{ $residentesAtivos }}
                        </h3>

                        <p class="text-xs text-[#60756B] mt-2">
                            Residentes liberados no sistema.
                        </p>
                    </div>

                    <div class="bg-white rounded-3xl p-5 shadow-sm border border-[#E3EBE4] border-l-4 border-l-blue-500 hover:shadow-lg transition">
                        <p class="text-[11px] uppercase tracking-widest text-[#60756B] font-extrabold">
                            Preceptores ativos
                        </p>

                        <h3 class="text-3xl font-extrabold mt-2 text-blue-700">
                            {{ $preceptoresAtivos }}
                        </h3>

                        <p class="text-xs text-[#60756B] mt-2">
                            Preceptores liberados no sistema.
                        </p>
                    </div>

                </div>

            </div>

            <!-- SEM RESULTADOS -->
            <div id="semResultados"
                 class="hidden bg-white border border-[#E3EBE4] rounded-3xl p-8 text-center text-[#60756B] mb-6 shadow-sm">

                <div class="w-16 h-16 mx-auto rounded-full bg-[#EAF5EF] flex items-center justify-center mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg"
                         class="w-8 h-8 text-[#004D3A]"
                         fill="none"
                         viewBox="0 0 24 24"
                         stroke="currentColor">
                        <path stroke-linecap="round"
                              stroke-linejoin="round"
                              stroke-width="1.8"
                              d="M15.75 15.75 21 21m-5.25-5.25a7.5 7.5 0 1 0-10.607 0 7.5 7.5 0 0 0 10.607 0z"/>
                    </svg>
                </div>

                <p class="font-extrabold text-[#003C2F]">Nenhum usuário encontrado</p>
                <p class="text-sm mt-1">Tente pesquisar por outro nome, CPF, e-mail, tipo, status ou período.</p>
            </div>

            <!-- LISTA RESPONSIVA DE USUÁRIOS -->
            <div class="mb-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">

                <h2 class="text-xl font-extrabold text-[#003C2F]">
                    Usuários cadastrados
                </h2>

                <p class="text-sm text-[#60756B]">
                    Mostrando <strong id="contadorUsuariosVisiveis">{{ $usuarios->count() }}</strong> de
                    <strong>{{ $usuarios->count() }}</strong> usuários registrados
                </p>

            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 2xl:grid-cols-3 gap-5 mb-10" id="listaUsuarios">

                @foreach($usuarios as $user)

                    @php
                        $nomePartes = preg_split('/\s+/', trim($user->name));
                        $iniciais = strtoupper(substr($nomePartes[0] ?? 'U', 0, 1));

                        if (count($nomePartes) > 1) {
                            $iniciais .= strtoupper(substr(end($nomePartes), 0, 1));
                        }

                        $tipoUser = strtolower($user->tipo ?? '');
                        $statusUser = $user->status ?? 'pendente';

                        $statusAtivo = $statusUser === 'aprovado';
                        $statusPendente = $statusUser === 'pendente';
                        $statusInutilizado = $statusUser === 'inutilizado';

                        $statusFiltro = match ($statusUser) {
                            'aprovado' => 'ativo',
                            'inutilizado' => 'inutilizado',
                            default => 'pendente',
                        };

                        $statusTexto = match ($statusUser) {
                            'aprovado' => 'ATIVO',
                            'inutilizado' => 'INUTILIZADO',
                            default => 'PENDENTE',
                        };

                        /*
                        |--------------------------------------------------------------------------
                        | DATAS NO HORÁRIO DO BRASIL
                        |--------------------------------------------------------------------------
                        | O banco/Laravel Cloud normalmente salva datas em UTC.
                        | Aqui convertemos para America/Sao_Paulo antes de mostrar na tela.
                        */
                        $dataCadastro = $user->created_at
                            ? \Carbon\Carbon::parse($user->created_at)->timezone($timezoneBrasil)
                            : null;

                        $dataAceito = $statusAtivo && $user->updated_at
                            ? \Carbon\Carbon::parse($user->updated_at)->timezone($timezoneBrasil)
                            : null;
                    @endphp

                    <div
                        class="usuario-item usuario-card bg-white border-2 border-[#E3EBE4] rounded-3xl p-5 shadow-sm hover:shadow-lg transition cursor-pointer"
                        tabindex="0"
                        data-id="{{ $user->id }}"
                        data-nome="{{ $user->name }}"
                        data-email="{{ $user->email }}"
                        data-cpf="{{ $user->cpf }}"
                        data-tipo="{{ $tipoUser }}"
                        data-status="{{ $statusFiltro }}"
                        data-status-real="{{ $statusUser }}"
                        data-status-texto="{{ $statusTexto }}"
                        data-cadastro="{{ $dataCadastro ? $dataCadastro->format('Y-m-d') : '' }}"
                        data-aceito="{{ $dataAceito ? $dataAceito->format('Y-m-d') : '' }}"
                        data-update-url="{{ route('usuarios.update', $user->id) }}"
                        data-delete-url="{{ route('usuarios.destroy', $user->id) }}"
                        data-inutilizar-url="{{ route('usuarios.inutilizar', $user->id) }}"
                        data-reativar-url="{{ route('usuarios.reativar', $user->id) }}"
                        data-aprovar-url="{{ route('usuario.aprovar', $user->id) }}"
                        data-rejeitar-url="{{ route('usuario.rejeitar', $user->id) }}"
                        data-search="{{ strtolower($user->name . ' ' . $user->email . ' ' . $user->cpf . ' ' . $user->tipo . ' ' . $user->status . ' ' . $statusTexto . ' ' . ($dataCadastro ? $dataCadastro->format('d/m/Y') : '') . ' ' . ($dataAceito ? $dataAceito->format('d/m/Y') : '')) }}"
                    >

                        <div class="flex items-start gap-4">

                            <div class="w-14 h-14 rounded-2xl
                                @if($statusAtivo) bg-[#00A63E] text-white
                                @elseif($statusInutilizado) bg-red-100 text-red-700
                                @else bg-yellow-100 text-yellow-700
                                @endif
                                flex items-center justify-center font-extrabold shrink-0">
                                {{ $iniciais }}
                            </div>

                            <div class="min-w-0 flex-1">

                                <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-2">

                                    <div class="min-w-0">
                                        <p class="font-extrabold text-[#003C2F] break-words leading-tight text-lg">
                                            {{ $user->name }}
                                        </p>

                                        <p class="text-sm text-[#60756B] break-words mt-1">
                                            {{ $user->email }}
                                        </p>
                                    </div>

                                    <div class="shrink-0">
                                        @if($statusAtivo)
                                            <span class="inline-flex items-center gap-2 text-green-700 bg-green-50 border border-green-100 px-3 py-1 rounded-full font-extrabold text-[11px] whitespace-nowrap">
                                                <span class="w-2 h-2 rounded-full bg-green-600"></span>
                                                ATIVO
                                            </span>
                                        @elseif($statusInutilizado)
                                            <span class="inline-flex items-center gap-2 text-red-700 bg-red-50 border border-red-100 px-3 py-1 rounded-full font-extrabold text-[11px] whitespace-nowrap">
                                                <span class="w-2 h-2 rounded-full bg-red-600"></span>
                                                INUTILIZADO
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-2 text-yellow-700 bg-yellow-50 border border-yellow-100 px-3 py-1 rounded-full font-extrabold text-[11px] whitespace-nowrap">
                                                <span class="w-2 h-2 rounded-full bg-yellow-500"></span>
                                                PENDENTE
                                            </span>
                                        @endif
                                    </div>

                                </div>

                            </div>

                        </div>

                        <div class="mt-5 grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">

                            <div class="bg-[#F8FBF8] border border-[#E3EBE4] rounded-2xl px-4 py-3">
                                <span class="text-[#60756B] font-semibold block">CPF</span>
                                <span class="text-[#003C2F] font-bold break-words">
                                    {{ $user->cpf ?: '-' }}
                                </span>
                            </div>

                            <div class="bg-[#F8FBF8] border border-[#E3EBE4] rounded-2xl px-4 py-3">
                                <span class="text-[#60756B] font-semibold block">Tipo</span>
                                <span class="inline-flex mt-1 items-center bg-green-100 text-green-700 px-3 py-1 rounded-full text-[11px] font-extrabold">
                                    {{ strtoupper($user->tipo ?? 'USUÁRIO') }}
                                </span>
                            </div>

                            <div class="bg-[#F8FBF8] border border-[#E3EBE4] rounded-2xl px-4 py-3">
                                <span class="text-[#60756B] font-semibold block">Cadastro</span>
                                <span class="text-[#003C2F] font-bold">
                                    {{ $dataCadastro ? $dataCadastro->format('d/m/Y H:i') : '-' }}
                                </span>
                            </div>

                            <div class="bg-[#F8FBF8] border border-[#E3EBE4] rounded-2xl px-4 py-3">
                                <span class="text-[#60756B] font-semibold block">Situação</span>

                                @if($dataAceito)
                                    <span class="text-green-700 font-bold">
                                        Aceito em {{ $dataAceito->format('d/m/Y H:i') }}
                                    </span>
                                @elseif($statusInutilizado)
                                    <span class="text-red-600 font-bold">
                                        Acesso bloqueado
                                    </span>
                                @else
                                    <span class="text-yellow-700 font-bold">
                                        Ainda não aceito
                                    </span>
                                @endif
                            </div>

                        </div>

                        <div class="mt-5 flex flex-col sm:flex-row flex-wrap gap-3">

                            <button
                                type="button"
                                onclick='event.stopPropagation(); abrirModalEditar(
                                    @json($user->id),
                                    @json($user->name),
                                    @json($user->email),
                                    @json($user->cpf)
                                )'
                                class="flex-1 min-w-[130px] bg-[#004D3A] hover:bg-[#003C2F] text-white px-4 py-3 rounded-2xl transition text-sm font-extrabold flex items-center justify-center gap-2 shadow-sm">
                                Editar
                            </button>

                            @if($statusPendente)
                                <form action="{{ route('usuario.aprovar', $user->id) }}" method="POST" class="flex-1 min-w-[130px]" onclick="event.stopPropagation();">
                                    @csrf

                                    <button type="button"
                                            onclick="event.stopPropagation(); confirmarFormularioUsuario(
                                                this,
                                                'aprovar',
                                                'Aprovar acesso?',
                                                'Este usuário passará a ter acesso ao sistema.',
                                                'Aprovar usuário'
                                            )"
                                            class="w-full bg-green-50 hover:bg-green-100 text-green-700 border border-green-200 px-4 py-3 rounded-2xl transition text-sm font-extrabold flex items-center justify-center gap-2">
                                        Aprovar
                                    </button>
                                </form>

                                <form action="{{ route('usuario.rejeitar', $user->id) }}" method="POST" class="flex-1 min-w-[130px]" onclick="event.stopPropagation();">
                                    @csrf

                                    <button type="button"
                                            onclick="event.stopPropagation(); confirmarFormularioUsuario(
                                                this,
                                                'rejeitar',
                                                'Rejeitar solicitação?',
                                                'O acesso será negado e o usuário não conseguirá entrar no sistema.',
                                                'Rejeitar usuário'
                                            )"
                                            class="w-full bg-red-50 hover:bg-red-100 text-red-600 border border-red-200 px-4 py-3 rounded-2xl transition text-sm font-extrabold flex items-center justify-center gap-2">
                                        Rejeitar
                                    </button>
                                </form>
                            @elseif($statusInutilizado)
                                <form action="{{ route('usuarios.reativar', $user->id) }}" method="POST" class="flex-1 min-w-[130px]" onclick="event.stopPropagation();">
                                    @csrf
                                    @method('PATCH')

                                    <button type="button"
                                            onclick="event.stopPropagation(); confirmarFormularioUsuario(
                                                this,
                                                'reativar',
                                                'Reativar usuário?',
                                                'O usuário voltará a ter acesso normalmente ao sistema.',
                                                'Reativar usuário'
                                            )"
                                            class="w-full bg-green-50 hover:bg-green-100 text-green-700 border border-green-200 px-4 py-3 rounded-2xl transition text-sm font-extrabold flex items-center justify-center gap-2">
                                        Reativar
                                    </button>
                                </form>
                            @else
                                <form action="{{ route('usuarios.inutilizar', $user->id) }}" method="POST" class="flex-1 min-w-[130px]" onclick="event.stopPropagation();">
                                    @csrf
                                    @method('PATCH')

                                    <button type="button"
                                            onclick="event.stopPropagation(); confirmarFormularioUsuario(
                                                this,
                                                'inutilizar',
                                                'Inutilizar usuário?',
                                                'O acesso será bloqueado até que o usuário seja reativado.',
                                                'Inutilizar usuário'
                                            )"
                                            class="w-full bg-yellow-50 hover:bg-yellow-100 text-yellow-700 border border-yellow-200 px-4 py-3 rounded-2xl transition text-sm font-extrabold flex items-center justify-center gap-2">
                                        Inutilizar
                                    </button>
                                </form>

                                <button
                                    type="button"
                                    onclick='event.stopPropagation(); abrirModalExcluir(
                                        @json($user->id),
                                        @json($user->name),
                                        @json($user->email)
                                    )'
                                    class="flex-1 min-w-[130px] bg-red-50 hover:bg-red-100 text-red-600 border border-red-200 px-4 py-3 rounded-2xl transition text-sm font-extrabold flex items-center justify-center gap-2">
                                    Excluir
                                </button>
                            @endif

                        </div>

                    </div>

                @endforeach

            </div>

        </section>

    </main>

</div>

<!-- MENU DE CONTEXTO -->
<div id="menuContextoUsuario" class="menu-contexto-usuario">
    <div class="w-72 bg-white border border-[#E3EBE4] rounded-3xl shadow-2xl overflow-hidden">

        <div class="px-5 py-4 bg-[#F8FBF8] border-b border-[#E3EBE4]">
            <p class="text-xs uppercase tracking-widest text-[#60756B] font-extrabold">
                Ações do usuário
            </p>

            <p id="menuNomeUsuario" class="text-[#003C2F] font-extrabold mt-1 break-words">
                Usuário
            </p>

            <p id="menuEmailUsuario" class="text-xs text-[#60756B] break-words mt-1">
                email
            </p>
        </div>

        <div class="p-3 space-y-2">

            <button type="button"
                    id="botaoContextoAprovar"
                    onclick="aprovarUsuarioSelecionado()"
                    class="hidden w-full text-left px-4 py-3 rounded-2xl hover:bg-green-50 text-green-700 font-extrabold transition items-center justify-between">
                <span>Aprovar acesso</span>
                <span>✅</span>
            </button>

            <button type="button"
                    id="botaoContextoRejeitar"
                    onclick="rejeitarUsuarioSelecionado()"
                    class="hidden w-full text-left px-4 py-3 rounded-2xl hover:bg-red-50 text-red-600 font-extrabold transition items-center justify-between">
                <span>Rejeitar acesso</span>
                <span>❌</span>
            </button>

            <button type="button"
                    onclick="editarUsuarioSelecionado()"
                    class="w-full text-left px-4 py-3 rounded-2xl hover:bg-[#EAF5EF] text-[#004D3A] font-extrabold transition flex items-center justify-between">
                <span>Editar</span>
                <span>✏️</span>
            </button>

            <button type="button"
                    id="botaoContextoInutilizar"
                    onclick="alternarStatusUsuarioSelecionado()"
                    class="w-full text-left px-4 py-3 rounded-2xl hover:bg-yellow-50 text-yellow-700 font-extrabold transition flex items-center justify-between">
                <span id="textoContextoInutilizar">Inutilizar</span>
                <span id="iconeContextoInutilizar">🚫</span>
            </button>

            <button type="button"
                    onclick="excluirUsuarioSelecionado()"
                    class="w-full text-left px-4 py-3 rounded-2xl hover:bg-red-50 text-red-600 font-extrabold transition flex items-center justify-between">
                <span>Excluir</span>
                <span>🗑️</span>
            </button>

        </div>

    </div>
</div>

<!-- FORM DINÂMICO PARA INUTILIZAR / REATIVAR -->
<form id="formAcaoRapidaUsuario" method="POST" class="hidden">
    @csrf
    <input type="hidden" name="_method" id="metodoAcaoRapidaUsuario" value="PATCH">
</form>

<!-- FORM DINÂMICO PARA APROVAR / REJEITAR -->
<form id="formAprovacaoRapidaUsuario" method="POST" class="hidden">
    @csrf
</form>


<!-- MODAL DE CONFIRMAÇÃO PERSONALIZADO -->
<div id="modalConfirmacaoUsuario"
     class="hidden items-center justify-center bg-[#001E17]/60 backdrop-blur-sm px-4"
     style="position: fixed; inset: 0; width: 100vw; height: 100vh; z-index: 999999;">

    <div class="modal-confirmacao-entrada bg-white w-full max-w-md rounded-[30px] border border-[#DCE7DE] shadow-2xl overflow-hidden">

        <div id="cabecalhoModalConfirmacao"
             class="px-6 pt-7 pb-5 bg-gradient-to-br from-[#F4FBF6] to-white">

            <div class="flex items-start justify-between gap-4">

                <div id="iconeModalConfirmacao"
                     class="modal-confirmacao-icone w-16 h-16 rounded-2xl bg-green-100 text-green-700 flex items-center justify-center shrink-0">

                    <svg id="svgAprovarConfirmacao"
                         xmlns="http://www.w3.org/2000/svg"
                         class="w-8 h-8"
                         fill="none"
                         viewBox="0 0 24 24"
                         stroke="currentColor">
                        <path stroke-linecap="round"
                              stroke-linejoin="round"
                              stroke-width="1.9"
                              d="m4.5 12.75 6 6 9-13.5"/>
                    </svg>

                    <svg id="svgAlertaConfirmacao"
                         xmlns="http://www.w3.org/2000/svg"
                         class="hidden w-8 h-8"
                         fill="none"
                         viewBox="0 0 24 24"
                         stroke="currentColor">
                        <path stroke-linecap="round"
                              stroke-linejoin="round"
                              stroke-width="1.9"
                              d="M12 9v3.75m9-1.5a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 5.25h.008v.008H12V16.5Z"/>
                    </svg>
                </div>

                <button type="button"
                        onclick="fecharModalConfirmacaoUsuario()"
                        class="w-10 h-10 rounded-xl bg-white border border-[#E3EBE4] text-[#60756B] hover:bg-[#F3F7F3] hover:text-[#003C2F] transition flex items-center justify-center">

                    <svg xmlns="http://www.w3.org/2000/svg"
                         class="w-5 h-5"
                         fill="none"
                         viewBox="0 0 24 24"
                         stroke="currentColor">
                        <path stroke-linecap="round"
                              stroke-linejoin="round"
                              stroke-width="1.8"
                              d="M6 18 18 6M6 6l12 12"/>
                    </svg>
                </button>

            </div>

            <div class="mt-5">
                <p id="etiquetaModalConfirmacao"
                   class="text-[11px] uppercase tracking-[0.18em] text-green-700 font-extrabold">
                    Confirmação de acesso
                </p>

                <h2 id="tituloModalConfirmacao"
                    class="text-2xl font-extrabold text-[#003C2F] mt-2">
                    Aprovar acesso?
                </h2>

                <p id="mensagemModalConfirmacao"
                   class="text-sm text-[#60756B] mt-2 leading-relaxed">
                    Este usuário passará a ter acesso ao sistema.
                </p>
            </div>

        </div>

        <div class="px-6 pb-7">

            <div class="bg-[#F8FBF8] border border-[#E3EBE4] rounded-2xl p-4 mb-6">
                <div class="flex items-center gap-3">

                    <div id="avatarModalConfirmacao"
                         class="w-12 h-12 rounded-2xl bg-[#004D3A] text-white flex items-center justify-center font-extrabold shrink-0">
                        U
                    </div>

                    <div class="min-w-0">
                        <p id="nomeModalConfirmacao"
                           class="font-extrabold text-[#003C2F] break-words">
                            Usuário
                        </p>

                        <p id="emailModalConfirmacao"
                           class="text-xs text-[#60756B] mt-1 break-all">
                            usuario@email.com
                        </p>
                    </div>

                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">

                <button type="button"
                        onclick="fecharModalConfirmacaoUsuario()"
                        class="px-5 py-3.5 rounded-2xl bg-[#F1F6F2] text-[#60756B] font-extrabold hover:bg-[#E6EFE8] transition">
                    Cancelar
                </button>

                <button type="button"
                        id="botaoConfirmarAcaoUsuario"
                        onclick="executarAcaoConfirmadaUsuario()"
                        class="px-5 py-3.5 rounded-2xl bg-[#00A63E] text-white font-extrabold hover:bg-[#008F35] transition shadow-lg shadow-green-900/10">
                    Aprovar usuário
                </button>

            </div>

        </div>

    </div>

</div>

<!-- MODAL EDITAR -->
<div id="modalEditar" class="fixed inset-0 hidden items-center justify-center bg-black/50 backdrop-blur-sm z-[80] px-4">

    <div class="bg-white w-full max-w-lg p-6 rounded-3xl border border-[#E3EBE4] shadow-2xl">

        <div class="flex items-start justify-between mb-6">

            <div>
                <h2 class="text-2xl font-extrabold text-[#003C2F]">
                    Editar Usuário
                </h2>

                <p class="text-sm text-[#60756B] mt-1">
                    Atualize os dados principais do usuário.
                </p>
            </div>

            <button type="button"
                    onclick="fecharModal()"
                    class="bg-[#F1F6F2] hover:bg-[#E6EFE8] text-[#003C2F] p-2 rounded-xl transition">

                <svg xmlns="http://www.w3.org/2000/svg"
                     class="w-5 h-5"
                     fill="none"
                     viewBox="0 0 24 24"
                     stroke="currentColor">
                    <path stroke-linecap="round"
                          stroke-linejoin="round"
                          stroke-width="1.8"
                          d="M6 18 18 6M6 6l12 12"/>
                </svg>
            </button>

        </div>

        <form id="formEditar" method="POST">
            @csrf
            @method('PUT')

            <div class="space-y-4">

                <div>
                    <label class="block text-xs uppercase tracking-widest text-[#60756B] font-extrabold mb-2">
                        Nome
                    </label>

                    <input id="nomeEdit"
                           name="name"
                           type="text"
                           class="w-full px-4 py-3 rounded-2xl bg-[#F8FBF8] border border-[#DCE7DE] text-[#003C2F] focus:outline-none focus:ring-2 focus:ring-[#00A63E] focus:border-transparent transition">
                </div>

                <div>
                    <label class="block text-xs uppercase tracking-widest text-[#60756B] font-extrabold mb-2">
                        E-mail
                    </label>

                    <input id="emailEdit"
                           name="email"
                           type="email"
                           class="w-full px-4 py-3 rounded-2xl bg-[#F8FBF8] border border-[#DCE7DE] text-[#003C2F] focus:outline-none focus:ring-2 focus:ring-[#00A63E] focus:border-transparent transition">
                </div>

                <div>
                    <label class="block text-xs uppercase tracking-widest text-[#60756B] font-extrabold mb-2">
                        CPF
                    </label>

                    <input id="cpfEdit"
                           name="cpf"
                           type="text"
                           class="w-full px-4 py-3 rounded-2xl bg-[#F8FBF8] border border-[#DCE7DE] text-[#003C2F] focus:outline-none focus:ring-2 focus:ring-[#00A63E] focus:border-transparent transition">
                </div>

            </div>

            <div class="flex flex-col sm:flex-row justify-end gap-3 mt-7">
                <button type="button"
                        onclick="fecharModal()"
                        class="px-5 py-3 rounded-2xl bg-[#F1F6F2] text-[#60756B] font-bold hover:bg-[#E6EFE8] transition">
                    Cancelar
                </button>

                <button class="px-5 py-3 rounded-2xl bg-[#004D3A] text-white font-bold hover:bg-[#003C2F] transition shadow-sm">
                    Salvar Alterações
                </button>
            </div>

        </form>

    </div>

</div>

<!-- MODAL EXCLUIR -->
<div id="modalExcluir" class="fixed inset-0 hidden items-center justify-center bg-black/50 backdrop-blur-sm z-[90] px-4">

    <div class="bg-white w-full max-w-md p-6 rounded-3xl border border-red-100 shadow-2xl">

        <div class="w-16 h-16 mx-auto rounded-full bg-red-100 flex items-center justify-center mb-5">
            <svg xmlns="http://www.w3.org/2000/svg"
                 class="w-8 h-8 text-red-600"
                 fill="none"
                 viewBox="0 0 24 24"
                 stroke="currentColor">
                <path stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="1.8"
                      d="M12 9v3.75m0 3.75h.008v.008H12V16.5zm9-4.5a9 9 0 1 1-18 0 9 9 0 0 1 18 0z"/>
            </svg>
        </div>

        <h2 class="text-2xl font-extrabold text-[#003C2F] text-center">
            Excluir usuário?
        </h2>

        <p class="text-sm text-[#60756B] mt-3 text-center leading-relaxed">
            Tem certeza que deseja excluir este usuário? Essa ação removerá o usuário do banco e não poderá ser desfeita.
        </p>

        <div class="mt-5 bg-red-50 border border-red-100 rounded-2xl p-4">
            <p class="text-sm font-extrabold text-red-700" id="nomeExcluirUsuario">
                Usuário
            </p>

            <p class="text-xs text-red-600 mt-1 break-words" id="emailExcluirUsuario">
                email
            </p>
        </div>

        <form id="formExcluir" method="POST" class="mt-6">
            @csrf
            @method('DELETE')

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <button type="button"
                        onclick="fecharModalExcluir()"
                        class="px-5 py-3 rounded-2xl bg-[#F1F6F2] text-[#60756B] font-bold hover:bg-[#E6EFE8] transition">
                    Cancelar
                </button>

                <button type="submit"
                        class="px-5 py-3 rounded-2xl bg-red-600 text-white font-bold hover:bg-red-700 transition shadow-sm">
                    Excluir usuário
                </button>
            </div>
        </form>

    </div>

</div>

<!-- MODAL FILTROS -->
<div id="modalFiltros" class="fixed inset-0 hidden items-center justify-center bg-black/50 backdrop-blur-sm z-[75] px-4">

    <div class="bg-white w-full max-w-md p-6 rounded-3xl border border-[#E3EBE4] shadow-2xl">

        <div class="flex items-start justify-between mb-6">
            <div>
                <h2 class="text-2xl font-extrabold text-[#003C2F]">
                    Filtros Avançados
                </h2>

                <p class="text-sm text-[#60756B] mt-1">
                    Refine a visualização dos usuários por tipo, status e período de cadastro.
                </p>
            </div>

            <button type="button"
                    onclick="fecharModalFiltros()"
                    class="bg-[#F1F6F2] hover:bg-[#E6EFE8] text-[#003C2F] p-2 rounded-xl transition">

                <svg xmlns="http://www.w3.org/2000/svg"
                     class="w-5 h-5"
                     fill="none"
                     viewBox="0 0 24 24"
                     stroke="currentColor">
                    <path stroke-linecap="round"
                          stroke-linejoin="round"
                          stroke-width="1.8"
                          d="M6 18 18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <div class="space-y-4">

            <div>
                <label class="block text-xs uppercase tracking-widest text-[#60756B] font-extrabold mb-2">
                    Tipo
                </label>

                <select id="filtroTipo"
                        onchange="filtrarUsuarios()"
                        class="w-full px-4 py-3 rounded-2xl bg-[#F8FBF8] border border-[#DCE7DE] text-[#003C2F] focus:outline-none focus:ring-2 focus:ring-[#00A63E]">
                    <option value="">Todos</option>
                    <option value="admin">Administrador</option>
                    <option value="preceptor">Preceptor</option>
                    <option value="residente">Residente</option>
                    <option value="aluno">Aluno</option>
                </select>
            </div>

            <div>
                <label class="block text-xs uppercase tracking-widest text-[#60756B] font-extrabold mb-2">
                    Status
                </label>

                <select id="filtroStatus"
                        onchange="filtrarUsuarios()"
                        class="w-full px-4 py-3 rounded-2xl bg-[#F8FBF8] border border-[#DCE7DE] text-[#003C2F] focus:outline-none focus:ring-2 focus:ring-[#00A63E]">
                    <option value="">Todos</option>
                    <option value="ativo">Ativos</option>
                    <option value="pendente">Pendentes</option>
                    <option value="inutilizado">Inutilizados</option>
                </select>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs uppercase tracking-widest text-[#60756B] font-extrabold mb-2">
                        Cadastro de
                    </label>

                    <input type="date"
                           id="dataInicioCadastro"
                           onchange="filtrarUsuarios()"
                           class="w-full px-4 py-3 rounded-2xl bg-[#F8FBF8] border border-[#DCE7DE] text-[#003C2F] focus:outline-none focus:ring-2 focus:ring-[#00A63E]">
                </div>

                <div>
                    <label class="block text-xs uppercase tracking-widest text-[#60756B] font-extrabold mb-2">
                        Cadastro até
                    </label>

                    <input type="date"
                           id="dataFimCadastro"
                           onchange="filtrarUsuarios()"
                           class="w-full px-4 py-3 rounded-2xl bg-[#F8FBF8] border border-[#DCE7DE] text-[#003C2F] focus:outline-none focus:ring-2 focus:ring-[#00A63E]">
                </div>
            </div>

        </div>

        <div class="flex flex-col sm:flex-row justify-end gap-3 mt-7">
            <button type="button"
                    onclick="limparFiltros()"
                    class="px-5 py-3 rounded-2xl bg-[#F1F6F2] text-[#60756B] font-bold hover:bg-[#E6EFE8] transition">
                Limpar filtros
            </button>

            <button type="button"
                    onclick="fecharModalFiltros()"
                    class="px-5 py-3 rounded-2xl bg-[#004D3A] text-white font-bold hover:bg-[#003C2F] transition shadow-sm">
                Aplicar
            </button>
        </div>

    </div>

</div>

<script>
    let usuarioSelecionado = null;
    let longPressTimer = null;

    let acaoConfirmadaUsuario = null;

    function obterIniciaisUsuario(nome) {
        const partes = String(nome || 'Usuário')
            .trim()
            .split(/\s+/)
            .filter(Boolean);

        if (partes.length === 0) return 'U';

        const primeira = partes[0].charAt(0);
        const ultima = partes.length > 1
            ? partes[partes.length - 1].charAt(0)
            : '';

        return (primeira + ultima).toUpperCase();
    }

    function configurarVisualModalConfirmacao(tipo) {
        const icone = document.getElementById('iconeModalConfirmacao');
        const etiqueta = document.getElementById('etiquetaModalConfirmacao');
        const botao = document.getElementById('botaoConfirmarAcaoUsuario');
        const svgAprovar = document.getElementById('svgAprovarConfirmacao');
        const svgAlerta = document.getElementById('svgAlertaConfirmacao');

        const configuracoes = {
            aprovar: {
                etiqueta: 'Confirmação de acesso',
                iconeClasses: 'bg-green-100 text-green-700',
                botaoClasses: 'bg-[#00A63E] hover:bg-[#008F35]',
                alerta: false
            },
            reativar: {
                etiqueta: 'Restauração de acesso',
                iconeClasses: 'bg-green-100 text-green-700',
                botaoClasses: 'bg-[#00A63E] hover:bg-[#008F35]',
                alerta: false
            },
            rejeitar: {
                etiqueta: 'Negar solicitação',
                iconeClasses: 'bg-red-100 text-red-600',
                botaoClasses: 'bg-red-600 hover:bg-red-700',
                alerta: true
            },
            inutilizar: {
                etiqueta: 'Bloqueio de acesso',
                iconeClasses: 'bg-yellow-100 text-yellow-700',
                botaoClasses: 'bg-yellow-600 hover:bg-yellow-700',
                alerta: true
            }
        };

        const config = configuracoes[tipo] || configuracoes.aprovar;

        etiqueta.innerText = config.etiqueta;

        icone.className =
            'modal-confirmacao-icone w-16 h-16 rounded-2xl flex items-center justify-center shrink-0 '
            + config.iconeClasses;

        botao.className =
            'px-5 py-3.5 rounded-2xl text-white font-extrabold transition shadow-lg shadow-black/10 '
            + config.botaoClasses;

        if (config.alerta) {
            svgAprovar.classList.add('hidden');
            svgAlerta.classList.remove('hidden');
        } else {
            svgAprovar.classList.remove('hidden');
            svgAlerta.classList.add('hidden');
        }
    }


    function garantirModalConfirmacaoNoBody() {
        const modal = document.getElementById('modalConfirmacaoUsuario');

        if (modal && modal.parentElement !== document.body) {
            document.body.appendChild(modal);
        }
    }

    function abrirModalConfirmacaoUsuario({
        tipo = 'aprovar',
        titulo,
        mensagem,
        textoBotao,
        nome,
        email,
        executar
    }) {
        garantirModalConfirmacaoNoBody();

        const modal = document.getElementById('modalConfirmacaoUsuario');

        if (!modal) return;

        acaoConfirmadaUsuario = typeof executar === 'function'
            ? executar
            : null;

        document.getElementById('tituloModalConfirmacao').innerText =
            titulo || 'Confirmar ação?';

        document.getElementById('mensagemModalConfirmacao').innerText =
            mensagem || 'Confirme para continuar.';

        document.getElementById('botaoConfirmarAcaoUsuario').innerText =
            textoBotao || 'Confirmar';

        document.getElementById('nomeModalConfirmacao').innerText =
            nome || 'Usuário';

        document.getElementById('emailModalConfirmacao').innerText =
            email || '';

        document.getElementById('avatarModalConfirmacao').innerText =
            obterIniciaisUsuario(nome);

        configurarVisualModalConfirmacao(tipo);

        modal.classList.remove('hidden');
        modal.classList.add('flex');

        document.body.style.overflow = 'hidden';

        setTimeout(() => {
            document.getElementById('botaoConfirmarAcaoUsuario')?.focus();
        }, 80);
    }

    function fecharModalConfirmacaoUsuario() {
        const modal = document.getElementById('modalConfirmacaoUsuario');

        if (!modal) return;

        modal.classList.add('hidden');
        modal.classList.remove('flex');

        document.body.style.overflow = '';

        acaoConfirmadaUsuario = null;
    }

    function executarAcaoConfirmadaUsuario() {
        const executar = acaoConfirmadaUsuario;

        if (typeof executar !== 'function') {
            fecharModalConfirmacaoUsuario();
            return;
        }

        const botao = document.getElementById('botaoConfirmarAcaoUsuario');

        if (botao) {
            botao.disabled = true;
            botao.innerText = 'Processando...';
            botao.classList.add('opacity-70', 'cursor-not-allowed');
        }

        executar();
    }

    function confirmarFormularioUsuario(botao, tipo, titulo, mensagem, textoBotao) {
        const form = botao?.closest('form');
        const card = botao?.closest('.usuario-item');

        if (!form) return;

        abrirModalConfirmacaoUsuario({
            tipo,
            titulo,
            mensagem,
            textoBotao,
            nome: card?.dataset.nome || 'Usuário',
            email: card?.dataset.email || '',
            executar: () => form.submit()
        });
    }


    function selecionarUsuario(card) {
        document.querySelectorAll('.usuario-item').forEach((item) => {
            item.classList.remove('usuario-selecionado');
        });

        card.classList.add('usuario-selecionado');
        usuarioSelecionado = card;
    }

    function abrirMenuContexto(card, x, y) {
        selecionarUsuario(card);

        const menu = document.getElementById('menuContextoUsuario');

        document.getElementById('menuNomeUsuario').innerText = card.dataset.nome || 'Usuário';
        document.getElementById('menuEmailUsuario').innerText = card.dataset.email || '';

        const statusReal = card.dataset.statusReal || 'pendente';
        const textoBotao = document.getElementById('textoContextoInutilizar');
        const iconeBotao = document.getElementById('iconeContextoInutilizar');
        const botaoAcao = document.getElementById('botaoContextoInutilizar');
        const botaoAprovar = document.getElementById('botaoContextoAprovar');
        const botaoRejeitar = document.getElementById('botaoContextoRejeitar');

        if (botaoAprovar && botaoRejeitar) {
            if (statusReal === 'pendente') {
                botaoAprovar.classList.remove('hidden');
                botaoAprovar.classList.add('flex');
                botaoRejeitar.classList.remove('hidden');
                botaoRejeitar.classList.add('flex');
            } else {
                botaoAprovar.classList.add('hidden');
                botaoAprovar.classList.remove('flex');
                botaoRejeitar.classList.add('hidden');
                botaoRejeitar.classList.remove('flex');
            }
        }

        if (statusReal === 'inutilizado') {
            textoBotao.innerText = 'Reativar';
            iconeBotao.innerText = '✅';
            botaoAcao.className = 'w-full text-left px-4 py-3 rounded-2xl hover:bg-green-50 text-green-700 font-extrabold transition flex items-center justify-between';
        } else if (statusReal === 'pendente') {
            textoBotao.innerText = 'Inutilizar';
            iconeBotao.innerText = '🚫';
            botaoAcao.className = 'hidden w-full text-left px-4 py-3 rounded-2xl hover:bg-yellow-50 text-yellow-700 font-extrabold transition items-center justify-between';
        } else {
            textoBotao.innerText = 'Inutilizar';
            iconeBotao.innerText = '🚫';
            botaoAcao.className = 'w-full text-left px-4 py-3 rounded-2xl hover:bg-yellow-50 text-yellow-700 font-extrabold transition flex items-center justify-between';
        }

        menu.style.display = 'block';

        const larguraMenu = menu.offsetWidth || 288;
        const alturaMenu = menu.offsetHeight || 240;

        let posX = x;
        let posY = y;

        if (posX + larguraMenu > window.innerWidth - 12) {
            posX = window.innerWidth - larguraMenu - 12;
        }

        if (posY + alturaMenu > window.innerHeight - 12) {
            posY = window.innerHeight - alturaMenu - 12;
        }

        menu.style.left = Math.max(12, posX) + 'px';
        menu.style.top = Math.max(12, posY) + 'px';
    }

    function fecharMenuContexto() {
        const menu = document.getElementById('menuContextoUsuario');

        if (menu) {
            menu.style.display = 'none';
        }
    }

    function aprovarUsuarioSelecionado() {
        if (!usuarioSelecionado) return;

        const card = usuarioSelecionado;
        const form = document.getElementById('formAprovacaoRapidaUsuario');

        if (!form) return;

        fecharMenuContexto();

        abrirModalConfirmacaoUsuario({
            tipo: 'aprovar',
            titulo: 'Aprovar acesso?',
            mensagem: 'Este usuário passará a ter acesso ao sistema.',
            textoBotao: 'Aprovar usuário',
            nome: card.dataset.nome,
            email: card.dataset.email,
            executar: () => {
                form.action = card.dataset.aprovarUrl;
                form.submit();
            }
        });
    }

    function rejeitarUsuarioSelecionado() {
        if (!usuarioSelecionado) return;

        const card = usuarioSelecionado;
        const form = document.getElementById('formAprovacaoRapidaUsuario');

        if (!form) return;

        fecharMenuContexto();

        abrirModalConfirmacaoUsuario({
            tipo: 'rejeitar',
            titulo: 'Rejeitar solicitação?',
            mensagem: 'O acesso será negado e o usuário não conseguirá entrar no sistema.',
            textoBotao: 'Rejeitar usuário',
            nome: card.dataset.nome,
            email: card.dataset.email,
            executar: () => {
                form.action = card.dataset.rejeitarUrl;
                form.submit();
            }
        });
    }

    function editarUsuarioSelecionado() {
        if (!usuarioSelecionado) return;

        abrirModalEditar(
            usuarioSelecionado.dataset.id,
            usuarioSelecionado.dataset.nome,
            usuarioSelecionado.dataset.email,
            usuarioSelecionado.dataset.cpf
        );

        fecharMenuContexto();
    }

    function alternarStatusUsuarioSelecionado() {
        if (!usuarioSelecionado) return;

        const card = usuarioSelecionado;
        const statusReal = card.dataset.statusReal || 'pendente';
        const reativar = statusReal === 'inutilizado';

        const url = reativar
            ? card.dataset.reativarUrl
            : card.dataset.inutilizarUrl;

        const form = document.getElementById('formAcaoRapidaUsuario');
        const metodo = document.getElementById('metodoAcaoRapidaUsuario');

        if (!form || !metodo) return;

        fecharMenuContexto();

        abrirModalConfirmacaoUsuario({
            tipo: reativar ? 'reativar' : 'inutilizar',
            titulo: reativar ? 'Reativar usuário?' : 'Inutilizar usuário?',
            mensagem: reativar
                ? 'O usuário voltará a ter acesso normalmente ao sistema.'
                : 'O acesso será bloqueado até que o usuário seja reativado.',
            textoBotao: reativar ? 'Reativar usuário' : 'Inutilizar usuário',
            nome: card.dataset.nome,
            email: card.dataset.email,
            executar: () => {
                form.action = url;
                metodo.value = 'PATCH';
                form.submit();
            }
        });
    }

    function excluirUsuarioSelecionado() {
        if (!usuarioSelecionado) return;

        abrirModalExcluir(
            usuarioSelecionado.dataset.id,
            usuarioSelecionado.dataset.nome,
            usuarioSelecionado.dataset.email
        );

        fecharMenuContexto();
    }

    function abrirModalEditar(id, nome, email, cpf) {
        const modal = document.getElementById('modalEditar');

        modal.classList.remove('hidden');
        modal.classList.add('flex');

        document.getElementById('nomeEdit').value = nome ?? '';
        document.getElementById('emailEdit').value = email ?? '';
        document.getElementById('cpfEdit').value = cpf ?? '';

        document.getElementById('formEditar').action = "/usuarios/" + id;
    }

    function fecharModal() {
        const modal = document.getElementById('modalEditar');

        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    function abrirModalExcluir(id, nome, email) {
        const modal = document.getElementById('modalExcluir');

        modal.classList.remove('hidden');
        modal.classList.add('flex');

        document.getElementById('nomeExcluirUsuario').innerText = nome ?? 'Usuário';
        document.getElementById('emailExcluirUsuario').innerText = email ?? '';
        document.getElementById('formExcluir').action = "/usuarios/" + id;
    }

    function fecharModalExcluir() {
        const modal = document.getElementById('modalExcluir');

        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    function abrirModalFiltros() {
        const modal = document.getElementById('modalFiltros');

        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function fecharModalFiltros() {
        const modal = document.getElementById('modalFiltros');

        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    function filtrarUsuarios() {
        const pesquisaInput = document.getElementById('pesquisaUsuarios');
        const filtroTipoInput = document.getElementById('filtroTipo');
        const filtroStatusInput = document.getElementById('filtroStatus');
        const dataInicioInput = document.getElementById('dataInicioCadastro');
        const dataFimInput = document.getElementById('dataFimCadastro');

        const termo = pesquisaInput ? pesquisaInput.value.toLowerCase().trim() : '';
        const tipoFiltro = filtroTipoInput ? filtroTipoInput.value.toLowerCase().trim() : '';
        const statusFiltro = filtroStatusInput ? filtroStatusInput.value.toLowerCase().trim() : '';
        const dataInicio = dataInicioInput ? dataInicioInput.value : '';
        const dataFim = dataFimInput ? dataFimInput.value : '';

        const itens = document.querySelectorAll('.usuario-item');
        const semResultados = document.getElementById('semResultados');
        const contador = document.getElementById('contadorUsuariosVisiveis');

        let encontrados = 0;

        itens.forEach((item) => {
            const texto = item.dataset.search || '';
            const tipo = item.dataset.tipo || '';
            const status = item.dataset.status || '';
            const dataCadastro = item.dataset.cadastro || '';

            const batePesquisa = texto.includes(termo);
            const bateTipo = !tipoFiltro || tipo === tipoFiltro;
            const bateStatus = !statusFiltro || status === statusFiltro;

            let bateData = true;

            if (dataInicio && dataCadastro) {
                bateData = bateData && dataCadastro >= dataInicio;
            }

            if (dataFim && dataCadastro) {
                bateData = bateData && dataCadastro <= dataFim;
            }

            if ((dataInicio || dataFim) && !dataCadastro) {
                bateData = false;
            }

            if (batePesquisa && bateTipo && bateStatus && bateData) {
                item.classList.remove('hidden');
                encontrados++;
            } else {
                item.classList.add('hidden');
            }
        });

        if (semResultados) {
            if (encontrados === 0) {
                semResultados.classList.remove('hidden');
            } else {
                semResultados.classList.add('hidden');
            }
        }

        if (contador) {
            contador.innerText = encontrados;
        }

        fecharMenuContexto();
    }

    function limparPesquisa() {
        const input = document.getElementById('pesquisaUsuarios');

        if (input) {
            input.value = '';
        }

        filtrarUsuarios();

        if (input) {
            input.focus();
        }
    }

    function limparFiltros() {
        const filtroTipo = document.getElementById('filtroTipo');
        const filtroStatus = document.getElementById('filtroStatus');
        const dataInicio = document.getElementById('dataInicioCadastro');
        const dataFim = document.getElementById('dataFimCadastro');

        if (filtroTipo) filtroTipo.value = '';
        if (filtroStatus) filtroStatus.value = '';
        if (dataInicio) dataInicio.value = '';
        if (dataFim) dataFim.value = '';

        filtrarUsuarios();
    }

    document.querySelectorAll('.usuario-item').forEach((card) => {
        card.addEventListener('click', function(e) {
            if (e.target.closest('button') || e.target.closest('form') || e.target.closest('a')) {
                return;
            }

            selecionarUsuario(this);
            fecharMenuContexto();
        });

        card.addEventListener('contextmenu', function(e) {
            e.preventDefault();

            abrirMenuContexto(this, e.clientX, e.clientY);
        });

        card.addEventListener('touchstart', function(e) {
            if (e.target.closest('button') || e.target.closest('form') || e.target.closest('a')) {
                return;
            }

            const touch = e.touches[0];
            const cardAtual = this;

            longPressTimer = setTimeout(() => {
                abrirMenuContexto(cardAtual, touch.clientX, touch.clientY);
            }, 650);
        }, { passive: true });

        card.addEventListener('touchend', function() {
            clearTimeout(longPressTimer);
        });

        card.addEventListener('touchmove', function() {
            clearTimeout(longPressTimer);
        });

        card.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                selecionarUsuario(this);
            }
        });
    });

    document.addEventListener('click', function(e) {
        const menu = document.getElementById('menuContextoUsuario');

        if (menu && !menu.contains(e.target) && !e.target.closest('.usuario-item')) {
            fecharMenuContexto();
        }
    });

    window.addEventListener('scroll', fecharMenuContexto);
    window.addEventListener('resize', fecharMenuContexto);


    document.addEventListener('DOMContentLoaded', function() {
        garantirModalConfirmacaoNoBody();
    });

    const modalConfirmacaoUsuario = document.getElementById('modalConfirmacaoUsuario');
    const modalEditar = document.getElementById('modalEditar');
    const modalExcluir = document.getElementById('modalExcluir');
    const modalFiltros = document.getElementById('modalFiltros');

    if (modalConfirmacaoUsuario) {
        modalConfirmacaoUsuario.addEventListener('click', function(e) {
            if (e.target === this) {
                fecharModalConfirmacaoUsuario();
            }
        });
    }

    if (modalEditar) {
        modalEditar.addEventListener('click', function(e) {
            if (e.target === this) {
                fecharModal();
            }
        });
    }

    if (modalExcluir) {
        modalExcluir.addEventListener('click', function(e) {
            if (e.target === this) {
                fecharModalExcluir();
            }
        });
    }

    if (modalFiltros) {
        modalFiltros.addEventListener('click', function(e) {
            if (e.target === this) {
                fecharModalFiltros();
            }
        });
    }

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            fecharModalConfirmacaoUsuario();
            fecharModal();
            fecharModalExcluir();
            fecharModalFiltros();
            fecharMenuContexto();
        }
    });
</script>

@endsection