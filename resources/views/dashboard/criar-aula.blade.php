@extends('layout.app')

@section('title', 'Criar Aula Completa')

@section('content')

<div class="p-8 bg-[#0B1120] text-white min-h-screen">

    <h2 class="text-2xl mb-6">Criar Aula Completa</h2>

    <form action="{{ route('aulas.store') }}" method="POST">
        @csrf

        <!-- DADOS DA AULA -->
        <h3 class="text-xl mb-4">📚 Dados da Aula</h3>

        <input type="text" name="titulo" placeholder="Título da aula"
            class="w-full p-3 mb-4 bg-[#1E293B] rounded">

        <textarea name="descricao" placeholder="Descrição"
            class="w-full p-3 mb-4 bg-[#1E293B] rounded"></textarea>

        <input type="text" name="video_url" placeholder="Link do vídeo (YouTube)"
            class="w-full p-3 mb-6 bg-[#1E293B] rounded">

        <!-- DADOS DO TESTE -->
        <h3 class="text-xl mb-4">🧠 Pós-Teste</h3>

        <input type="text" name="avaliacao[titulo]" placeholder="Título do teste"
            class="w-full p-3 mb-4 bg-[#1E293B] rounded">

        <input type="number" name="avaliacao[tempo_limite]" placeholder="Tempo (minutos)"
            class="w-full p-3 mb-6 bg-[#1E293B] rounded">

        <!-- PERGUNTAS -->
        <div id="perguntas-container">

            <div class="pergunta-bloco mb-6 border border-gray-600 p-4 rounded">

                <input type="text" name="perguntas[0][pergunta]" placeholder="Pergunta"
                    class="w-full p-3 mb-4 bg-[#1E293B] rounded">

                <input type="text" name="perguntas[0][respostas][1]" placeholder="Resposta 1"
                    class="w-full p-2 mb-2 bg-[#1E293B] rounded">

                <input type="text" name="perguntas[0][respostas][2]" placeholder="Resposta 2"
                    class="w-full p-2 mb-2 bg-[#1E293B] rounded">

                <input type="text" name="perguntas[0][respostas][3]" placeholder="Resposta 3"
                    class="w-full p-2 mb-2 bg-[#1E293B] rounded">

                <input type="text" name="perguntas[0][respostas][4]" placeholder="Resposta 4"
                    class="w-full p-2 mb-4 bg-[#1E293B] rounded">

                <label class="block mb-2 mt-3">Resposta correta:</label>

                <label><input type="radio" name="perguntas[0][correta]" value="1"> 1</label>
                <label><input type="radio" name="perguntas[0][correta]" value="2"> 2</label>
                <label><input type="radio" name="perguntas[0][correta]" value="3"> 3</label>
                <label><input type="radio" name="perguntas[0][correta]" value="4"> 4</label>

            </div>

        </div>

        <!-- BOTÕES -->
        <div class="flex gap-4">
            <button type="button" onclick="addPergunta()" class="bg-blue-600 px-4 py-2 rounded">
                + Pergunta
            </button>

            <button class="bg-green-600 px-4 py-2 rounded hover:bg-green-700">
                Salvar Aula Completa
            </button>
        </div>

    </form>

</div>

<!-- SWEET ALERT -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

@if(session('success'))
<script>
Swal.fire({
    icon: 'success',
    title: 'Sucesso!',
    text: '{{ session('success') }}',
    confirmButtonColor: '#16a34a'
});
</script>
@endif

@if(session('error'))
<script>
Swal.fire({
    icon: 'error',
    title: 'Erro!',
    text: '{{ session('error') }}',
    confirmButtonColor: '#dc2626'
});
</script>
@endif

<!-- SCRIPT PERGUNTAS -->
<script>
let contador = 1;

function addPergunta() {

    let container = document.getElementById('perguntas-container');

    let bloco = `
    <div class="pergunta-bloco mb-6 border border-gray-600 p-4 rounded">

        <input type="text" name="perguntas[${contador}][pergunta]" placeholder="Pergunta"
            class="w-full p-3 mb-4 bg-[#1E293B] rounded">

        <input type="text" name="perguntas[${contador}][respostas][1]" placeholder="Resposta 1"
            class="w-full p-2 mb-2 bg-[#1E293B] rounded">

        <input type="text" name="perguntas[${contador}][respostas][2]" placeholder="Resposta 2"
            class="w-full p-2 mb-2 bg-[#1E293B] rounded">

        <input type="text" name="perguntas[${contador}][respostas][3]" placeholder="Resposta 3"
            class="w-full p-2 mb-2 bg-[#1E293B] rounded">

        <input type="text" name="perguntas[${contador}][respostas][4]" placeholder="Resposta 4"
            class="w-full p-2 mb-4 bg-[#1E293B] rounded">

        <label class="block mb-2">Resposta correta:</label>

        <label><input type="radio" name="perguntas[${contador}][correta]" value="1"> 1</label>
        <label><input type="radio" name="perguntas[${contador}][correta]" value="2"> 2</label>
        <label><input type="radio" name="perguntas[${contador}][correta]" value="3"> 3</label>
        <label><input type="radio" name="perguntas[${contador}][correta]" value="4"> 4</label>

    </div>
    `;

    container.insertAdjacentHTML('beforeend', bloco);
    contador++;
}
</script>

@endsection