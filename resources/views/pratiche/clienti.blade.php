<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">Aggiungi clienti alla pratica #{{ $pratica->id }}</h2>
    </x-slot>

    <div class="p-6">
        @if ($errors->any())
            <div class="mx-auto mb-4 max-w-7xl rounded border border-red-400 bg-red-100 px-4 py-3 text-red-700">
                <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
        @endif

        <div class="mx-auto max-w-7xl">
            <div class="mb-6 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <h3 class="text-lg font-semibold">Elenco clienti</h3>
                    <p class="mt-1 text-sm text-gray-500">Seleziona uno o più clienti da aggiungere alla pratica.</p>
                </div>
                <div class="flex gap-3">
                    <a href="{{ route('clienti.create') }}" target="_blank" class="rounded bg-green-600 px-4 py-2 text-white">Nuova anagrafica</a>
                    <a href="{{ route('pratiche.edit', $pratica) }}" class="rounded border px-4 py-2 text-gray-700">Torna alla pratica</a>
                </div>
            </div>

            <form method="GET" class="mb-4" onsubmit="return false;">
                <label for="ricerca-clienti" class="sr-only">Cerca cliente</label>
                <input id="ricerca-clienti" type="search" name="ricerca" value="{{ $ricerca }}" placeholder="Cerca per nome, cognome o email" autocomplete="off" class="w-full rounded border px-3 py-2 md:max-w-md">
            </form>

            <div id="clienti-table-container">
                @include('pratiche.clienti._table')
            </div>
        </div>
    </div>
</x-app-layout>

<script>
document.addEventListener('DOMContentLoaded', function () {
    let timer;
    const ricerca = document.getElementById('ricerca-clienti');
    const tabella = document.getElementById('clienti-table-container');
    const searchUrl = @js(route('pratiche.clienti.search', $pratica));
    const selezionati = new Set(@js($pratica->clienti->pluck('id')->map(fn ($id) => (string) $id)->all()));

    function sincronizzaSelezione() {
        tabella.querySelectorAll('input[name="clienti[]"]').forEach((cb) => {
            if (cb.checked) selezionati.add(cb.value);
        });
    }

    function applicaSelezione() {
        tabella.querySelectorAll('input[name="clienti[]"]').forEach((cb) => {
            cb.checked = selezionati.has(cb.value);
        });
    }

    async function aggiornaTabella(parametri) {
        sincronizzaSelezione();
        const response = await fetch(`${searchUrl}?${parametri}`);
        if (response.ok) {
            tabella.innerHTML = await response.text();
            applicaSelezione();
        }
    }

    ricerca.addEventListener('input', function () {
        clearTimeout(timer);

        timer = setTimeout(function () {
            aggiornaTabella(new URLSearchParams({ q: ricerca.value }));
        }, 400);
    });

    tabella.addEventListener('change', function (event) {
        const checkbox = event.target.closest('input[name="clienti[]"]');
        if (!checkbox) return;
        if (checkbox.checked) selezionati.add(checkbox.value);
        else selezionati.delete(checkbox.value);
    });

    tabella.addEventListener('click', function (event) {
        const link = event.target.closest('a[href*="page="]');
        if (!link) return;
        event.preventDefault();

        const pagina = new URL(link.href, window.location.origin).searchParams.get('page');
        const parametri = new URLSearchParams({ q: ricerca.value });
        if (pagina) parametri.set('page', pagina);
        aggiornaTabella(parametri);
    });

    tabella.addEventListener('submit', function (event) {
        event.preventDefault();
        const form = event.target;
        sincronizzaSelezione();
        form.querySelectorAll('input[name="clienti[]"]').forEach((el) => el.remove());
        selezionati.forEach((id) => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'clienti[]';
            input.value = id;
            form.appendChild(input);
        });
        form.submit();
    });
});
</script>