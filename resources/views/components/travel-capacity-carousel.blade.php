@props(['travels'])

<section data-dashboard-widget="capacita_viaggi" x-data="travelCapacityCarousel()" x-init="inizializza()" class="min-w-0 rounded-lg border border-gray-200 bg-white p-5 shadow-sm" aria-labelledby="travel-capacity-title">
    <div class="mb-4 flex items-center justify-between gap-4">
        <div class="flex items-center gap-2">
            <button type="button" draggable="true" title="Trascina per riordinare" aria-label="Sposta widget Stato conferma viaggi" class="cursor-grab rounded p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-700 active:cursor-grabbing">
                <svg aria-hidden="true" class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><circle cx="8" cy="5" r="1.5"/><circle cx="16" cy="5" r="1.5"/><circle cx="8" cy="12" r="1.5"/><circle cx="16" cy="12" r="1.5"/><circle cx="8" cy="19" r="1.5"/><circle cx="16" cy="19" r="1.5"/></svg>
            </button>
            <h3 id="travel-capacity-title" class="text-lg font-semibold text-gray-900">Stato conferma viaggi</h3>
        </div>
        @if ($travels->isNotEmpty())
            <div class="flex items-center gap-2">
                <span class="mr-1 text-sm tabular-nums text-gray-500" aria-live="polite"><span x-text="indice + 1"></span> / {{ $travels->count() }}</span>
                <button type="button" @click="vaiA(-1)" :disabled="indice === 0" aria-label="Viaggio precedente" class="inline-flex h-9 w-9 items-center justify-center rounded border border-gray-300 text-gray-700 hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-40">
                    <svg aria-hidden="true" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                </button>
                <button type="button" @click="vaiA(1)" :disabled="indice >= {{ $travels->count() - 1 }}" aria-label="Viaggio successivo" class="inline-flex h-9 w-9 items-center justify-center rounded border border-gray-300 text-gray-700 hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-40">
                    <svg aria-hidden="true" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
                </button>
            </div>
        @endif
    </div>

    @if ($travels->isEmpty())
        <p class="rounded border border-dashed border-gray-300 px-4 py-8 text-center text-sm text-gray-500">Nessuna partenza futura disponibile.</p>
    @else
        <div class="min-w-0">
            <div
                x-ref="traccia"
                role="region"
                aria-label="Viaggi e stato di riempimento"
                tabindex="0"
                @scroll="aggiornaIndice()"
                @keydown.left.prevent="vaiA(-1)"
                @keydown.right.prevent="vaiA(1)"
                @pointerdown="iniziaTrascinamento($event)"
                @pointermove="trascina($event)"
                @pointerup="terminaTrascinamento()"
                @pointercancel="terminaTrascinamento()"
                class="flex snap-x snap-mandatory overflow-x-auto scroll-smooth touch-pan-x rounded focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-600"
            >
                @foreach ($travels as $travel)
                    @php
                        $iscritti = (int) $travel['iscritti'];
                        $minimo = (int) $travel['numeroMinimo'];
                        $massimo = $travel['numeroMassimo'] !== null ? (int) $travel['numeroMassimo'] : null;
                        $completo = $massimo !== null && $massimo > 0 && $iscritti >= $massimo;
                        $confermato = $iscritti >= $minimo;
                        $quasiPieno = $massimo !== null && $massimo > 0 && $iscritti >= ceil($massimo * 0.8) && $iscritti < $massimo;
                        $percentuale = $massimo !== null && $massimo > 0 ? min(100, ($iscritti / $massimo) * 100) : null;
                        $percentualeMinimo = $massimo !== null && $massimo > 0 ? min(100, ($minimo / $massimo) * 100) : null;
                        $coloreMinimo = $confermato ? '#22C55E' : '#EF4444';
                        $coloreMassimo = $completo ? '#3B82F6' : ($quasiPieno ? '#F97316' : '#64748B');
                        if ($completo) {
                            $stato = 'Completo';
                            $colore = '#3B82F6';
                        } elseif (! $confermato) {
                            $stato = 'Non confermato';
                            $colore = '#EF4444';
                        } elseif ($quasiPieno) {
                            $stato = 'Ultimi posti';
                            $colore = '#F97316';
                        } else {
                            $stato = 'Confermato';
                            $colore = '#22C55E';
                        }
                    @endphp
                    <article data-capacity-slide class="w-full min-w-0 shrink-0 snap-start p-1">
                        <div class="flex min-h-[250px] flex-col rounded-lg border border-gray-200 bg-white p-5 transition-shadow duration-200 hover:shadow-md sm:p-6">
                            <div class="flex flex-wrap items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <h4 class="truncate text-xl font-semibold text-gray-900"><a href="{{ route('viaggi.show', ['viaggio' => $travel['id']]) }}" class="rounded-sm hover:text-blue-700 hover:underline focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-600">{{ $travel['titolo'] }}</a></h4>
                                    <time class="mt-1 block text-sm text-gray-500" datetime="{{ \Carbon\CarbonImmutable::createFromTimestamp($travel['ordinamentoData'])->format('Y-m-d') }}">{{ $travel['dataPartenza'] }}</time>
                                </div>
                                <span class="inline-flex shrink-0 items-center gap-2 rounded-full px-3 py-1 text-sm font-semibold" style="color: {{ $colore }}; background-color: {{ $colore }}1A">
                                    <span aria-hidden="true" class="h-2 w-2 rounded-full" style="background-color: {{ $colore }}"></span>{{ $stato }}
                                </span>
                            </div>

                            <div class="mt-5 grid {{ $massimo !== null ? 'grid-cols-3' : 'grid-cols-2' }} gap-3">
                                <div class="rounded-md p-3" style="background-color: {{ $colore }}1A; color: {{ $colore }}">
                                    <p class="text-xs font-medium uppercase">Iscritti</p>
                                    <a href="{{ route('pratiche.index', ['viaggio_id' => $travel['id']]) }}" aria-label="Apri {{ $iscritti }} iscritti del viaggio {{ $travel['titolo'] }}" class="mt-1 inline-block rounded-sm text-2xl font-semibold tabular-nums underline decoration-transparent underline-offset-2 hover:decoration-current focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-current">{{ $iscritti }}</a>
                                </div>
                                <div class="rounded-md p-3" style="background-color: {{ $coloreMinimo }}1A; color: {{ $coloreMinimo }}">
                                    <p class="text-xs font-medium uppercase">Minimo</p>
                                    <p class="mt-1 text-2xl font-semibold tabular-nums">{{ $minimo }}</p>
                                </div>
                                @if ($massimo !== null)
                                    <div class="rounded-md p-3" style="background-color: {{ $coloreMassimo }}1A; color: {{ $coloreMassimo }}">
                                        <p class="text-xs font-medium uppercase">Massimo</p>
                                        <p class="mt-1 text-2xl font-semibold tabular-nums">{{ $massimo }}</p>
                                    </div>
                                @endif
                            </div>

                            @if ($massimo !== null && $percentuale !== null)
                                <div class="mt-5">
                                    <div class="relative mb-2 h-5 text-xs text-gray-500">
                                        <span class="absolute left-0 top-0">0</span>
                                        <span class="absolute top-0 -translate-x-1/2 whitespace-nowrap" style="left: {{ $percentualeMinimo }}%">Minimo {{ $minimo }}</span>
                                        <span class="absolute right-0 top-0">{{ $massimo }}</span>
                                    </div>
                                    <div class="relative h-3 overflow-visible rounded-full bg-gray-100" role="progressbar" aria-label="Riempimento {{ $travel['titolo'] }}" aria-valuemin="0" aria-valuemax="{{ $massimo }}" aria-valuenow="{{ min($iscritti, $massimo) }}">
                                        <div class="h-full rounded-full transition-[width] duration-500" style="width: {{ $percentuale }}%; background-color: {{ $colore }}"></div>
                                        <span aria-hidden="true" class="absolute -top-1 h-5 w-0.5 bg-gray-700" style="left: {{ $percentualeMinimo }}%"></span>
                                    </div>
                                    <div class="mt-2 flex items-center justify-between text-sm">
                                        <span class="tabular-nums text-gray-600">{{ $iscritti }} / {{ $massimo }}</span>
                                        <strong class="tabular-nums text-gray-900">{{ number_format($percentuale, 0) }}% riempimento</strong>
                                    </div>
                                </div>
                            @endif

                            <p class="mt-auto pt-4 text-sm font-medium" style="color: {{ $colore }}">
                                @if (! $confermato)
                                    Mancano {{ $minimo - $iscritti }} {{ $minimo - $iscritti === 1 ? 'partecipante' : 'partecipanti' }} per raggiungere il minimo.
                                @elseif ($completo)
                                    Capacità massima raggiunta.
                                @elseif ($quasiPieno)
                                    Restano {{ max(0, $massimo - $iscritti) }} {{ $massimo - $iscritti === 1 ? 'posto disponibile' : 'posti disponibili' }}.
                                @else
                                    Viaggio confermato.
                                @endif
                                @if ($travel['inScadenza'])
                                    <span class="block mt-1 text-xs font-normal">Partenza tra {{ $travel['giorniAllaPartenza'] }} {{ $travel['giorniAllaPartenza'] === 1 ? 'giorno' : 'giorni' }}.</span>
                                @endif
                            </p>
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    @endif
</section>

<script>
    function travelCapacityCarousel() {
        return {
            indice: 0,
            trascinamento: null,
            inizializza() { if (this.$refs.traccia) this.aggiornaIndice(); },
            vaiA(direzione) {
                const traccia = this.$refs.traccia;
                const slides = traccia?.querySelectorAll('[data-capacity-slide]') ?? [];
                const prossimoIndice = Math.max(0, Math.min(slides.length - 1, this.indice + direzione));
                slides[prossimoIndice]?.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'start' });
            },
            aggiornaIndice() {
                const traccia = this.$refs.traccia;
                if (!traccia) return;
                const slide = traccia.querySelector('[data-capacity-slide]');
                if (slide) this.indice = Math.round(traccia.scrollLeft / slide.getBoundingClientRect().width);
            },
            iniziaTrascinamento(evento) {
                if (evento.pointerType !== 'mouse' || evento.button !== 0 || !this.$refs.traccia) return;
                if (evento.target.closest('a, button, input, select, textarea, [role="button"]')) return;
                this.trascinamento = { x: evento.clientX, scroll: this.$refs.traccia.scrollLeft };
                this.$refs.traccia.setPointerCapture(evento.pointerId);
            },
            trascina(evento) {
                if (this.trascinamento && this.$refs.traccia) {
                    this.$refs.traccia.scrollLeft = this.trascinamento.scroll - (evento.clientX - this.trascinamento.x);
                }
            },
            terminaTrascinamento() { this.trascinamento = null; },
        };
    }
</script>
