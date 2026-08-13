<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Viaggi</h2>
    </x-slot>

    <div class="p-6">
        @if (session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif

        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
            <div>
                <h3 class="text-lg font-semibold">Elenco viaggi</h3>
                <p class="text-sm text-gray-500 mt-1">Gestisci programmi, disponibilità e locandine.</p>
            </div>
            <div class="flex gap-3">
                <a href="{{ route('calendario') }}" class="rounded border px-4 py-2 text-gray-700 whitespace-nowrap">Calendario</a>
                <a href="{{ route('viaggi.create') }}" class="bg-green-600 text-white px-4 py-2 rounded whitespace-nowrap">Nuovo viaggio</a>
            </div>
        </div>

        <form method="GET" class="mb-4 flex flex-col gap-3 md:flex-row md:items-center" onsubmit="return false;">
            <label for="ricerca-viaggi" class="sr-only">Cerca viaggio</label>
            <input
                type="search"
                id="ricerca-viaggi"
                name="ricerca"
                value="{{ $ricerca ?? '' }}"
                placeholder="Cerca per nome, destinazione o tipologia"
                autocomplete="off"
                class="border rounded px-3 py-2 w-full md:w-96">
            <label class="flex items-center gap-2 text-sm text-gray-700">
                <input id="mostra-passati" type="checkbox" name="mostra_passati" value="1" @checked($mostraPassati ?? false) class="rounded border-gray-300 text-blue-600">
                Mostra viaggi passati
            </label>
        </form>

        <div id="viaggi-table-container">
            @include('viaggi._table')
        </div>
    </div>
</x-app-layout>

<script>
document.addEventListener('DOMContentLoaded', function () {
    let timer;
    const ricerca = document.getElementById('ricerca-viaggi');
    const mostraPassati = document.getElementById('mostra-passati');
    const tabella = document.getElementById('viaggi-table-container');

    function aggiornaRisultati() {
        clearTimeout(timer);

        timer = setTimeout(async () => {
            const parametri = new URLSearchParams({ q: ricerca.value });
            if (mostraPassati.checked) parametri.set('mostra_passati', '1');
            const response = await fetch(`/viaggi/search?${parametri}`);

            if (response.ok) {
                tabella.innerHTML = await response.text();
            }
        }, 400);
    }

    ricerca.addEventListener('input', aggiornaRisultati);
    mostraPassati.addEventListener('change', aggiornaRisultati);
});
</script>
