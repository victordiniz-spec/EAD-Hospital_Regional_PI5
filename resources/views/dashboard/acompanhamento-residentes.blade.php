@extends('layout.app')

@section('title', 'Acompanhamento dos Residentes')

@section('content')

@php
    use Carbon\Carbon;

    function formatarDataAcompanhamento($data) {
        if (!$data) return 'Sem registro';

        try {
            return Carbon::parse($data)
                ->timezone('America/Sao_Paulo')
                ->format('d/m/Y H:i');
        } catch (\Throwable $e) {
            return $data;
        }
    }

    function statusResidenteAcompanhamento($status) {
        $status = strtolower($status ?? '');

        return match ($status) {
            'aprovado' => 'Ativo',
            'pendente' => 'Pendente',
            'inutilizado' => 'Inutilizado',
            default => ucfirst($status ?: 'Indefinido'),
        };
    }
@endphp

<div class="flex min-h-screen w-full bg-[#F3F7F3] text-[#003C2F] overflow-x-hidden">

    @include('partials.sidebar-professor')

    <main class="flex-1 min-w-0 w-full bg-[#F3F7F3] overflow-x-hidden">

        @include('partials.navbar')

        <section class="p-4 sm:p-6 lg:p-8">

            <!-- CABEÇALHO -->
            <div class="mb-7 flex flex-col xl:flex-row xl:items-end xl:justify-between gap-5">
                <div class="min-w-0">
                    <div class="inline-flex items-center gap-2 text-[11px] font-bold uppercase tracking-widest text-[#00A63E] mb-2">
                        <span class="w-2 h-2 rounded-full bg-[#00A63E]"></span>
                        Painel inteligente
                    </div>

                    <h1 class="text-2xl sm:text-4xl font-extrabold text-[#003C2F] tracking-tight break-words">
                        Acompanhamento dos Residentes
                    </h1>

                    <p class="text-sm text-[#60756B] mt-2 max-w-3xl leading-relaxed">
                        Acompanhe progresso, pós-testes pendentes, alunos em risco e residentes próximos de liberar certificado.
                        Esta tela ajuda o professor a identificar rapidamente quem precisa de atenção.
                    </p>
                </div>

                <div class="flex flex-col sm:flex-row gap-3 w-full xl:w-auto">
                    <a href="{{ route('controle.usuarios') }}"
                       class="w-full sm:w-auto inline-flex items-center justify-center gap-2 bg-white text-[#004D3A] border border-[#DCE7DE] px-5 py-3 rounded-2xl shadow-sm hover:bg-[#F8FBF8] transition text-sm font-bold">
                        Ver usuários
                    </a>

                    <a href="{{ route('videoaulas') }}"
                       class="w-full sm:w-auto inline-flex items-center justify-center gap-2 bg-[#004D3A] text-white px-5 py-3 rounded-2xl shadow-sm hover:bg-[#003C2F] transition text-sm font-bold">
                        Gerenciar aulas
                    </a>
                </div>
            </div>

            <!-- CARDS PRINCIPAIS -->
            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-5 gap-4 mb-7">

                <div class="bg-white border-l-4 border-[#004D3A] rounded-3xl p-5 shadow-sm border-y border-r border-[#E3EBE4]">
                    <p class="text-[11px] uppercase tracking-widest text-[#60756B] font-extrabold">
                        Residentes ativos
                    </p>
                    <h3 class="text-3xl font-extrabold mt-2 text-[#003C2F]">
                        {{ $residentesAtivos }}
                    </h3>
                    <p class="text-xs text-[#60756B] mt-2">
                        Usuários liberados para estudar.
                    </p>
                </div>

                <div class="bg-white border-l-4 border-yellow-400 rounded-3xl p-5 shadow-sm border-y border-r border-[#E3EBE4]">
                    <p class="text-[11px] uppercase tracking-widest text-[#60756B] font-extrabold">
                        Pendentes
                    </p>
                    <h3 class="text-3xl font-extrabold mt-2 text-yellow-600">
                        {{ $residentesPendentesAprovacao }}
                    </h3>
                    <p class="text-xs text-[#60756B] mt-2">
                        Aguardando aprovação.
                    </p>
                </div>

                <div class="bg-white border-l-4 border-red-500 rounded-3xl p-5 shadow-sm border-y border-r border-[#E3EBE4]">
                    <p class="text-[11px] uppercase tracking-widest text-[#60756B] font-extrabold">
                        Alunos em risco
                    </p>
                    <h3 class="text-3xl font-extrabold mt-2 text-red-600">
                        {{ $alunosEmRisco }}
                    </h3>
                    <p class="text-xs text-[#60756B] mt-2">
                        Baixo progresso ou pendências.
                    </p>
                </div>

                <div class="bg-white border-l-4 border-blue-500 rounded-3xl p-5 shadow-sm border-y border-r border-[#E3EBE4]">
                    <p class="text-[11px] uppercase tracking-widest text-[#60756B] font-extrabold">
                        Pós-testes pendentes
                    </p>
                    <h3 class="text-3xl font-extrabold mt-2 text-blue-600">
                        {{ $postestesPendentes }}
                    </h3>
                    <p class="text-xs text-[#60756B] mt-2">
                        Total acumulado da turma.
                    </p>
                </div>

                <div class="bg-white border-l-4 border-[#00A63E] rounded-3xl p-5 shadow-sm border-y border-r border-[#E3EBE4]">
                    <p class="text-[11px] uppercase tracking-widest text-[#60756B] font-extrabold">
                        Quase certificado
                    </p>
                    <h3 class="text-3xl font-extrabold mt-2 text-[#00A63E]">
                        {{ $certificadosQuaseLiberados }}
                    </h3>
                    <p class="text-xs text-[#60756B] mt-2">
                        Progresso e média favoráveis.
                    </p>
                </div>

            </div>

            <!-- PAINEL DE INTELIGÊNCIA -->
            <div class="grid grid-cols-1 xl:grid-cols-12 gap-7 mb-7">

                <!-- ALUNOS EM RISCO -->
                <div class="xl:col-span-7 bg-white border border-[#E3EBE4] rounded-3xl shadow-sm overflow-hidden">
                    <div class="p-5 sm:p-6 border-b border-[#E3EBE4]">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                            <div>
                                <p class="text-[11px] uppercase tracking-widest text-red-600 font-extrabold">
                                    Atenção do professor
                                </p>
                                <h2 class="text-xl font-extrabold text-[#003C2F] mt-1">
                                    Residentes que precisam de acompanhamento
                                </h2>
                            </div>

                            <span class="bg-red-50 text-red-600 border border-red-100 px-4 py-2 rounded-2xl text-sm font-extrabold">
                                {{ $alunosEmRisco }} em risco
                            </span>
                        </div>
                    </div>

                    <div class="p-5 sm:p-6 space-y-3">
                        @forelse($residentesEmRiscoLista as $residente)
                            <div class="bg-[#F8FBF8] border border-[#E3EBE4] rounded-3xl p-4">
                                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                                    <div class="min-w-0">
                                        <h3 class="font-extrabold text-[#003C2F] break-words">
                                            {{ $residente->name }}
                                        </h3>
                                        <p class="text-xs text-[#60756B] mt-1 break-all">
                                            {{ $residente->email }}
                                        </p>
                                    </div>

                                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 text-center">
                                        <div class="bg-white border border-[#DCE7DE] rounded-2xl px-3 py-2">
                                            <p class="text-[10px] uppercase tracking-widest text-[#60756B] font-extrabold">Progresso</p>
                                            <p class="text-lg font-extrabold text-[#003C2F]">{{ $residente->progresso }}%</p>
                                        </div>

                                        <div class="bg-white border border-[#DCE7DE] rounded-2xl px-3 py-2">
                                            <p class="text-[10px] uppercase tracking-widest text-[#60756B] font-extrabold">Pendentes</p>
                                            <p class="text-lg font-extrabold text-red-600">{{ $residente->postestes_pendentes }}</p>
                                        </div>

                                        <div class="bg-white border border-[#DCE7DE] rounded-2xl px-3 py-2">
                                            <p class="text-[10px] uppercase tracking-widest text-[#60756B] font-extrabold">Média</p>
                                            <p class="text-lg font-extrabold text-blue-600">{{ number_format($residente->media, 1, ',', '.') }}</p>
                                        </div>

                                        <div class="bg-white border border-[#DCE7DE] rounded-2xl px-3 py-2">
                                            <p class="text-[10px] uppercase tracking-widest text-[#60756B] font-extrabold">Dias sem progresso</p>
                                            <p class="text-lg font-extrabold text-yellow-600">
                                                {{ $residente->dias_sem_atividade === null ? '-' : (int) $residente->dias_sem_atividade }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="bg-green-50 border border-green-100 rounded-3xl p-8 text-center">
                                <h3 class="text-lg font-extrabold text-green-700">
                                    Nenhum residente em risco no momento
                                </h3>
                                <p class="text-sm text-green-700/80 mt-2">
                                    A turma está com bom acompanhamento.
                                </p>
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- RANKING -->
                <div class="xl:col-span-5 bg-white border border-[#E3EBE4] rounded-3xl shadow-sm overflow-hidden">
                    <div class="p-5 sm:p-6 border-b border-[#E3EBE4]">
                        <p class="text-[11px] uppercase tracking-widest text-[#00A63E] font-extrabold">
                            Evolução
                        </p>
                        <h2 class="text-xl font-extrabold text-[#003C2F] mt-1">
                            Ranking de progresso
                        </h2>
                    </div>

                    <div class="p-5 sm:p-6 space-y-3">
                        @forelse($rankingEvolucao as $index => $residente)
                            <div class="flex items-center gap-4 bg-[#F8FBF8] border border-[#E3EBE4] rounded-3xl p-4">
                                <div class="w-11 h-11 rounded-2xl bg-[#004D3A] text-white flex items-center justify-center font-extrabold shrink-0">
                                    {{ $index + 1 }}
                                </div>

                                <div class="min-w-0 flex-1">
                                    <h3 class="font-extrabold text-[#003C2F] truncate">
                                        {{ $residente->name }}
                                    </h3>

                                    <div class="mt-2 h-2 bg-[#DCE7DE] rounded-full overflow-hidden">
                                        <div class="h-full bg-[#00A63E] rounded-full" style="width: {{ min($residente->progresso, 100) }}%;"></div>
                                    </div>
                                </div>

                                <div class="text-right shrink-0">
                                    <p class="text-xl font-extrabold text-[#003C2F]">
                                        {{ $residente->progresso }}%
                                    </p>
                                    <p class="text-[10px] uppercase tracking-widest text-[#60756B] font-extrabold">
                                        concluído
                                    </p>
                                </div>
                            </div>
                        @empty
                            <div class="bg-[#F8FBF8] border border-[#E3EBE4] rounded-3xl p-8 text-center text-[#60756B]">
                                Nenhum progresso registrado ainda.
                            </div>
                        @endforelse
                    </div>
                </div>

            </div>

            <!-- TABELA COMPLETA -->
            <div class="bg-white border border-[#E3EBE4] rounded-3xl shadow-sm overflow-hidden">
                <div class="p-5 sm:p-6 border-b border-[#E3EBE4] flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                    <div>
                        <p class="text-[11px] uppercase tracking-widest text-[#60756B] font-extrabold">
                            Visão geral da turma
                        </p>
                        <h2 class="text-xl font-extrabold text-[#003C2F] mt-1">
                            Todos os residentes
                        </h2>
                    </div>

                    <div class="bg-[#EAF5EF] text-[#004D3A] px-4 py-3 rounded-2xl text-sm font-extrabold">
                        Média geral da turma: {{ number_format($mediaGeralTurma, 1, ',', '.') }}
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-[#F8FBF8] text-[#60756B] uppercase text-[11px] tracking-widest">
                            <tr>
                                <th class="px-5 py-4 text-left font-extrabold">Residente</th>
                                <th class="px-5 py-4 text-left font-extrabold">Status</th>
                                <th class="px-5 py-4 text-left font-extrabold">Progresso</th>
                                <th class="px-5 py-4 text-left font-extrabold">Aulas</th>
                                <th class="px-5 py-4 text-left font-extrabold">Pós-testes</th>
                                <th class="px-5 py-4 text-left font-extrabold">Média</th>
                                <th class="px-5 py-4 text-left font-extrabold">Última atividade</th>
                                <th class="px-5 py-4 text-left font-extrabold">Situação</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-[#E3EBE4]">
                            @forelse($linhasResidentes as $residente)
                                <tr class="hover:bg-[#F8FBF8] transition">
                                    <td class="px-5 py-4">
                                        <div class="font-extrabold text-[#003C2F]">
                                            {{ $residente->name }}
                                        </div>
                                        <div class="text-xs text-[#60756B] break-all">
                                            {{ $residente->email }}
                                        </div>
                                    </td>

                                    <td class="px-5 py-4">
                                        <span class="inline-flex px-3 py-1 rounded-full text-xs font-extrabold
                                            {{ $residente->status === 'aprovado'
                                                ? 'bg-green-100 text-green-700'
                                                : ($residente->status === 'pendente'
                                                    ? 'bg-yellow-100 text-yellow-700'
                                                    : 'bg-red-100 text-red-700')
                                            }}">
                                            {{ statusResidenteAcompanhamento($residente->status) }}
                                        </span>
                                    </td>

                                    <td class="px-5 py-4 min-w-[150px]">
                                        <div class="flex items-center gap-3">
                                            <div class="flex-1 h-2 bg-[#DCE7DE] rounded-full overflow-hidden">
                                                <div class="h-full rounded-full {{ $residente->progresso >= 70 ? 'bg-[#00A63E]' : ($residente->progresso >= 40 ? 'bg-yellow-400' : 'bg-red-500') }}"
                                                     style="width: {{ min($residente->progresso, 100) }}%;"></div>
                                            </div>
                                            <strong>{{ $residente->progresso }}%</strong>
                                        </div>
                                    </td>

                                    <td class="px-5 py-4 font-bold text-[#003C2F]">
                                        {{ $residente->aulas_assistidas }} / {{ $residente->total_aulas }}
                                    </td>

                                    <td class="px-5 py-4 font-bold text-[#003C2F]">
                                        {{ $residente->avaliacoes_feitas }} / {{ $residente->total_avaliacoes }}
                                        <div class="text-xs text-red-600 font-bold">
                                            {{ $residente->postestes_pendentes }} pendente(s)
                                        </div>
                                    </td>

                                    <td class="px-5 py-4">
                                        <strong class="text-blue-600">
                                            {{ number_format($residente->media, 1, ',', '.') }}
                                        </strong>
                                    </td>

                                    <td class="px-5 py-4 text-[#60756B]">
                                        {{ formatarDataAcompanhamento($residente->ultima_atividade) }}
                                    </td>

                                    <td class="px-5 py-4">
                                        @if($residente->quase_certificado)
                                            <span class="inline-flex px-3 py-1 rounded-full bg-green-100 text-green-700 text-xs font-extrabold">
                                                Quase certificado
                                            </span>
                                        @elseif($residente->em_risco)
                                            <span class="inline-flex px-3 py-1 rounded-full bg-red-100 text-red-700 text-xs font-extrabold">
                                                Precisa atenção
                                            </span>
                                        @else
                                            <span class="inline-flex px-3 py-1 rounded-full bg-[#EAF5EF] text-[#004D3A] text-xs font-extrabold">
                                                Acompanhado
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-5 py-10 text-center text-[#60756B]">
                                        Nenhum residente encontrado.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </section>

    </main>

</div>

@endsection
