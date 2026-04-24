@extends('layout.app')

@section('title', 'Criar Certificado')

@section('content')

<div class="flex min-h-screen">

    @include('partials.sidebar-professor')

    <main class="flex-1 p-8 bg-[#0B1120] text-white">

        <!-- HEADER -->
        <h2 class="text-2xl font-bold mb-6">
            🎓 Criar Modelo de Certificado
        </h2>

        <div class="bg-[#1E293B] p-6 rounded-xl shadow">

            <form action="{{ route('certificados.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <!-- NOME DO CURSO -->
                <input type="text" name="curso"
                    placeholder="Nome do Curso"
                    class="w-full p-3 mb-4 bg-[#0F172A] rounded">

                <!-- CARGA HORÁRIA -->
                <input type="number" name="carga_horaria"
                    placeholder="Carga Horária (ex: 40)"
                    class="w-full p-3 mb-4 bg-[#0F172A] rounded">

                <!-- NOME DO RESPONSÁVEL -->
                <input type="text" name="responsavel"
                    placeholder="Nome do Responsável"
                    class="w-full p-3 mb-4 bg-[#0F172A] rounded">

                <!-- CARGO -->
                <input type="text" name="cargo"
                    placeholder="Cargo (Ex: Coordenador)"
                    class="w-full p-3 mb-4 bg-[#0F172A] rounded">

                <!-- ASSINATURA -->
                <label class="block mb-2 text-sm text-gray-400">
                    Assinatura (imagem PNG)
                </label>

                <input type="file" name="assinatura"
                    class="w-full p-3 mb-6 bg-[#0F172A] rounded">

                <!-- BOTÃO -->
                <button class="bg-green-600 px-6 py-3 rounded hover:bg-green-700 transition">
                    💾 Salvar Certificado
                </button>

            </form>

        </div>

    </main>

</div>

@endsection