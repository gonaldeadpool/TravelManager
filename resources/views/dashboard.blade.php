<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Dashboard</h2>
            <div x-data="{ open: false }" class="relative">
                <button type="button" @click="open = !open" @click.outside="open = false" class="inline-flex items-center gap-1 rounded border border-gray-300 bg-white px-3 py-1.5 text-sm text-gray-700 hover:bg-gray-50">
                    Widget
                    <svg aria-hidden="true" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6" /></svg>
                </button>
                <form x-show="open" x-cloak method="POST" action="{{ route('dashboard.widgets.update') }}" class="absolute right-0 z-10 mt-2 w-64 rounded border border-gray-200 bg-white p-3 shadow-lg">
                    @csrf
                    <p class="mb-2 text-xs font-semibold uppercase text-gray-500">Mostra in dashboard</p>
                    <div class="space-y-1">
                        @foreach ($widgetsDisponibili as $chiave => $etichetta)
                            <label class="flex items-center gap-2 rounded px-1 py-1 text-sm hover:bg-gray-50">
                                <input type="checkbox" name="widgets[]" value="{{ $chiave }}" @checked(in_array($chiave, $widgetsAttivi, true)) onchange="this.form.submit()" class="rounded border-gray-300 text-blue-600">
                                {{ $etichetta }}
                            </label>
                        @endforeach
                    </div>
                </form>
            </div>
        </div>
    </x-slot>
    @php
        $clienti = [['In regola', 'in_regola', '#38bdf8'], ['In scadenza', 'in_scadenza', '#facc15'], ['Scaduti', 'scaduti', '#ef4444']];
        $viaggi = [['Viaggi giornalieri', 'viaggio', '#34d399'], ['Soggiorni', 'soggiorno', '#f472b6'], ['Tour', 'tour', '#60a5fa'], ['Crociere', 'crociera', '#a78bfa']];
        $pratiche = [['Acconto non versato', 'acconto_non_versato', '#facc15'], ['Acconto non versato in scadenza', 'acconto_non_versato_scadenza', '#f97316'], ['Saldo non versato in scadenza', 'saldo_non_versato_scadenza', '#ef4444']];
        $sezioni = [
            'clienti' => ['Clienti', $totaleClienti, route('clienti'), $clienti, 'documenti_stato', $statiClienti],
            'viaggi' => ['Viaggi', $totaleViaggi, route('viaggi.index'), $viaggi, 'tipologia', $tipologieViaggi],
            'pratiche' => ['Pratiche', $totalePratiche, route('pratiche.index'), $pratiche, 'pagamento', $statiPratiche],
        ];
    @endphp
    <div class="p-3 md:p-6" x-data="dashboardWidgetOrder({ url: @js(route('dashboard.widgets.order')), csrf: @js(csrf_token()), ordine: @js($widgetsAttivi) })">
        <p class="sr-only" aria-live="polite" x-text="messaggioOrdine"></p>
        <div x-ref="griglia" @pointerdown="iniziaTocco($event)" @pointermove="muoviTocco($event)" @pointerup="finisciTocco()" @pointercancel="finisciTocco()" @dragstart="iniziaTrascinamento($event)" @dragover.prevent="riordinaDuranteTrascinamento($event)" @drop.prevent="salvaOrdine()" @dragend="terminaTrascinamento()" class="mx-auto grid max-w-7xl grid-cols-1 gap-3 md:grid-cols-3 md:gap-5">
        @foreach ($sezioni as $chiave => $colonna)
            @continue(! in_array($chiave, $widgetsAttivi, true))
            @php $totale = $colonna[1]; @endphp
            <section data-dashboard-widget="{{ $chiave }}" x-data="{ aperto: false }" class="rounded-lg border border-gray-200 bg-white p-3 shadow-sm md:p-5">
                <div x-show="!aperto" class="flex md:hidden">
                    <span data-drag-handle role="button" tabindex="-1" aria-label="Trascina per riordinare" class="flex h-10 w-7 shrink-0 cursor-grab touch-none items-center justify-center self-center rounded text-gray-400 md:hidden"><svg aria-hidden="true" class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><circle cx="8" cy="5" r="1.5"/><circle cx="16" cy="5" r="1.5"/><circle cx="8" cy="12" r="1.5"/><circle cx="16" cy="12" r="1.5"/><circle cx="8" cy="19" r="1.5"/><circle cx="16" cy="19" r="1.5"/></svg></span>
                    <button type="button" @click="aperto = true" class="flex min-w-0 flex-1 items-center gap-2 text-left" aria-label="Espandi widget {{ $colonna[0] }}">
                        <span class="w-[4.5rem] shrink-0">
                            <span class="block text-xs font-semibold uppercase text-gray-500">{{ $colonna[0] }}</span>
                            <span class="block text-4xl font-bold leading-tight">{{ $totale }}</span>
                        </span>
                        @php
                            $gradiente = [];
                            $etichette = [];
                            $accumulato = 0;
                            foreach ($colonna[3] as $voce) {
                                $valore = $colonna[5]->get($voce[1], 0);
                                $quota = $valore / max(1, $totale) * 100;
                                $gradiente[] = $voce[2] . ' ' . $accumulato . '% ' . ($accumulato + $quota) . '%';
                                if ($quota >= 12) {
                                    $angolo = deg2rad(($accumulato + $quota / 2) * 3.6);
                                    $etichette[] = [$valore, round(sin($angolo) * 30, 1), round(-cos($angolo) * 30, 1)];
                                }
                                $accumulato += $quota;
                            }
                        @endphp
                        <span class="relative block h-[88px] w-[88px] shrink-0 rounded-full" style="background: conic-gradient({{ implode(', ', array_merge($gradiente, $accumulato < 99.9 ? ['#e5e7eb '.$accumulato.'% 100%'] : [])) ?: '#e5e7eb 0% 100%' }});">
                            <span class="absolute left-1/2 top-1/2 h-10 w-10 -translate-x-1/2 -translate-y-1/2 rounded-full bg-white"></span>
                            @foreach ($etichette as $etichetta)
                                <span class="absolute text-[11px] font-bold leading-none text-white" style="left: calc(50% + {{ $etichetta[1] }}px); top: calc(50% + {{ $etichetta[2] }}px); transform: translate(-50%, -50%); text-shadow: 0 0 2px rgba(0,0,0,.5)">{{ $etichetta[0] }}</span>
                            @endforeach
                        </span>
                        <span class="min-w-0 flex-1 space-y-0.5 pl-1">
                            @foreach ($colonna[3] as $voce)
                                <span class="flex items-center gap-1.5 text-[11px] leading-4 text-gray-600"><span class="h-2 w-2 shrink-0 rounded-full" style="background-color: {{ $voce[2] }}"></span><span class="truncate">{{ $voce[0] }}</span></span>
                            @endforeach
                        </span>
                    </button>
                </div>
                <div :class="aperto ? '' : 'hidden md:block'">
                <button type="button" x-show="aperto" @click="aperto = false" class="mb-2 text-xs text-blue-600 md:hidden">Riduci</button>
                <div class="flex items-center justify-between border-b border-gray-100 pb-4"><div class="flex items-center gap-2"><button type="button" draggable="true" title="Trascina per riordinare" aria-label="Sposta widget {{ $colonna[0] }}" class="cursor-grab rounded p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-700 active:cursor-grabbing"><svg aria-hidden="true" class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><circle cx="8" cy="5" r="1.5"/><circle cx="16" cy="5" r="1.5"/><circle cx="8" cy="12" r="1.5"/><circle cx="16" cy="12" r="1.5"/><circle cx="8" cy="19" r="1.5"/><circle cx="16" cy="19" r="1.5"/></svg></button><h3 class="text-lg font-semibold">{{ $colonna[0] }}</h3></div><a href="{{ $colonna[2] }}" class="text-3xl font-bold hover:text-blue-600">{{ $totale }}</a></div>
                <div class="mx-auto my-6 flex items-center justify-center rounded-full" style="width: 224px; height: 224px; min-width: 224px; min-height: 224px; aspect-ratio: 1 / 1; flex: 0 0 224px; background: conic-gradient(@foreach ($colonna[3] as $index => $item) @php $inizio = collect($colonna[3])->take($index)->sum(fn ($voce) => $colonna[5]->get($voce[1], 0)) / max(1, $totale) * 100; @endphp @php $fine = $inizio + $colonna[5]->get($item[1], 0) / max(1, $totale) * 100; @endphp {{ $item[2] }} {{ $inizio }}% {{ $fine }}%@if (!$loop->last), @endif @endforeach, #e5e7eb {{ $fine ?? 0 }}% 100%);"><div class="flex items-center justify-center rounded-full bg-white text-center text-xs text-gray-500" style="width: 112px; height: 112px;">Totale<br><strong class="text-xl text-gray-900">{{ $totale }}</strong></div></div>
                <div class="space-y-2">@foreach ($colonna[3] as $item)<a href="{{ $colonna[2] . '?' . $colonna[4] . '=' . $item[1] }}" class="flex items-center justify-between rounded px-2 py-1.5 text-sm hover:bg-gray-50"><span class="flex items-center gap-2"><span class="h-3 w-3 rounded-full" style="background-color: {{ $item[2] }}"></span>{{ $item[0] }}</span><strong>{{ $colonna[5]->get($item[1], 0) }}</strong></a>@endforeach</div>
                </div>
            </section>
        @endforeach

        @if (in_array('top_viaggi', $widgetsAttivi, true))
            @php $maxPratiche = max(1, optional($topViaggi->first())->pratiche_count ?? 1); @endphp
            <section data-dashboard-widget="top_viaggi" x-data="{ aperto: false }" class="rounded-lg border border-gray-200 bg-white p-3 shadow-sm md:p-5">
                <div x-show="!aperto" class="flex md:hidden">
                    <span data-drag-handle role="button" tabindex="-1" aria-label="Trascina per riordinare" class="flex h-10 w-7 shrink-0 cursor-grab touch-none items-center justify-center self-center rounded text-gray-400 md:hidden"><svg aria-hidden="true" class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><circle cx="8" cy="5" r="1.5"/><circle cx="16" cy="5" r="1.5"/><circle cx="8" cy="12" r="1.5"/><circle cx="16" cy="12" r="1.5"/><circle cx="8" cy="19" r="1.5"/><circle cx="16" cy="19" r="1.5"/></svg></span>
                    <button type="button" @click="aperto = true" class="block min-w-0 flex-1 text-left" aria-label="Espandi widget Viaggi più venduti">
                        <span class="mb-1 block text-xs font-semibold uppercase text-gray-500">Viaggi più venduti</span>
                        @foreach ($topViaggi->take(3) as $viaggio)
                            <span class="flex items-center gap-2 text-xs leading-5"><span class="w-24 shrink-0 truncate">{{ $viaggio->nome }}</span><span class="h-1.5 flex-1 rounded-full bg-gray-100"><span class="block h-1.5 rounded-full bg-blue-500" style="width: {{ $viaggio->pratiche_count / $maxPratiche * 100 }}%"></span></span><strong class="w-5 text-right">{{ $viaggio->pratiche_count }}</strong></span>
                        @endforeach
                    </button>
                </div>
                <div :class="aperto ? '' : 'hidden md:block'">
                <button type="button" x-show="aperto" @click="aperto = false" class="mb-2 text-xs text-blue-600 md:hidden">Riduci</button>
                <div class="flex items-center justify-between border-b border-gray-100 pb-4"><div class="flex items-center gap-2"><button type="button" draggable="true" title="Trascina per riordinare" aria-label="Sposta widget Viaggi più venduti" class="cursor-grab rounded p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-700 active:cursor-grabbing"><svg aria-hidden="true" class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><circle cx="8" cy="5" r="1.5"/><circle cx="16" cy="5" r="1.5"/><circle cx="8" cy="12" r="1.5"/><circle cx="16" cy="12" r="1.5"/><circle cx="8" cy="19" r="1.5"/><circle cx="16" cy="19" r="1.5"/></svg></button><h3 class="text-lg font-semibold">Viaggi più venduti</h3></div><a href="{{ route('viaggi.index') }}" class="text-sm text-blue-600 hover:underline">Vedi tutti</a></div>
                <div class="mt-4 space-y-3">
                    @forelse ($topViaggi as $viaggio)
                        <a href="{{ route('pratiche.index', ['viaggio_id' => $viaggio->id]) }}" class="block rounded px-2 py-1.5 hover:bg-gray-50">
                            <div class="mb-1 flex items-center justify-between text-sm"><span class="truncate font-medium">{{ $viaggio->nome }}</span><strong>{{ $viaggio->pratiche_count }} {{ $viaggio->pratiche_count === 1 ? 'pratica' : 'pratiche' }}</strong></div>
                            <div class="h-2 w-full rounded-full bg-gray-100"><div class="h-2 rounded-full bg-blue-500" style="width: {{ $viaggio->pratiche_count / $maxPratiche * 100 }}%"></div></div>
                        </a>
                    @empty
                        <p class="text-sm text-gray-500">Nessuna pratica registrata.</p>
                    @endforelse
                </div>
                </div>
            </section>
        @endif

        @if (in_array('capacita_viaggi', $widgetsAttivi, true))
            <x-travel-capacity-carousel :travels="$travels" />
        @endif
        </div>
    </div>
</x-app-layout>

