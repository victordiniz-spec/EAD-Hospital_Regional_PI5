@extends('layout.app')

@section('title', 'Controle de Usuários')

@section('content')

@php
    $totalUsuarios = $usuarios->count();

    $usuariosAtivos = $usuarios->where('status', 'aprovado')->count();

    $usuariosPendentes = $usuarios->filter(function ($user) {
        return $user->status !== 'aprovado';
    })->count();

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
</style>

<div class="flex min-h-screen w-full bg-[#F3F7F3] text-[#003C2F] overflow-x-hidden">

    @include('partials.sidebar-professor')

    <main class="flex-1 min-w-0 w-full bg-[#F3F7F3] overflow-x-hidden">

        @include('partials.navbar')

        <section class="p-4 sm:p-6 lg:p-8">

            <!-- CABEÇALHO CENTRALIZADO -->
            <div class="mb-8">

                <div class="max-w-4xl mx-auto text-center">

                    <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold text-[#003C2F] tracking-tight leading-tight">
                        Controle de Usuários
                    </h1>

                    <p class="text-base sm:text-lg text-[#60756B] mt-4 max-w-2xl mx-auto leading-relaxed">
                        Administre acessos, perfis, datas de cadastro, aprovações e permissões da instituição.
                    </p>

                </div>

                <!-- PESQUISA E BOTÕES -->
                <div class="mt-8 max-w-6xl mx-auto">

                    <div class="flex flex-col xl:flex-row items-stretch justify-center gap-4">

                        <!-- PESQUISA -->
                        <div class="relative flex-1 max-w-3xl">
                            <span class="absolute inset-y-0 left-6 flex items-center text-[#8A9B92]">
                                <svg xmlns="http://www.w3.org/2000/svg"
                                     class="w-7 h-7"
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
                                placeholder="Pesquisar aluno, CPF, e-mail..."
                                class="w-full h-[72px] bg-white border-2 border-[#00A63E] text-[#003C2F] placeholder-[#8A9B92] rounded-[26px] pl-17 pr-6 text-base sm:text-lg shadow-sm focus:outline-none focus:ring-4 focus:ring-green-100 focus:border-[#00A63E] transition"
                            >
                        </div>

                        <!-- BOTÃO FILTROS -->
                        <button type="button"
                                onclick="abrirModalFiltros()"
                                class="h-[72px] bg-[#EAF5EF] border border-[#DCE7DE] text-[#004D3A] px-7 rounded-[26px] hover:bg-[#DFF1E5] transition flex items-center justify-center gap-3 text-base font-extrabold shadow-sm min-w-[230px]">

                            <svg xmlns="http://www.w3.org/2000/svg"
                                 class="w-6 h-6"
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

                        <!-- BOTÃO LIMPAR -->
                        <button type="button"
                                onclick="limparPesquisa()"
                                class="h-[72px] bg-white border border-[#DCE7DE] text-[#60756B] px-7 rounded-[26px] hover:bg-[#F8FBF8] transition flex items-center justify-center gap-3 text-base font-bold shadow-sm min-w-[160px]">

                            <svg xmlns="http://www.w3.org/2000/svg"
                                 class="w-6 h-6"
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

            <!-- TABELA DESKTOP -->
            <div class="hidden xl:block bg-white rounded-3xl shadow-sm border border-[#E3EBE4] overflow-hidden w-full mb-7">

                <div class="overflow-x-auto w-full">

                    <table class="w-full text-sm">

                        <thead>
                            <tr class="text-left text-[11px] uppercase tracking-widest text-[#60756B] bg-[#F8FBF8] border-b border-[#E3EBE4]">
                                <th class="py-5 px-6">Nome</th>
                                <th class="py-5 px-6">E-mail</th>
                                <th class="py-5 px-6">CPF</th>
                                <th class="py-5 px-6">Tipo</th>
                                <th class="py-5 px-6">Status</th>
                                <th class="py-5 px-6">Cadastro</th>
                                <th class="py-5 px-6">Aceito em</th>
                                <th class="py-5 px-6 text-right">Ações</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-[#EEF3EF]">

                            @foreach($usuarios as $user)

                                @php
                                    $nomePartes = preg_split('/\s+/', trim($user->name));
                                    $iniciais = strtoupper(substr($nomePartes[0] ?? 'U', 0, 1));

                                    if (count($nomePartes) > 1) {
                                        $iniciais .= strtoupper(substr(end($nomePartes), 0, 1));
                                    }

                                    $tipoUser = strtolower($user->tipo ?? '');
                                    $statusAtivo = $user->status === 'aprovado';

                                    $dataCadastro = $user->created_at ? \Carbon\Carbon::parse($user->created_at) : null;

                                    $dataAceito = $statusAtivo && $user->updated_at
                                        ? \Carbon\Carbon::parse($user->updated_at)
                                        : null;
                                @endphp

                                <tr class="usuario-item hover:bg-[#F8FBF8] transition"
                                    data-tipo="{{ $tipoUser }}"
                                    data-status="{{ $statusAtivo ? 'ativo' : 'inativo' }}"
                                    data-cadastro="{{ $dataCadastro ? $dataCadastro->format('Y-m-d') : '' }}"
                                    data-aceito="{{ $dataAceito ? $dataAceito->format('Y-m-d') : '' }}"
                                    data-search="{{ strtolower($user->name . ' ' . $user->email . ' ' . $user->cpf . ' ' . $user->tipo . ' ' . $user->status . ' ' . ($dataCadastro ? $dataCadastro->format('d/m/Y') : '') . ' ' . ($dataAceito ? $dataAceito->format('d/m/Y') : '')) }}">

                                    <td class="py-6 px-6">
                                        <div class="flex items-center gap-4">

                                            <div class="w-12 h-12 rounded-full
                                                @if($statusAtivo) bg-[#00A63E] text-white
                                                @else bg-[#E1E7E2] text-[#60756B]
                                                @endif
                                                flex items-center justify-center font-extrabold shrink-0">
                                                {{ $iniciais }}
                                            </div>

                                            <div class="min-w-0">
                                                <p class="font-extrabold text-[#1F2A24] break-words leading-tight">
                                                    {{ $user->name }}
                                                </p>
                                            </div>

                                        </div>
                                    </td>

                                    <td class="text-[#4B5C52] break-words px-6">
                                        {{ $user->email }}
                                    </td>

                                    <td class="text-[#4B5C52] whitespace-nowrap px-6">
                                        {{ $user->cpf }}
                                    </td>

                                    <td class="px-6">
                                        <span class="inline-flex items-center gap-1 bg-green-100 text-green-700 px-3 py-1 rounded-full text-[11px] font-extrabold whitespace-nowrap">
                                            {{ strtoupper($user->tipo ?? 'USUÁRIO') }}
                                        </span>
                                    </td>

                                    <td class="px-6">
                                        @if($statusAtivo)
                                            <span class="inline-flex items-center gap-2 text-green-700 font-extrabold text-xs whitespace-nowrap">
                                                <span class="w-2 h-2 rounded-full bg-green-600"></span>
                                                ATIVO
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-2 text-[#8A9B92] font-extrabold text-xs whitespace-nowrap">
                                                <span class="w-2 h-2 rounded-full bg-[#AAB7AF]"></span>
                                                PENDENTE
                                            </span>
                                        @endif
                                    </td>

                                    <td class="px-6 text-[#4B5C52] whitespace-nowrap">
                                        {{ $dataCadastro ? $dataCadastro->format('d/m/Y H:i') : '-' }}
                                    </td>

                                    <td class="px-6 text-[#4B5C52] whitespace-nowrap">
                                        @if($dataAceito)
                                            <span class="font-bold text-green-700">
                                                {{ $dataAceito->format('d/m/Y H:i') }}
                                            </span>
                                        @else
                                            <span class="text-[#8A9B92]">Ainda não aceito</span>
                                        @endif
                                    </td>

                                    <td class="text-right px-6">
                                        <div class="flex items-center justify-end gap-2">

                                            <button
                                                onclick='abrirModalEditar(
                                                    @json($user->id),
                                                    @json($user->name),
                                                    @json($user->email),
                                                    @json($user->cpf)
                                                )'
                                                class="w-10 h-10 rounded-xl hover:bg-[#EAF5EF] text-[#004D3A] transition inline-flex items-center justify-center"
                                                title="Editar usuário">

                                                <svg xmlns="http://www.w3.org/2000/svg"
                                                     class="w-5 h-5"
                                                     fill="none"
                                                     viewBox="0 0 24 24"
                                                     stroke="currentColor">
                                                    <path stroke-linecap="round"
                                                          stroke-linejoin="round"
                                                          stroke-width="1.8"
                                                          d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931z"/>
                                                </svg>
                                            </button>

                                            <button type="button"
                                                    onclick='abrirModalExcluir(
                                                        @json($user->id),
                                                        @json($user->name),
                                                        @json($user->email)
                                                    )'
                                                    class="w-10 h-10 rounded-xl hover:bg-red-50 text-red-600 transition inline-flex items-center justify-center"
                                                    title="Excluir usuário">
                                                <svg xmlns="http://www.w3.org/2000/svg"
                                                     class="w-5 h-5"
                                                     fill="none"
                                                     viewBox="0 0 24 24"
                                                     stroke="currentColor">
                                                    <path stroke-linecap="round"
                                                          stroke-linejoin="round"
                                                          stroke-width="1.8"
                                                          d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166M19.228 5.79 18.16 19.673A2.25 2.25 0 0 1 15.916 21.75H8.084A2.25 2.25 0 0 1 5.84 19.673L4.772 5.79M19.228 5.79a48.108 48.108 0 0 0-3.478-.397M4.772 5.79c1.148-.175 2.32-.302 3.478-.397m7.5 0V4.875C15.75 3.839 14.911 3 13.875 3h-3.75C9.089 3 8.25 3.839 8.25 4.875v.518m7.5 0a48.667 48.667 0 0 0-7.5 0"/>
                                                </svg>
                                            </button>

                                        </div>
                                    </td>

                                </tr>
                            @endforeach

                        </tbody>

                    </table>

                </div>

                <div class="bg-[#F8FBF8] border-t border-[#E3EBE4] px-6 py-4 flex items-center justify-between text-xs text-[#60756B]">

                    <p>
                        Mostrando <strong id="contadorUsuariosVisiveis">{{ $usuarios->count() }}</strong> de
                        <strong>{{ $usuarios->count() }}</strong> usuários registrados
                    </p>

                    <div class="flex items-center gap-2">
                        <button class="w-9 h-9 rounded-xl bg-[#EEF3EF] text-[#AAB7AF] flex items-center justify-center cursor-not-allowed">
                            ‹
                        </button>

                        <button class="w-9 h-9 rounded-xl bg-[#004D3A] text-white flex items-center justify-center">
                            ›
                        </button>
                    </div>

                </div>

            </div>

            <!-- CARDS MOBILE / TABLET -->
            <div class="xl:hidden space-y-4 mb-7" id="listaUsuariosMobile">

                @foreach($usuarios as $user)

                    @php
                        $nomePartesMobile = preg_split('/\s+/', trim($user->name));
                        $iniciaisMobile = strtoupper(substr($nomePartesMobile[0] ?? 'U', 0, 1));

                        if (count($nomePartesMobile) > 1) {
                            $iniciaisMobile .= strtoupper(substr(end($nomePartesMobile), 0, 1));
                        }

                        $tipoUserMobile = strtolower($user->tipo ?? '');
                        $statusAtivoMobile = $user->status === 'aprovado';

                        $dataCadastroMobile = $user->created_at ? \Carbon\Carbon::parse($user->created_at) : null;

                        $dataAceitoMobile = $statusAtivoMobile && $user->updated_at
                            ? \Carbon\Carbon::parse($user->updated_at)
                            : null;
                    @endphp

                    <div class="usuario-item bg-white border border-[#E3EBE4] rounded-3xl p-5 shadow-sm"
                         data-tipo="{{ $tipoUserMobile }}"
                         data-status="{{ $statusAtivoMobile ? 'ativo' : 'inativo' }}"
                         data-cadastro="{{ $dataCadastroMobile ? $dataCadastroMobile->format('Y-m-d') : '' }}"
                         data-aceito="{{ $dataAceitoMobile ? $dataAceitoMobile->format('Y-m-d') : '' }}"
                         data-search="{{ strtolower($user->name . ' ' . $user->email . ' ' . $user->cpf . ' ' . $user->tipo . ' ' . $user->status . ' ' . ($dataCadastroMobile ? $dataCadastroMobile->format('d/m/Y') : '') . ' ' . ($dataAceitoMobile ? $dataAceitoMobile->format('d/m/Y') : '')) }}">

                        <div class="flex items-start gap-4">

                            <div class="w-12 h-12 rounded-full
                                @if($statusAtivoMobile) bg-[#00A63E] text-white
                                @else bg-[#E1E7E2] text-[#60756B]
                                @endif
                                flex items-center justify-center font-extrabold shrink-0">
                                {{ $iniciaisMobile }}
                            </div>

                            <div class="min-w-0 flex-1">
                                <p class="font-extrabold text-[#003C2F] break-words leading-tight">
                                    {{ $user->name }}
                                </p>

                                <p class="text-sm text-[#60756B] break-words mt-1">
                                    {{ $user->email }}
                                </p>
                            </div>

                        </div>

                        <div class="mt-4 space-y-2 text-sm">

                            <div class="bg-[#F8FBF8] border border-[#E3EBE4] rounded-2xl px-4 py-3">
                                <span class="text-[#60756B] font-semibold">CPF:</span>
                                <span class="text-[#003C2F] font-bold">{{ $user->cpf }}</span>
                            </div>

                            <div class="bg-[#F8FBF8] border border-[#E3EBE4] rounded-2xl px-4 py-3">
                                <span class="text-[#60756B] font-semibold">Cadastro:</span>
                                <span class="text-[#003C2F] font-bold">
                                    {{ $dataCadastroMobile ? $dataCadastroMobile->format('d/m/Y H:i') : '-' }}
                                </span>
                            </div>

                            <div class="bg-[#F8FBF8] border border-[#E3EBE4] rounded-2xl px-4 py-3">
                                <span class="text-[#60756B] font-semibold">Aceito em:</span>

                                @if($dataAceitoMobile)
                                    <span class="text-green-700 font-bold">
                                        {{ $dataAceitoMobile->format('d/m/Y H:i') }}
                                    </span>
                                @else
                                    <span class="text-[#8A9B92] font-bold">
                                        Ainda não aceito
                                    </span>
                                @endif
                            </div>

                            <div class="flex flex-wrap gap-2 mt-3">

                                <span class="inline-flex items-center bg-green-100 text-green-700 px-3 py-1 rounded-full text-[11px] font-extrabold">
                                    {{ strtoupper($user->tipo ?? 'USUÁRIO') }}
                                </span>

                                @if($statusAtivoMobile)
                                    <span class="inline-flex items-center gap-1 text-green-700 px-3 py-1 rounded-full text-[11px] font-extrabold">
                                        <span class="w-2 h-2 bg-green-600 rounded-full"></span>
                                        ATIVO
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 text-[#8A9B92] px-3 py-1 rounded-full text-[11px] font-extrabold">
                                        <span class="w-2 h-2 bg-[#AAB7AF] rounded-full"></span>
                                        PENDENTE
                                    </span>
                                @endif

                            </div>

                        </div>

                        <div class="mt-5 grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <button
                                onclick='abrirModalEditar(@json($user->id), @json($user->name), @json($user->email), @json($user->cpf))'
                                class="w-full bg-[#004D3A] hover:bg-[#003C2F] text-white px-4 py-3 rounded-2xl transition text-sm font-extrabold flex items-center justify-center gap-2 shadow-sm">

                                <svg xmlns="http://www.w3.org/2000/svg"
                                     class="w-4 h-4"
                                     fill="none"
                                     viewBox="0 0 24 24"
                                     stroke="currentColor">
                                    <path stroke-linecap="round"
                                          stroke-linejoin="round"
                                          stroke-width="1.8"
                                          d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931z"/>
                                </svg>

                                Editar
                            </button>

                            <button
                                onclick='abrirModalExcluir(@json($user->id), @json($user->name), @json($user->email))'
                                class="w-full bg-red-50 hover:bg-red-100 text-red-600 border border-red-200 px-4 py-3 rounded-2xl transition text-sm font-extrabold flex items-center justify-center gap-2">

                                <svg xmlns="http://www.w3.org/2000/svg"
                                     class="w-4 h-4"
                                     fill="none"
                                     viewBox="0 0 24 24"
                                     stroke="currentColor">
                                    <path stroke-linecap="round"
                                          stroke-linejoin="round"
                                          stroke-width="1.8"
                                          d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166M19.228 5.79 18.16 19.673A2.25 2.25 0 0 1 15.916 21.75H8.084A2.25 2.25 0 0 1 5.84 19.673L4.772 5.79"/>
                                </svg>

                                Excluir
                            </button>
                        </div>

                    </div>

                @endforeach

            </div>

            <!-- DADOS GERAIS -->
            <div class="mb-7">

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

                <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5">

                    <!-- TOTAL -->
                    <div class="bg-white rounded-3xl p-5 shadow-sm border border-[#E3EBE4] border-l-4 border-l-[#004D3A] hover:shadow-lg transition">

                        <div class="flex items-start justify-between mb-5">
                            <div class="w-12 h-12 rounded-2xl bg-[#EAF5EF] flex items-center justify-center text-[#004D3A]">
                                <svg xmlns="http://www.w3.org/2000/svg"
                                     class="w-6 h-6"
                                     fill="none"
                                     viewBox="0 0 24 24"
                                     stroke="currentColor">
                                    <path stroke-linecap="round"
                                          stroke-linejoin="round"
                                          stroke-width="1.8"
                                          d="M18 18.72a8.94 8.94 0 0 0-6-2.22 8.94 8.94 0 0 0-6 2.22M15 11.25a3 3 0 1 0-6 0 3 3 0 0 0 6 0z"/>
                                </svg>
                            </div>

                            <span class="text-[11px] font-bold bg-green-100 text-green-700 px-2.5 py-1 rounded-full">
                                +{{ $totalUsuarios }}
                            </span>
                        </div>

                        <p class="text-[11px] uppercase tracking-widest text-[#60756B] font-extrabold">
                            Total de usuários
                        </p>

                        <h3 class="text-3xl font-extrabold mt-2 text-[#003C2F]">
                            {{ $totalUsuarios }}
                        </h3>

                        <div class="mt-4 h-1.5 bg-[#E8EFE9] rounded-full overflow-hidden">
                            <div class="h-full bg-[#004D3A] rounded-full" style="width: {{ $totalUsuarios > 0 ? 100 : 0 }}%;"></div>
                        </div>
                    </div>

                    <!-- PRECEPTORES -->
                    <div class="bg-white rounded-3xl p-5 shadow-sm border border-[#E3EBE4] border-l-4 border-l-[#00A63E] hover:shadow-lg transition">

                        <div class="flex items-start justify-between mb-5">
                            <div class="w-12 h-12 rounded-2xl bg-[#EAF5EF] flex items-center justify-center text-[#00A63E]">
                                <svg xmlns="http://www.w3.org/2000/svg"
                                     class="w-6 h-6"
                                     fill="none"
                                     viewBox="0 0 24 24"
                                     stroke="currentColor">
                                    <path stroke-linecap="round"
                                          stroke-linejoin="round"
                                          stroke-width="1.8"
                                          d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0z"/>
                                </svg>
                            </div>
                        </div>

                        <p class="text-[11px] uppercase tracking-widest text-[#60756B] font-extrabold">
                            Preceptores ativos
                        </p>

                        <h3 class="text-3xl font-extrabold mt-2 text-[#003C2F]">
                            {{ $preceptoresAtivos }}
                        </h3>

                        <p class="text-xs text-[#60756B] mt-2">
                            Profissionais aprovados.
                        </p>
                    </div>

                    <!-- RESIDENTES -->
                    <div class="bg-white rounded-3xl p-5 shadow-sm border border-[#E3EBE4] border-l-4 border-l-[#7EDB90] hover:shadow-lg transition">

                        <div class="flex items-start justify-between mb-5">
                            <div class="w-12 h-12 rounded-2xl bg-[#EAF5EF] flex items-center justify-center text-[#00A63E]">
                                <svg xmlns="http://www.w3.org/2000/svg"
                                     class="w-6 h-6"
                                     fill="none"
                                     viewBox="0 0 24 24"
                                     stroke="currentColor">
                                    <path stroke-linecap="round"
                                          stroke-linejoin="round"
                                          stroke-width="1.8"
                                          d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0zM4.5 20.25a8.25 8.25 0 0 1 15 0"/>
                                </svg>
                            </div>
                        </div>

                        <p class="text-[11px] uppercase tracking-widest text-[#60756B] font-extrabold">
                            Residentes ativos
                        </p>

                        <h3 class="text-3xl font-extrabold mt-2 text-[#003C2F]">
                            {{ $residentesAtivos }}
                        </h3>

                        <p class="text-xs text-[#60756B] mt-2">
                            Alunos liberados no sistema.
                        </p>
                    </div>

                    <!-- PENDENTES -->
                    <div class="bg-white rounded-3xl p-5 shadow-sm border border-[#E3EBE4] border-l-4 border-l-red-500 hover:shadow-lg transition">

                        <div class="flex items-start justify-between mb-5">
                            <div class="w-12 h-12 rounded-2xl bg-red-50 flex items-center justify-center text-red-600">
                                <svg xmlns="http://www.w3.org/2000/svg"
                                     class="w-6 h-6"
                                     fill="none"
                                     viewBox="0 0 24 24"
                                     stroke="currentColor">
                                    <path stroke-linecap="round"
                                          stroke-linejoin="round"
                                          stroke-width="1.8"
                                          d="M12 9v3.75m0 3.75h.008v.008H12V16.5zm9-4.5a9 9 0 1 1-18 0 9 9 0 0 1 18 0z"/>
                                </svg>
                            </div>

                            <span class="w-2 h-2 rounded-full bg-red-600"></span>
                        </div>

                        <p class="text-[11px] uppercase tracking-widest text-[#60756B] font-extrabold">
                            Solicitações pendentes
                        </p>

                        <h3 class="text-3xl font-extrabold mt-2 text-red-600">
                            {{ $usuariosPendentes }}
                        </h3>

                        <p class="text-xs text-[#60756B] mt-2 underline">
                            Revisar registros novos
                        </p>
                    </div>

                </div>

            </div>

        </section>

    </main>

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
            Tem certeza que deseja excluir este usuário? Essa ação não poderá ser desfeita.
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
                    <option value="inativo">Pendentes</option>
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

    const modalEditar = document.getElementById('modalEditar');
    const modalExcluir = document.getElementById('modalExcluir');
    const modalFiltros = document.getElementById('modalFiltros');

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
            fecharModal();
            fecharModalExcluir();
            fecharModalFiltros();
        }
    });
</script>

@endsection