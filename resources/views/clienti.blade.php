<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Clienti</h2>
    </x-slot>

    <div class="p-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
            <div>
                <h3 class="text-lg font-semibold">Elenco clienti</h3>
                <p class="text-sm text-gray-500 mt-1">Gestisci anagrafiche e contatti dei clienti.</p>
            </div>
            <a href="{{ route('clienti.create') }}" class="bg-green-600 text-white px-4 py-2 rounded whitespace-nowrap">Nuovo cliente</a>
        </div>

        <form method="GET" class="mb-4" onsubmit="return false;">
            <label for="ricerca-clienti" class="sr-only">Cerca cliente</label>
            <input
                type="search"
                id="ricerca-clienti"
                name="ricerca"
                value="{{ $ricerca ?? '' }}"
                placeholder="Cerca per nome, cognome o email"
                autocomplete="off"
                class="border rounded px-3 py-2 w-full md:w-96">
        </form>

        <div id="clienti-table-container">
            @include('clienti._table')
        </div>
    </div>
</x-app-layout>

<script>
document.addEventListener('DOMContentLoaded', function () {
    let timer;
    const ricerca = document.getElementById('ricerca-clienti');
    const tabella = document.getElementById('clienti-table-container');

    ricerca.addEventListener('input', function () {
        clearTimeout(timer);

        timer = setTimeout(async () => {
            const response = await fetch(
                `/clienti/search?q=${encodeURIComponent(ricerca.value)}`
            );

            if (response.ok) {
                tabella.innerHTML = await response.text();
            }
        }, 400);
    });
});
</script>
