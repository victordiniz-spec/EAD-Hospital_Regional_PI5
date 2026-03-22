@extends('layout.app')

@section('title', 'Alunos')

@section('content')

<div class="flex min-h-screen bg-slate-950 text-white">

    <!-- SIDEBAR -->
    <aside class="w-64 bg-slate-900 p-6 flex flex-col justify-between">
        <div>
            <h1 class="text-xl font-bold mb-8">ResidentEAD</h1>

            <nav class="space-y-4">
                <a href="{{ route('dashboard.professor') }}" class="block hover:text-blue-400">Dashboard</a>
                <a href="{{ route('videoaulas') }}" class="block hover:text-blue-400">Videoaulas</a>
                <a href="{{ route('postestes') }}" class="block hover:text-blue-400">Pós-testes</a>
                <a href="{{ route('alunos') }}" class="block bg-blue-600 p-2 rounded">Alunos</a>
            </nav>
        </div>

        <!-- PERFIL -->
        <div class="text-center">
            <img 
                src="{{ auth()->user()->foto ? asset('storage/' . auth()->user()->foto) : asset('images/usuario-padrao.png') }}" 
                class="w-16 h-16 mx-auto rounded-full mb-2"
            >
            <p>{{ auth()->user()->name }}</p>
        </div>
    </aside>

    <!-- CONTEÚDO -->
    <main class="flex-1 p-8">

        <h2 class="text-2xl font-bold mb-6">Alunos</h2>

        <div class="space-y-3">

            @foreach($alunos as $aluno)
            <div class="bg-slate-900 p-4 rounded-xl flex justify-between items-center">

                <div class="flex items-center gap-3">
                    <img 
                        src="{{ $aluno->foto ? asset('storage/' . $aluno->foto) : asset('images/usuario-padrao.png') }}"
                        class="w-10 h-10 rounded-full"
                    >
                    <span>{{ $aluno->name }}</span>
                </div>

                <button onclick="abrirAluno({{ json_encode($aluno) }})"
                    class="bg-blue-600 px-3 py-1 rounded">
                    Ver
                </button>

            </div>
            @endforeach

        </div>

    </main>

</div>

<!-- MODAL -->
<div id="modalAluno" class="fixed inset-0 bg-black bg-opacity-70 backdrop-blur-sm hidden items-center justify-center z-50">

    <div class="bg-slate-900 p-6 rounded-xl w-[400px] relative">

        <button onclick="fecharAluno()" class="absolute top-2 right-3 text-xl">×</button>

        <div class="text-center">
            <img id="fotoAluno" class="w-24 h-24 rounded-full mx-auto mb-3">

            <h2 id="nomeAluno" class="text-xl font-bold"></h2>

            <p class="mt-3">📚 Assistiu aula: <span id="assistido"></span></p>
            <p>📝 Nota: <span id="nota"></span></p>
        </div>

    </div>

</div>

<script>
function abrirAluno(aluno) {
    document.getElementById('modalAluno').classList.remove('hidden');
    document.getElementById('modalAluno').classList.add('flex');

    document.getElementById('nomeAluno').innerText = aluno.name;

    document.getElementById('fotoAluno').src =
        aluno.foto ? '/storage/' + aluno.foto : '/images/usuario-padrao.png';

    document.getElementById('assistido').innerText =
        aluno.assistido == 1 ? '✔ Sim' : '❌ Não';

    document.getElementById('nota').innerText =
        aluno.nota ? aluno.nota : 'Sem nota';
}

function fecharAluno() {
    document.getElementById('modalAluno').classList.add('hidden');
}
</script>

@endsection