<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Clienti</h2>
    </x-slot>

    <div class="p-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
            <div>
                <h3 class="text-lg font-semibold">Elenco clienti</h3>
                <!--<p class="text-sm text-gray-500 mt-1">Gestisci anagrafiche e contatti dei clienti.</p>-->
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

            <!--<p class="mb-4 text-xs text-gray-500">Clic su una intestazione per ordinare. Usa Shift + clic su altre colonne per combinare ordinamenti multipli.</p>-->

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
    const documentiStato = @js($documentiStato ?? null);
    let ordinamenti = @js($ordinamenti ?? []);

    function serializzaOrdinamenti() {
        return ordinamenti.map((entry) => `${entry.field}:${entry.direction}`).join(',');
    }

    function prossimaDirezione(direzioneCorrente) {
        if (direzioneCorrente === 'asc') return 'desc';
        if (direzioneCorrente === 'desc') return null;
        return 'asc';
    }

    function aggiornaOrdinamenti(field, multiColonna = false) {
        const esistente = ordinamenti.find((entry) => entry.field === field);
        const nuovaDirezione = prossimaDirezione(esistente?.direction ?? null);

        if (!multiColonna) {
            ordinamenti = nuovaDirezione ? [{ field, direction: nuovaDirezione }] : [];
            return;
        }

        ordinamenti = ordinamenti.filter((entry) => entry.field !== field);
        if (nuovaDirezione) {
            ordinamenti.push({ field, direction: nuovaDirezione });
        }
    }

    async function aggiornaPagina(url) {
        const pagina = new URL(url, window.location.origin).searchParams.get('page');
        const parametri = new URLSearchParams({ q: ricerca.value });
        if (documentiStato) parametri.set('documenti_stato', documentiStato);
        if (ordinamenti.length) parametri.set('sort', serializzaOrdinamenti());
        if (pagina) parametri.set('page', pagina);
        const response = await fetch(`{{ route('clienti.search') }}?${parametri}`);
        if (response.ok) tabella.innerHTML = await response.text();
    }

    ricerca.addEventListener('input', function () {
        clearTimeout(timer);

        timer = setTimeout(async () => {
            const parametri = new URLSearchParams({ q: ricerca.value });
            if (documentiStato) parametri.set('documenti_stato', documentiStato);
            if (ordinamenti.length) parametri.set('sort', serializzaOrdinamenti());
            const response = await fetch(`{{ route('clienti.search') }}?${parametri}`);

            if (response.ok) {
                tabella.innerHTML = await response.text();
            }
        }, 400);
    });

    tabella.addEventListener('click', function (event) {
        const sortButton = event.target.closest('[data-sort-field]');
        if (sortButton) {
            event.preventDefault();
            aggiornaOrdinamenti(sortButton.dataset.sortField, event.shiftKey);

            clearTimeout(timer);
            timer = setTimeout(async () => {
                const parametri = new URLSearchParams({ q: ricerca.value });
                if (documentiStato) parametri.set('documenti_stato', documentiStato);
                if (ordinamenti.length) parametri.set('sort', serializzaOrdinamenti());
                const response = await fetch(`{{ route('clienti.search') }}?${parametri}`);
                if (response.ok) tabella.innerHTML = await response.text();
            }, 0);
            return;
        }

        const link = event.target.closest('a[href*="page="]');
        if (!link) return;
        event.preventDefault();
        aggiornaPagina(link.href);
    });
});
</script>
