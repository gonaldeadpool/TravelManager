<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">Pratiche</h2>
    </x-slot>

    <div class="p-6">
        @if (session('success'))
            <div class="mx-auto mb-4 max-w-7xl rounded border border-green-400 bg-green-100 px-4 py-3 text-green-700">{{ session('success') }}</div>
        @endif

        @if ($viaggioFiltrato)
            <div class="mx-auto mb-4 flex max-w-7xl items-center justify-between gap-4 rounded border border-blue-200 bg-blue-50 px-4 py-3 text-blue-800">
                <span>Pratiche del viaggio: <strong>{{ $viaggioFiltrato->nome }}</strong></span>
                <a href="{{ route('pratiche.index') }}" class="text-sm underline">Mostra tutte</a>
            </div>
        @endif

        <div class="mx-auto mb-6 flex max-w-7xl flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <h3 class="text-lg font-semibold">Elenco pratiche</h3>
                <p class="mt-1 text-sm text-gray-500">Gestisci pratiche, pagamenti e partecipanti ai viaggi.</p>
            </div>
            <a href="{{ route('pratiche.create') }}" class="whitespace-nowrap rounded bg-green-600 px-4 py-2 text-white">Nuova pratica</a>
        </div>

        <form method="GET" class="mx-auto mb-4 flex max-w-7xl flex-col gap-3 md:flex-row md:items-center" onsubmit="return false;">
            <label for="ricerca-pratiche" class="sr-only">Cerca pratica</label>
            <input id="ricerca-pratiche" type="search" name="ricerca" value="{{ $ricerca ?? '' }}" placeholder="Cerca per viaggio, destinazione, tipologia o cliente" autocomplete="off" class="w-full rounded border px-3 py-2 md:w-96">
            <label class="flex items-center gap-2 text-sm text-gray-700"><input id="mostra-passati" type="checkbox" name="mostra_passati" value="1" @checked($mostraPassati ?? false) @disabled($viaggioFiltrato) class="rounded border-gray-300 text-blue-600"> Mostra pratiche di viaggi passati</label>
        </form>

        <p class="mx-auto mb-4 max-w-7xl text-xs text-gray-500">Clic su una intestazione per ordinare. Usa Shift + clic su altre colonne per combinare ordinamenti multipli.</p>

        <div id="pratiche-table-container" class="mx-auto max-w-7xl overflow-hidden rounded bg-white shadow">
            @include('pratiche._table')
        </div>
    </div>
</x-app-layout>

<script>
document.addEventListener('DOMContentLoaded', function () {
    let timer;
    const ricerca = document.getElementById('ricerca-pratiche');
    const mostraPassati = document.getElementById('mostra-passati');
    const tabella = document.getElementById('pratiche-table-container');
    const viaggioId = @js($viaggioFiltrato?->id);
    const pagamento = @js($pagamento ?? null);
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
        if (mostraPassati.checked) parametri.set('mostra_passati', '1');
        if (viaggioId) parametri.set('viaggio_id', viaggioId);
        if (pagamento) parametri.set('pagamento', pagamento);
        if (ordinamenti.length) parametri.set('sort', serializzaOrdinamenti());
        if (pagina) parametri.set('page', pagina);
        const response = await fetch(`{{ route('pratiche.search') }}?${parametri}`);
        if (response.ok) tabella.innerHTML = await response.text();
    }

    function aggiornaRisultati() {
        clearTimeout(timer);
        timer = setTimeout(async () => {
            const parametri = new URLSearchParams({ q: ricerca.value });
            if (mostraPassati.checked) parametri.set('mostra_passati', '1');
            if (viaggioId) parametri.set('viaggio_id', viaggioId);
            if (pagamento) parametri.set('pagamento', pagamento);
            if (ordinamenti.length) parametri.set('sort', serializzaOrdinamenti());
            const response = await fetch(`{{ route('pratiche.search') }}?${parametri}`);
            if (response.ok) tabella.innerHTML = await response.text();
        }, 400);
    }

    ricerca.addEventListener('input', aggiornaRisultati);
    mostraPassati.addEventListener('change', aggiornaRisultati);
    tabella.addEventListener('click', function (event) {
        const sortButton = event.target.closest('[data-sort-field]');
        if (sortButton) {
            event.preventDefault();
            aggiornaOrdinamenti(sortButton.dataset.sortField, event.shiftKey);
            aggiornaRisultati();
            return;
        }

        const link = event.target.closest('a[href*="page="]');
        if (!link) return;
        event.preventDefault();
        aggiornaPagina(link.href);
    });
});
</script>