<nav class="bg-white border-b border-gray-200 px-6 py-3 flex justify-end items-center shadow-sm">

    <div class="flex items-center gap-3">

        <!-- NOME -->
        <span class="text-gray-700 font-medium">
            {{ auth()->user()->name }}
        </span>

        <!-- AVATAR (INICIAIS) -->
        <div class="w-10 h-10 rounded-full bg-green-600 flex items-center justify-center text-white font-bold">
            {{ strtoupper(substr(auth()->user()->name, 0, 1) . substr(strstr(auth()->user()->name, ' '), 1, 1)) }}
        </div>

    </div>

</nav>