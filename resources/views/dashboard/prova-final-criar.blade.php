@extends('layout.app')

@section('title', 'Criar Prova Final')

@section('content')

<div class="flex min-h-screen">

    @include('partials.sidebar-professor')

    <main class="flex-1 p-8 bg-[#0B1120] text-white">

        <!-- HEADER -->
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold">Criar Prova Final</h2>
        </div>

        <!-- ALERTA -->
        @if(session('success'))
            <div class="bg-green-500/20 text-green-400 p-3 mb-4 rounded border border-green-500">
                {{ session('success') }}
            </div>
        @endif

        <!-- FORM -->
        <form action="{{ route('prova.final.store') }}" method="POST">
            @csrf

            <!-- DADOS DA PROVA -->
            <div class="bg-[#1E293B] p-6 rounded-xl mb-6">

                <h3 class="font-semibold mb-4">Dados da Prova</h3>

                <input type="text" name="titulo" placeholder="Título da prova"
                    class="w-full p-3 mb-3 bg-[#0F172A] rounded">

                <input type="number" name="tempo_limite" placeholder="Tempo (minutos)"
                    class="w-full p-3 mb-3 bg-[#0F172A] rounded">

            </div>

            <!-- PERGUNTAS -->
            <div id="perguntas-container">

                <!-- PRIMEIRA PERGUNTA -->
                <div class="pergunta-bloco bg-[#1E293B] p-6 rounded-xl mb-4 relative">

                    <button type="button" onclick="removerPergunta(this)"
                        class="absolute top-3 right-3 bg-red-600 px-2 py-1 text-xs rounded">
                        X
                    </button>

                    <input type="text" name="perguntas[0][pergunta]" placeholder="Pergunta"
                        class="w-full p-3 mb-3 bg-[#0F172A] rounded">

                    <!-- RESPOSTAS -->
                    <input type="text" name="perguntas[0][respostas][1]" placeholder="Resposta 1"
                        class="w-full p-2 mb-2 bg-[#0F172A] rounded">

                    <input type="text" name="perguntas[0][respostas][2]" placeholder="Resposta 2"
                        class="w-full p-2 mb-2 bg-[#0F172A] rounded">

                    <input type="text" name="perguntas[0][respostas][3]" placeholder="Resposta 3"
                        class="w-full p-2 mb-2 bg-[#0F172A] rounded">

                    <input type="text" name="perguntas[0][respostas][4]" placeholder="Resposta 4"
                        class="w-full p-2 mb-2 bg-[#0F172A] rounded">

                    <!-- CORRETA -->
                    <label class="block mt-2">Resposta correta:</label>

                    <label><input type="radio" name="perguntas[0][correta]" value="1"> 1</label>
                    <label><input type="radio" name="perguntas[0][correta]" value="2"> 2</label>
                    <label><input type="radio" name="perguntas[0][correta]" value="3"> 3</label>
                    <label><input type="radio" name="perguntas[0][correta]" value="4"> 4</label>

                </div>

            </div>

            <!-- BOTÕES -->
            <div class="flex justify-between mt-6">

                <button type="button" onclick="addPergunta()"
                    class="bg-blue-600 px-4 py-2 rounded hover:bg-blue-700">
                    + Nova Pergunta
                </button>

                <button class="bg-green-600 px-6 py-2 rounded hover:bg-green-700">
                    Salvar Prova
                </button>

            </div>

        </form>

    </main>

</div>

<script>

let contador = 1;

function addPergunta() {

    let container = document.getElementById('perguntas-container');

    let bloco = `
    <div class="pergunta-bloco bg-[#1E293B] p-6 rounded-xl mb-4 relative">

        <button type="button" onclick="removerPergunta(this)"
            class="absolute top-3 right-3 bg-red-600 px-2 py-1 text-xs rounded">
            X
        </button>

        <input type="text" name="perguntas[${contador}][pergunta]"
            class="w-full p-3 mb-3 bg-[#0F172A] rounded">

        <input type="text" name="perguntas[${contador}][respostas][1]" class="w-full p-2 mb-2 bg-[#0F172A] rounded">
        <input type="text" name="perguntas[${contador}][respostas][2]" class="w-full p-2 mb-2 bg-[#0F172A] rounded">
        <input type="text" name="perguntas[${contador}][respostas][3]" class="w-full p-2 mb-2 bg-[#0F172A] rounded">
        <input type="text" name="perguntas[${contador}][respostas][4]" class="w-full p-2 mb-2 bg-[#0F172A] rounded">

        <label class="block mt-2">Resposta correta:</label>

        <label><input type="radio" name="perguntas[${contador}][correta]" value="1"> 1</label>
        <label><input type="radio" name="perguntas[${contador}][correta]" value="2"> 2</label>
        <label><input type="radio" name="perguntas[${contador}][correta]" value="3"> 3</label>
        <label><input type="radio" name="perguntas[${contador}][correta]" value="4"> 4</label>

    </div>`;

    container.insertAdjacentHTML('beforeend', bloco);

    contador++;
}

function removerPergunta(btn) {
    btn.parentElement.remove();
}

</script>

@endsection