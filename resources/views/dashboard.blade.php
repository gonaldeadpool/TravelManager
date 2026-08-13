<x-app-layout>
    <x-slot name="header"><h2 class="text-xl font-semibold leading-tight text-gray-800">Dashboard</h2></x-slot>
    @php
        $clienti = [['In regola', 'in_regola', '#38bdf8'], ['In scadenza', 'in_scadenza', '#facc15'], ['Scaduti', 'scaduti', '#ef4444']];
        $viaggi = [['Viaggi', 'viaggio', '#34d399'], ['Tour', 'tour', '#60a5fa'], ['Crociere', 'crociera', '#a78bfa']];
        $pratiche = [['Acconto non versato', 'acconto_non_versato', '#fb923c'], ['Acconto non versato in scadenza', 'acconto_non_versato_scadenza', '#f97316'], ['Acconto versato', 'acconto_versato', '#facc15'], ['Saldo non versato in scadenza', 'saldo_non_versato_scadenza', '#f59e0b'], ['Saldo versato', 'saldo_versato', '#22c55e']];
    @endphp
    <div class="p-6"><div class="mx-auto grid max-w-7xl grid-cols-1 gap-5 md:grid-cols-3">
        @foreach ([['Clienti', $totaleClienti, route('clienti'), $clienti, 'documenti_stato', $statiClienti], ['Viaggi', $totaleViaggi, route('viaggi.index'), $viaggi, 'tipologia', $tipologieViaggi], ['Pratiche', $totalePratiche, route('pratiche.index'), $pratiche, 'pagamento', $statiPratiche]] as $colonna)
            @php($totale = $colonna[1])
            <section class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                <div class="flex items-baseline justify-between border-b border-gray-100 pb-4"><h3 class="text-lg font-semibold">{{ $colonna[0] }}</h3><a href="{{ $colonna[2] }}" class="text-3xl font-bold hover:text-blue-600">{{ $totale }}</a></div>
                <div class="mx-auto my-6 flex items-center justify-center rounded-full" style="width: 224px; height: 224px; min-width: 224px; min-height: 224px; aspect-ratio: 1 / 1; flex: 0 0 224px; background: conic-gradient(@foreach ($colonna[3] as $index => $item) @php($inizio = collect($colonna[3])->take($index)->sum(fn ($voce) => $colonna[5]->get($voce[1], 0)) / max(1, $totale) * 100) @php($fine = $inizio + $colonna[5]->get($item[1], 0) / max(1, $totale) * 100) {{ $item[2] }} {{ $inizio }}% {{ $fine }}%@if (!$loop->last), @endif @endforeach);"><div class="flex items-center justify-center rounded-full bg-white text-center text-xs text-gray-500" style="width: 112px; height: 112px;">Totale<br><strong class="text-xl text-gray-900">{{ $totale }}</strong></div></div>
                <div class="space-y-2">@foreach ($colonna[3] as $item)<a href="{{ $colonna[2] . '?' . $colonna[4] . '=' . $item[1] }}" class="flex items-center justify-between rounded px-2 py-1.5 text-sm hover:bg-gray-50"><span class="flex items-center gap-2"><span class="h-3 w-3 rounded-full" style="background-color: {{ $item[2] }}"></span>{{ $item[0] }}</span><strong>{{ $colonna[5]->get($item[1], 0) }}</strong></a>@endforeach</div>
            </section>
        @endforeach
    </div></div>
</x-app-layout>
