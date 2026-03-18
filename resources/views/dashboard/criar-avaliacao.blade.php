@extends('layout.app')

@section('content')

<div class="p-8 bg-[#0B1120] text-white min-h-screen">

    <h2 class="text-2xl mb-6">Criar Pós-Teste</h2>

    <form action="{{ route('avaliacoes.store') }}" method="POST">
        @csrf

        <input type="hidden" name="aula_id" value="{{ is_object($aula) ? $aula->id : $aula }}">

        <!-- TÍTULO -->
        <input type="text" name="titulo" placeholder="Título do teste"
            class="w-full p-3 mb-4 bg-[#1E293B] rounded">

        <!-- TEMPO -->
        <input type="number" name="tempo_limite" placeholder="Tempo (minutos)"
            class="w-full p-3 mb-4 bg-[#1E293B] rounded">

        <!-- PERGUNTAS -->
        <div id="perguntas-container">

            <!-- PERGUNTA 1 -->
            <div class="pergunta-bloco mb-6 border border-gray-600 p-4 rounded relative">

                <!-- BOTÃO REMOVER -->
                <button type="button" onclick="removerPergunta(this)" 
                    class="absolute top-2 right-2 bg-red-600 px-2 py-1 rounded text-sm">
                    🗑️
                </button>

                <input type="text" name="perguntas[0][pergunta]" placeholder="Pergunta"
                    class="w-full p-3 mb-4 bg-[#1E293B] rounded">

                <!-- RESPOSTAS -->
                <input type="text" name="perguntas[0][respostas][1]" placeholder="Resposta 1"
                    class="w-full p-2 mb-2 bg-[#1E293B] rounded">

                <input type="text" name="perguntas[0][respostas][2]" placeholder="Resposta 2"
                    class="w-full p-2 mb-2 bg-[#1E293B] rounded">

                <input type="text" name="perguntas[0][respostas][3]" placeholder="Resposta 3"
                    class="w-full p-2 mb-2 bg-[#1E293B] rounded">

                <input type="text" name="perguntas[0][respostas][4]" placeholder="Resposta 4"
                    class="w-full p-2 mb-4 bg-[#1E293B] rounded">

                <!-- CORRETAS -->
                <label class="block mb-2">Respostas corretas:</label>

                <label><input type="checkbox" name="perguntas[0][corretas][]" value="1"> 1</label>
                <label><input type="checkbox" name="perguntas[0][corretas][]" value="2"> 2</label>
                <label><input type="checkbox" name="perguntas[0][corretas][]" value="3"> 3</label>
                <label><input type="checkbox" name="perguntas[0][corretas][]" value="4"> 4</label>

            </div>

        </div>

        <!-- BOTÕES -->
        <div class="flex gap-4">
            <button type="button" onclick="adicionarPergunta()" class="bg-blue-600 px-4 py-2 rounded">
                + Adicionar Pergunta
            </button>

            <button class="bg-green-600 px-4 py-2 rounded">
                Salvar Teste
            </button>
        </div>

    </form>

</div>

<!-- SWEETALERT -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- SCRIPT -->
<script>
let contador = 1;

function adicionarPergunta() {
    let container = document.getElementById('perguntas-container');

    let bloco = `
    <div class="pergunta-bloco mb-6 border border-gray-600 p-4 rounded relative">

        <button type="button" onclick="removerPergunta(this)" 
            class="absolute top-2 right-2 bg-red-600 px-2 py-1 rounded text-sm">
            🗑️
        </button>

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

        <label class="block mb-2">Respostas corretas:</label>

        <label><input type="checkbox" name="perguntas[${contador}][corretas][]" value="1"> 1</label>
        <label><input type="checkbox" name="perguntas[${contador}][corretas][]" value="2"> 2</label>
        <label><input type="checkbox" name="perguntas[${contador}][corretas][]" value="3"> 3</label>
        <label><input type="checkbox" name="perguntas[${contador}][corretas][]" value="4"> 4</label>

    </div>
    `;

    container.insertAdjacentHTML('beforeend', bloco);
    contador++;
}

function removerPergunta(botao) {
    let container = document.getElementById('perguntas-container');
    let perguntas = container.querySelectorAll('.pergunta-bloco');

    if (perguntas.length <= 1) {
        Swal.fire({
            icon: 'warning',
            title: 'Atenção',
            text: 'Você precisa ter pelo menos uma pergunta.',
            confirmButtonColor: '#2563eb'
        });
        return;
    }

    Swal.fire({
        title: 'Tem certeza?',
        text: "Essa pergunta será removida!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#2563eb',
        confirmButtonText: 'Sim, excluir',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            botao.closest('.pergunta-bloco').remove();

            Swal.fire({
                icon: 'success',
                title: 'Removido!',
                text: 'A pergunta foi excluída.',
                timer: 1500,
                showConfirmButton: false
            });
        }
    });
}
</script>

@endsection