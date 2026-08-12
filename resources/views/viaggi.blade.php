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
            <a href="{{ route('viaggi.create') }}" class="bg-green-600 text-white px-4 py-2 rounded whitespace-nowrap">Nuovo viaggio</a>
        </div>

        <form method="GET" class="mb-4" onsubmit="return false;">
            <label for="ricerca-viaggi" class="sr-only">Cerca viaggio</label>
            <input
                type="search"
                id="ricerca-viaggi"
                name="ricerca"
                value="{{ $ricerca ?? '' }}"
                placeholder="Cerca per nome, destinazione o tipologia"
                autocomplete="off"
                class="border rounded px-3 py-2 w-full md:w-96">
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
    const tabella = document.getElementById('viaggi-table-container');

    ricerca.addEventListener('input', function () {
        clearTimeout(timer);

        timer = setTimeout(async () => {
            const response = await fetch(
                `/viaggi/search?q=${encodeURIComponent(ricerca.value)}`
            );

            if (response.ok) {
                tabella.innerHTML = await response.text();
            }
        }, 400);
    });
});
</script>
