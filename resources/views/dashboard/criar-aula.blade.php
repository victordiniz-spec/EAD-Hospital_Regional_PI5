@extends('layout.app')

@section('title', 'Criar Aula Completa')

@section('content')

<div class="p-8 bg-[#0B1120] text-white min-h-screen">

    <h2 class="text-2xl mb-6">Criar Aula Completa</h2>

    @if($errors->any())
        <div class="bg-red-950 border border-red-600 rounded p-4 mb-6" role="alert">
            <p class="font-bold mb-2">Confira os dados informados:</p>
            <ul class="list-disc pl-5">
                @foreach($errors->all() as $erro)
                    <li>{{ $erro }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('aulas.store') }}" method="POST" id="formCriarAula">
        @csrf

        <!-- CURSO E MÓDULO: campos exigidos pelo AulaController -->
        <h3 class="text-xl mb-4">🎓 Curso e Módulo</h3>

        <label for="curso_id" class="block mb-2">Curso existente</label>
        <select id="curso_id" name="curso_id" class="w-full p-3 mb-4 bg-[#1E293B] rounded border border-gray-600">
            <option value="">Selecione um curso ou crie um novo abaixo</option>
            @foreach($cursos as $curso)
                <option value="{{ $curso->id }}" @selected(old('curso_id') == $curso->id)>{{ $curso->nome }}</option>
            @endforeach
        </select>

        <label for="novo_curso" class="block mb-2">Ou criar um novo curso</label>
        <input type="text" id="novo_curso" name="novo_curso" value="{{ old('novo_curso') }}"
               placeholder="Nome do novo curso" maxlength="255"
               class="w-full p-3 mb-4 bg-[#1E293B] rounded border border-gray-600">

        <textarea id="descricao_curso" name="descricao_curso" maxlength="2000"
                  placeholder="Descrição do novo curso (opcional)"
                  class="w-full p-3 mb-4 bg-[#1E293B] rounded border border-gray-600">{{ old('descricao_curso') }}</textarea>

        <!-- Só tem efeito na criação de um novo curso; usa o campo preparado no controller. -->
        <div id="opcaoObrigatoriedade" class="mb-6 p-4 rounded border border-gray-600 bg-[#111827]">
            <label for="curso_obrigatorio" class="block mb-2 font-semibold">Curso obrigatório para certificação?</label>
            <select name="curso_obrigatorio" id="curso_obrigatorio"
                    class="w-full p-3 bg-[#1E293B] rounded border border-gray-600">
                <option value="1" @selected(old('curso_obrigatorio', '1') == '1')>Sim — entra na média geral</option>
                <option value="0" @selected(old('curso_obrigatorio') === '0')>Não — curso complementar</option>
            </select>
            <p class="text-sm text-gray-300 mt-2">Esta opção é aplicada somente ao cadastrar um novo curso e requer a migration do campo <code>obrigatorio</code>. Para modificar um curso já cadastrado, precisaremos de uma função de edição específica.</p>
        </div>

        <label for="modulo_id" class="block mb-2">Módulo existente</label>
        <select id="modulo_id" name="modulo_id" class="w-full p-3 mb-4 bg-[#1E293B] rounded border border-gray-600">
            <option value="">Selecione um módulo ou crie um novo abaixo</option>
            @foreach($modulos as $modulo)
                <option value="{{ $modulo->id }}" data-curso-id="{{ $modulo->curso_id }}" @selected(old('modulo_id') == $modulo->id)>{{ $modulo->nome }}</option>
            @endforeach
        </select>

        <label for="novo_modulo" class="block mb-2">Ou criar um novo módulo</label>
        <input type="text" id="novo_modulo" name="novo_modulo" value="{{ old('novo_modulo') }}"
               placeholder="Nome do novo módulo" maxlength="255"
               class="w-full p-3 mb-6 bg-[#1E293B] rounded border border-gray-600">

        <!-- DADOS DA AULA -->
        <h3 class="text-xl mb-4">📚 Dados da Aula</h3>

        <input type="text" name="titulo" value="{{ old('titulo') }}" placeholder="Título da aula" maxlength="255" required
            class="w-full p-3 mb-4 bg-[#1E293B] rounded">

        <textarea name="descricao" placeholder="Descrição" maxlength="5000" required
            class="w-full p-3 mb-4 bg-[#1E293B] rounded">{{ old('descricao') }}</textarea>

        <input type="text" name="video_url" value="{{ old('video_url') }}" placeholder="Link do vídeo (YouTube)" maxlength="1000" required
            class="w-full p-3 mb-4 bg-[#1E293B] rounded">

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
            <div>
                <label for="tempo_minimo_video" class="block mb-2">Tempo mínimo do vídeo (minutos)</label>
                <input id="tempo_minimo_video" type="number" name="tempo_minimo_video" value="{{ old('tempo_minimo_video', 0) }}" min="0"
                       class="w-full p-3 bg-[#1E293B] rounded">
            </div>
            <div>
                <label for="tempo_maximo_video" class="block mb-2">Tempo máximo do vídeo (minutos, opcional)</label>
                <input id="tempo_maximo_video" type="number" name="tempo_maximo_video" value="{{ old('tempo_maximo_video') }}" min="0"
                       class="w-full p-3 bg-[#1E293B] rounded">
            </div>
        </div>

        <!-- DADOS DO TESTE -->
        <h3 class="text-xl mb-4">🧠 Pós-Teste</h3>

        <input type="text" name="avaliacao[titulo]" value="{{ old('avaliacao.titulo') }}" placeholder="Título do teste" maxlength="255" required
            class="w-full p-3 mb-4 bg-[#1E293B] rounded">

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
            <div>
                <label for="tempo_minimo_teste" class="block mb-2">Tempo mínimo do pós-teste (minutos)</label>
                <input id="tempo_minimo_teste" type="number" name="avaliacao[tempo_minimo]" value="{{ old('avaliacao.tempo_minimo', 0) }}" min="0"
                       class="w-full p-3 bg-[#1E293B] rounded">
            </div>
            <div>
                <label for="tempo_limite_teste" class="block mb-2">Tempo máximo do pós-teste (minutos)</label>
                <input id="tempo_limite_teste" type="number" name="avaliacao[tempo_limite]" value="{{ old('avaliacao.tempo_limite') }}" min="1" required
                       class="w-full p-3 bg-[#1E293B] rounded">
            </div>
        </div>

        <!-- PERGUNTAS -->
        <div id="perguntas-container">
            @php
                $perguntasAnteriores = old('perguntas', [0 => ['pergunta' => '', 'respostas' => [], 'correta' => null]]);
            @endphp
            @foreach($perguntasAnteriores as $indice => $perguntaAntiga)
                <div class="pergunta-bloco mb-6 border border-gray-600 p-4 rounded">
                    <input type="text" name="perguntas[{{ $indice }}][pergunta]" value="{{ $perguntaAntiga['pergunta'] ?? '' }}" placeholder="Pergunta" required
                        class="w-full p-3 mb-4 bg-[#1E293B] rounded">

                    @for($alternativa = 1; $alternativa <= 4; $alternativa++)
                        <input type="text" name="perguntas[{{ $indice }}][respostas][{{ $alternativa }}]" value="{{ $perguntaAntiga['respostas'][$alternativa] ?? '' }}" placeholder="Resposta {{ $alternativa }}"
                            class="w-full p-2 mb-2 bg-[#1E293B] rounded" @if($alternativa <= 2) required @endif>
                    @endfor

                    <label class="block mb-2 mt-3">Resposta correta:</label>
                    @for($alternativa = 1; $alternativa <= 4; $alternativa++)
                        <label class="inline-flex items-center gap-1 mr-3 mb-2">
                            <input type="radio" name="perguntas[{{ $indice }}][correta]" value="{{ $alternativa }}"
                                @checked((string) ($perguntaAntiga['correta'] ?? '') === (string) $alternativa) required>
                            {{ $alternativa }}
                        </label>
                    @endfor
                </div>
            @endforeach
        </div>

        <!-- BOTÕES -->
        <div class="flex flex-wrap gap-4">
            <button type="button" onclick="addPergunta()" class="bg-blue-600 px-4 py-2 rounded hover:bg-blue-700">
                + Pergunta
            </button>

            <button type="submit" class="bg-green-600 px-4 py-2 rounded hover:bg-green-700">
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
    text: @json(session('success')),
    confirmButtonColor: '#16a34a'
});
</script>
@endif

@if(session('error'))
<script>
Swal.fire({
    icon: 'error',
    title: 'Erro!',
    text: @json(session('error')),
    confirmButtonColor: '#dc2626'
});
</script>
@endif

<!-- SCRIPT PERGUNTAS E CURSOS -->
<script>
let contador = {{ collect($perguntasAnteriores)->keys()->map(fn ($indice) => (int) $indice)->max() + 1 }};

function addPergunta() {
    const container = document.getElementById('perguntas-container');
    const bloco = `
    <div class="pergunta-bloco mb-6 border border-gray-600 p-4 rounded">
        <input type="text" name="perguntas[${contador}][pergunta]" placeholder="Pergunta" required class="w-full p-3 mb-4 bg-[#1E293B] rounded">
        <input type="text" name="perguntas[${contador}][respostas][1]" placeholder="Resposta 1" required class="w-full p-2 mb-2 bg-[#1E293B] rounded">
        <input type="text" name="perguntas[${contador}][respostas][2]" placeholder="Resposta 2" required class="w-full p-2 mb-2 bg-[#1E293B] rounded">
        <input type="text" name="perguntas[${contador}][respostas][3]" placeholder="Resposta 3" class="w-full p-2 mb-2 bg-[#1E293B] rounded">
        <input type="text" name="perguntas[${contador}][respostas][4]" placeholder="Resposta 4" class="w-full p-2 mb-4 bg-[#1E293B] rounded">
        <label class="block mb-2">Resposta correta:</label>
        <label class="inline-flex gap-1 mr-3"><input type="radio" name="perguntas[${contador}][correta]" value="1" required> 1</label>
        <label class="inline-flex gap-1 mr-3"><input type="radio" name="perguntas[${contador}][correta]" value="2" required> 2</label>
        <label class="inline-flex gap-1 mr-3"><input type="radio" name="perguntas[${contador}][correta]" value="3" required> 3</label>
        <label class="inline-flex gap-1 mr-3"><input type="radio" name="perguntas[${contador}][correta]" value="4" required> 4</label>
    </div>`;
    container.insertAdjacentHTML('beforeend', bloco);
    contador++;
}

(function () {
    const curso = document.getElementById('curso_id');
    const novoCurso = document.getElementById('novo_curso');
    const modulo = document.getElementById('modulo_id');
    const novoModulo = document.getElementById('novo_modulo');
    const boxObrigatorio = document.getElementById('opcaoObrigatoriedade');
    const form = document.getElementById('formCriarAula');

    function atualizarFormulario() {
        const criandoCurso = novoCurso.value.trim() !== '';
        boxObrigatorio.classList.toggle('hidden', !criandoCurso);
        for (const opcao of modulo.options) {
            if (!opcao.value) continue;
            const pertenceCurso = !criandoCurso && curso.value && opcao.dataset.cursoId === curso.value;
            opcao.hidden = !pertenceCurso;
            opcao.disabled = !pertenceCurso;
        }
        if (modulo.selectedOptions.length && modulo.selectedOptions[0].disabled) modulo.value = '';
    }

    curso.addEventListener('change', atualizarFormulario);
    novoCurso.addEventListener('input', atualizarFormulario);
    atualizarFormulario();

    form.addEventListener('submit', function (evento) {
        if (!novoCurso.value.trim() && !curso.value) {
            evento.preventDefault();
            Swal.fire({icon: 'warning', title: 'Selecione ou crie um curso', confirmButtonColor: '#16a34a'});
            return;
        }
        if (!novoModulo.value.trim() && !modulo.value) {
            evento.preventDefault();
            Swal.fire({icon: 'warning', title: 'Selecione ou crie um módulo', confirmButtonColor: '#16a34a'});
        }
    });
})();
</script>

@endsection
