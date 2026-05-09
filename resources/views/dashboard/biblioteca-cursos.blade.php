@extends('layout.app')

@section('title', 'Biblioteca de Cursos')

@section('content')

@php
    use Illuminate\Support\Facades\DB;
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

            <!-- CABEÇALHO -->
            <div class="mb-7 flex flex-col xl:flex-row xl:items-end xl:justify-between gap-5">

                <div>
                    <h1 class="text-3xl sm:text-4xl font-extrabold text-[#003C2F] tracking-tight">
                        Biblioteca de Cursos
                    </h1>

                    <p class="text-sm text-[#60756B] mt-2 max-w-3xl">
                        Reutilize cursos antigos com todos os módulos, aulas e pós-testes já cadastrados.
                    </p>
                </div>

                <a href="{{ route('videoaulas') }}"
                   class="bg-white border border-[#DCE7DE] text-[#004D3A] px-5 py-3 rounded-2xl hover:bg-[#F8FBF8] transition flex items-center justify-center gap-2 text-sm font-extrabold shadow-sm">
                    Voltar para videoaulas
                </a>

            </div>

            <!-- BARRA DE PESQUISA -->
            <div class="bg-white border border-[#E3EBE4] rounded-3xl shadow-sm p-5 sm:p-6 mb-7">

                <div class="relative">
                    <span class="absolute inset-y-0 left-5 flex items-center text-[#8A9B92]">
                        <svg xmlns="http://www.w3.org/2000/svg"
                             class="w-6 h-6"
                             fill="none"
                             viewBox="0 0 24 24"
                             stroke="currentColor">
                            <path stroke-linecap="round"
                                  stroke-linejoin="round"
                                  stroke-width="1.8"
                                  d="m21 21-4.35-4.35m1.35-5.65a7 7 0 1 1-14 0 7 7 0 0 1 14 0z"/>
                        </svg>
                    </span>

                    <input type="text"
                           id="pesquisaCursos"
                           onkeyup="filtrarCursos()"
                           placeholder="Pesquisar curso antigo..."
                           class="w-full px-5 py-4 pl-14 rounded-2xl bg-[#F8FBF8] border border-[#DCE7DE] text-[#003C2F] text-sm font-bold placeholder-[#8A9B92] focus:outline-none focus:ring-2 focus:ring-[#00A63E] focus:border-transparent transition">
                </div>

            </div>

            <!-- LISTA -->
            <div class="grid grid-cols-1 lg:grid-cols-2 2xl:grid-cols-3 gap-6">

                @forelse($cursos as $curso)

                    @php
                        $totalModulos = DB::table('modulos')
                            ->where('curso_id', $curso->id)
                            ->count();

                        $totalAulas = DB::table('aulas')
                            ->where('curso_id', $curso->id)
                            ->count();

                        $totalPostestes = DB::table('avaliacoes')
                            ->join('aulas', 'avaliacoes.aula_id', '=', 'aulas.id')
                            ->where('aulas.curso_id', $curso->id)
                            ->count();

                        $modulosCurso = DB::table('modulos')
                            ->where('curso_id', $curso->id)
                            ->orderBy('ordem')
                            ->get();
                    @endphp

                    <div class="curso-item bg-white border border-[#E3EBE4] rounded-3xl shadow-sm overflow-hidden hover:shadow-lg transition"
                         data-search="{{ strtolower($curso->nome . ' ' . $curso->descricao) }}">

                        <div class="p-6 border-b border-[#E3EBE4]">

                            <div class="flex items-start justify-between gap-4">

                                <div class="min-w-0">
                                    <p class="text-[11px] uppercase tracking-widest text-[#60756B] font-extrabold">
                                        Curso #{{ $curso->id }}
                                    </p>

                                    <h2 class="text-2xl font-extrabold text-[#003C2F] mt-2 break-words">
                                        {{ $curso->nome }}
                                    </h2>

                                    <p class="text-sm text-[#60756B] mt-2 leading-relaxed break-words">
                                        {{ $curso->descricao ?? 'Sem descrição cadastrada.' }}
                                    </p>
                                </div>

                                <div class="w-14 h-14 rounded-2xl bg-[#EAF5EF] text-[#004D3A] flex items-center justify-center shrink-0">
                                    <svg xmlns="http://www.w3.org/2000/svg"
                                         class="w-7 h-7"
                                         fill="none"
                                         viewBox="0 0 24 24"
                                         stroke="currentColor">
                                        <path stroke-linecap="round"
                                              stroke-linejoin="round"
                                              stroke-width="1.8"
                                              d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25A8.966 8.966 0 0 1 18 3.75c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.966 8.966 0 0 0-6 2.292m0-14.25v14.25"/>
                                    </svg>
                                </div>

                            </div>

                            <div class="grid grid-cols-3 gap-3 mt-5">

                                <div class="bg-[#F8FBF8] border border-[#E3EBE4] rounded-2xl p-3 text-center">
                                    <p class="text-xl font-extrabold text-[#004D3A]">
                                        {{ $totalModulos }}
                                    </p>

                                    <p class="text-[10px] uppercase tracking-widest text-[#60756B] font-extrabold mt-1">
                                        Módulos
                                    </p>
                                </div>

                                <div class="bg-[#F8FBF8] border border-[#E3EBE4] rounded-2xl p-3 text-center">
                                    <p class="text-xl font-extrabold text-[#004D3A]">
                                        {{ $totalAulas }}
                                    </p>

                                    <p class="text-[10px] uppercase tracking-widest text-[#60756B] font-extrabold mt-1">
                                        Aulas
                                    </p>
                                </div>

                                <div class="bg-[#F8FBF8] border border-[#E3EBE4] rounded-2xl p-3 text-center">
                                    <p class="text-xl font-extrabold text-[#004D3A]">
                                        {{ $totalPostestes }}
                                    </p>

                                    <p class="text-[10px] uppercase tracking-widest text-[#60756B] font-extrabold mt-1">
                                        Testes
                                    </p>
                                </div>

                            </div>

                        </div>

                        <div class="p-5">

                            <button type="button"
                                    onclick="toggleDetalhesCurso({{ $curso->id }})"
                                    class="w-full bg-[#F8FBF8] border border-[#DCE7DE] text-[#004D3A] px-4 py-3 rounded-2xl font-extrabold hover:bg-[#EAF5EF] transition mb-3">
                                Ver estrutura
                            </button>

                            <div id="detalhesCurso{{ $curso->id }}" class="hidden mb-4 space-y-3">

                                @forelse($modulosCurso as $modulo)

                                    @php
                                        $aulasModulo = DB::table('aulas')
                                            ->where('modulo_id', $modulo->id)
                                            ->orderBy('id')
                                            ->get();
                                    @endphp

                                    <div class="bg-[#F8FBF8] border border-[#E3EBE4] rounded-2xl p-4">

                                        <h3 class="font-extrabold text-[#003C2F]">
                                            {{ $modulo->nome }}
                                        </h3>

                                        <p class="text-xs text-[#60756B] mt-1 mb-3">
                                            {{ $aulasModulo->count() }} aula(s)
                                        </p>

                                        <div class="space-y-2">

                                            @forelse($aulasModulo as $aula)
                                                <div class="bg-white border border-[#E3EBE4] rounded-xl p-3">
                                                    <p class="text-sm font-bold text-[#003C2F]">
                                                        {{ $aula->titulo }}
                                                    </p>

                                                    @php
                                                        $temTeste = DB::table('avaliacoes')
                                                            ->where('aula_id', $aula->id)
                                                            ->exists();
                                                    @endphp

                                                    <p class="text-[11px] mt-1 {{ $temTeste ? 'text-green-700' : 'text-[#8A9B92]' }} font-bold">
                                                        {{ $temTeste ? 'Com pós-teste' : 'Sem pós-teste' }}
                                                    </p>
                                                </div>
                                            @empty
                                                <p class="text-xs text-[#60756B]">
                                                    Nenhuma aula neste módulo.
                                                </p>
                                            @endforelse

                                        </div>

                                    </div>

                                @empty

                                    <div class="bg-[#F8FBF8] border border-[#E3EBE4] rounded-2xl p-4 text-sm text-[#60756B]">
                                        Nenhum módulo cadastrado neste curso.
                                    </div>

                                @endforelse

                            </div>

                            <form method="POST"
                                  action="{{ route('biblioteca.cursos.duplicar', $curso->id) }}">
                                @csrf

                                <button type="submit"
                                        class="w-full bg-[#004D3A] hover:bg-[#003C2F] text-white px-5 py-4 rounded-2xl font-extrabold transition shadow-sm flex items-center justify-center gap-2">

                                    <svg xmlns="http://www.w3.org/2000/svg"
                                         class="w-5 h-5"
                                         fill="none"
                                         viewBox="0 0 24 24"
                                         stroke="currentColor">
                                        <path stroke-linecap="round"
                                              stroke-linejoin="round"
                                              stroke-width="1.8"
                                              d="M16.023 9.348h4.992M2.985 19.644v-4.992m0 0h4.992m-4.992 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M7.977 14.652H2.985m18.03-9.296v4.992m0 0h-4.992m4.992 0-3.181-3.183a8.25 8.25 0 0 0-13.803 3.7"/>
                                    </svg>

                                    Usar este curso novamente
                                </button>
                            </form>

                        </div>

                    </div>

                @empty

                    <div class="lg:col-span-2 2xl:col-span-3 bg-white border border-[#E3EBE4] rounded-3xl p-10 text-center">

                        <div class="w-20 h-20 mx-auto rounded-full bg-[#EAF5EF] text-[#004D3A] flex items-center justify-center mb-5">
                            <svg xmlns="http://www.w3.org/2000/svg"
                                 class="w-10 h-10"
                                 fill="none"
                                 viewBox="0 0 24 24"
                                 stroke="currentColor">
                                <path stroke-linecap="round"
                                      stroke-linejoin="round"
                                      stroke-width="1.8"
                                      d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25A8.966 8.966 0 0 1 18 3.75c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.966 8.966 0 0 0-6 2.292m0-14.25v14.25"/>
                            </svg>
                        </div>

                        <h3 class="text-xl font-extrabold text-[#003C2F]">
                            Nenhum curso na biblioteca
                        </h3>

                        <p class="text-sm text-[#60756B] mt-2">
                            Cadastre seu primeiro curso na tela de videoaulas.
                        </p>

                    </div>

                @endforelse

            </div>

        </section>

    </main>

</div>

<script>
    function toggleDetalhesCurso(id) {
        const detalhes = document.getElementById('detalhesCurso' + id);

        if (detalhes) {
            detalhes.classList.toggle('hidden');
        }
    }

    function filtrarCursos() {
        const input = document.getElementById('pesquisaCursos');
        const termo = input ? input.value.toLowerCase().trim() : '';
        const cursos = document.querySelectorAll('.curso-item');

        cursos.forEach((curso) => {
            const texto = curso.dataset.search || '';

            if (texto.includes(termo)) {
                curso.classList.remove('hidden');
            } else {
                curso.classList.add('hidden');
            }
        });
    }
</script>

@endsection