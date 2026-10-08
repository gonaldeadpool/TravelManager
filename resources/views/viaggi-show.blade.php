<x-app-layout>
    <style>
        @media print {
            nav, button, a, form, [draggable="true"] { display: none !important; }
            .viaggio-print-panel { display: block !important; break-inside: avoid; margin-bottom: 1rem; }
            .viaggio-print-layout { display: block !important; }
            body, .min-h-screen, main { background: #ffffff !important; }
            .shadow, .shadow-sm { box-shadow: none !important; }
        }
    </style>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <h2 class="text-xl font-semibold leading-tight text-gray-800">{{ $viaggio->nome }}</h2>
            <div class="flex items-center gap-2">
                <a href="{{ route('viaggi.riepilogo.pdf', $viaggio) }}" target="_blank" rel="noopener" title="Anteprima di stampa PDF" aria-label="Anteprima di stampa PDF" class="inline-flex h-9 w-9 items-center justify-center rounded border text-gray-700 hover:bg-gray-100">
                    <svg aria-hidden="true" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
                </a>
                <a href="{{ route('viaggi.riepilogo.pdf.download', $viaggio) }}" title="Scarica PDF" aria-label="Scarica PDF" class="inline-flex h-9 w-9 items-center justify-center rounded border text-gray-700 hover:bg-gray-100">
                    <svg aria-hidden="true" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3v12"/><path d="m7 10 5 5 5-5"/><path d="M5 21h14"/></svg>
                </a>
                <a href="{{ route('viaggi.edit', $viaggio) }}" class="rounded border px-4 py-2 text-sm text-gray-700">Modifica viaggio</a>
                <a href="{{ route('viaggi.index') }}" class="rounded border px-4 py-2 text-sm text-gray-700">Torna ai viaggi</a>
            </div>
        </div>
    </x-slot>

    <div class="p-6" x-data="{ tab: 'riepilogo' }">
        @php
            $partecipanti = $viaggio->pratiche->flatMap->clienti->unique('id')->values();
            $postiAssegnati = $partecipanti->filter(fn ($cliente) => filled($cliente->pivot->posto ?? null))->keyBy(fn ($cliente) => ($cliente->pivot->posto_bus ?? 0) . ':' . $cliente->pivot->posto);
            $clientiDisponibili = $partecipanti->reject(fn ($cliente) => filled($cliente->pivot->posto ?? null));
            $clientiInTappa = $tappeRaccolta->flatMap->clienti->pluck('id')->unique();
            $clientiDisponibiliTappe = $partecipanti->whereNotIn('id', $clientiInTappa);
        @endphp
        <div class="mx-auto mb-6 max-w-6xl border-b border-gray-200">
            <nav class="flex gap-6 overflow-x-auto whitespace-nowrap" aria-label="Sezioni viaggio">
                <button type="button" @click="tab = 'riepilogo'" :class="tab === 'riepilogo' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700'" class="shrink-0 border-b-2 px-1 pb-3 text-sm font-semibold">Riepilogo</button>
                <button type="button" @click="tab = 'partecipanti'" :class="tab === 'partecipanti' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700'" class="shrink-0 border-b-2 px-1 pb-3 text-sm font-semibold">Partecipanti</button>
                @foreach ($busTrasporti as $indiceBus => $bus)
                    <button type="button" @click="tab = 'posti-{{ $indiceBus }}'" :class="tab === 'posti-{{ $indiceBus }}' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700'" class="shrink-0 border-b-2 px-1 pb-3 text-sm font-semibold">Bus {{ $indiceBus + 1 }}</button>
                @endforeach
                @if ($busTrasporti->isNotEmpty())
                    <button type="button" @click="tab = 'tappe'" :class="tab === 'tappe' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700'" class="shrink-0 border-b-2 px-1 pb-3 text-sm font-semibold">Tappe di raccolta</button>
                @endif
            </nav>
        </div>

        <div x-show="tab === 'riepilogo'" class="viaggio-print-panel mx-auto grid max-w-6xl grid-cols-1 gap-6 lg:grid-cols-[280px_minmax(0,1fr)]">
            <aside class="rounded bg-white p-6 shadow">
                <img src="{{ route('viaggi.locandina', $viaggio) }}" alt="Locandina di {{ $viaggio->nome }}" class="max-h-[360px] w-full rounded object-contain">
            </aside>

            <div class="space-y-6">
                <section class="rounded bg-white p-6 shadow">
                    <h3 class="mb-4 text-lg font-semibold">Informazioni del viaggio</h3>
                    <dl class="grid grid-cols-1 gap-4 text-sm md:grid-cols-2">
                        <div><dt class="text-gray-500">Tipologia</dt><dd class="mt-1 font-medium">{{ $viaggio->tipologia_label }}</dd></div>
                        <div><dt class="text-gray-500">Destinazione</dt><dd class="mt-1 font-medium">{{ $viaggio->destinazione }}</dd></div>
                        <div><dt class="text-gray-500">Periodo</dt><dd class="mt-1 font-medium">{{ $viaggio->data_partenza->format('d/m/Y') }} - {{ $viaggio->data_rientro->format('d/m/Y') }}</dd></div>
                        <div><dt class="text-gray-500">Durata</dt><dd class="mt-1 font-medium">{{ $viaggio->data_partenza->diffInDays($viaggio->data_rientro) + 1 }} giorni, {{ $viaggio->data_partenza->diffInDays($viaggio->data_rientro) }} notti</dd></div>
                        <div><dt class="text-gray-500">Quota a persona</dt><dd class="mt-1 font-medium">{{ $viaggio->prezzo !== null ? number_format($viaggio->prezzo, 2, ',', '.') . ' EUR' : '-' }}</dd></div>
                        <div><dt class="text-gray-500">Quota ridotto</dt><dd class="mt-1 font-medium">{{ $viaggio->quota_ridotto !== null ? number_format($viaggio->quota_ridotto, 2, ',', '.') . ' EUR' : '-' }}</dd></div>
                        <div><dt class="text-gray-500">Minimo partecipanti</dt><dd class="mt-1 font-medium">{{ $viaggio->minimo_partecipanti }}</dd></div>
                        <div><dt class="text-gray-500">Massimo partecipanti</dt><dd class="mt-1 font-medium">{{ $viaggio->massimo_partecipanti ?? '-' }}</dd></div>
                        <div><dt class="text-gray-500">Numero partecipanti</dt><dd class="mt-1 font-medium">{{ $numeroPartecipanti }}</dd></div>
                        <div><dt class="text-gray-500">Data acconto</dt><dd class="mt-1 font-medium">{{ $viaggio->data_acconto?->format('d/m/Y') ?? '-' }}</dd></div>
                        <div><dt class="text-gray-500">Importo acconto</dt><dd class="mt-1 font-medium">{{ number_format($importoAcconto, 2, ',', '.') }} EUR</dd></div>
                        <div><dt class="text-gray-500">Data saldo</dt><dd class="mt-1 font-medium">{{ $viaggio->data_saldo?->format('d/m/Y') ?? '-' }}</dd></div>
                        <div class="md:col-span-2"><dt class="text-gray-500">Note</dt><dd class="mt-1 whitespace-pre-line font-medium">{{ $viaggio->note ?: '-' }}</dd></div>
                    </dl>
                </section>

                <section class="rounded bg-white p-6 shadow">
                    <h3 class="mb-3 text-lg font-semibold">Trasporti</h3>
                    <div class="space-y-2">
                        @forelse ($viaggio->trasporti ?? [] as $trasporto)
                            <p class="rounded border p-3 text-sm">{{ ucfirst($trasporto['tipo'] ?? '') }} @if (!empty($trasporto['posti'])) - {{ $trasporto['posti'] }} posti @endif</p>
                        @empty
                            <p class="text-sm text-gray-500">Nessun trasporto configurato.</p>
                        @endforelse
                    </div>
                </section>

            </div>
        </div>

        @if ($busTrasporti->isNotEmpty())
            <div x-cloak :class="tab.startsWith('posti-') ? 'flex flex-col gap-4 md:flex-row md:items-start md:gap-6' : 'hidden'" class="viaggio-print-panel viaggio-print-layout mx-auto max-w-6xl">
                <div class="w-full min-w-0 md:flex-1">
                    @foreach ($busTrasporti as $indiceBus => $bus)
                        @php
                            $postiBus = (int) ($bus['posti'] ?? 0);
                            $numeroSedile = 0;
                            $righePosti = [];
                            for ($riga = 1; $riga <= 14; $riga++) {
                                $righePosti[$riga] = [];
                                for ($colonna = 1; $colonna <= 5; $colonna++) {
                                    $assegnabile = $riga === 14 || ($colonna !== 3 && ! (in_array($riga, [7, 8], true) && in_array($colonna, [4, 5], true)));
                                    $righePosti[$riga][$colonna] = $assegnabile ? ++$numeroSedile : null;
                                }
                            }
                        @endphp
                        <div x-show="tab === 'posti-{{ $indiceBus }}'" x-cloak class="viaggio-print-panel rounded bg-white p-3 shadow sm:p-6">
                            <div class="mb-4 sm:mb-5"><h3 class="text-lg font-semibold">Assegnazione posti bus {{ $indiceBus + 1 }}</h3><p class="mt-1 text-sm text-gray-500"><span class="hidden md:inline">Trascina un cliente oppure selezionalo e clicca su un posto disponibile.</span><span class="md:hidden">Tocca un posto disponibile e scegli il cliente.</span></p><p class="mt-1 hidden text-sm font-medium text-blue-700 md:block" data-bus-selection-status aria-live="polite"></p></div>
                            <div class="rounded-xl bg-slate-700 p-2 sm:p-5">
                                <div class="mx-auto w-full max-w-2xl rounded-[2rem] border-4 border-slate-300 bg-slate-100 p-2 shadow-inner sm:rounded-[3rem] sm:p-5">
                                    <div class="mb-3 flex items-center justify-between gap-1 rounded-full bg-slate-300 px-2 py-2 text-[9px] font-semibold uppercase tracking-wide text-slate-600 sm:mb-5 sm:gap-2 sm:px-5 sm:py-3 sm:text-xs sm:tracking-wider"><span>Autista</span><span class="text-center">Bus {{ $indiceBus + 1 }} - {{ $postiBus }} posti</span><span>Hostess</span></div>
                                    <div class="space-y-2">
                                        @foreach ($righePosti as $riga)
                                            <div class="grid grid-cols-5 gap-1 sm:gap-2" style="display: grid; grid-template-columns: repeat(5, minmax(0, 1fr));">
                                                @for ($colonna = 1; $colonna <= 5; $colonna++)
                                                    @php($posto = $riga[$colonna])
                                                    @if ($posto)
                                                        @php($chiavePosto = $indiceBus . ':' . $posto)
                                                        <div role="button" tabindex="0" aria-label="Posto {{ $posto }}{{ $postiAssegnati->has($chiavePosto) ? ', occupato' : ', disponibile' }}" class="seat min-h-11 min-w-0 cursor-pointer rounded-lg bg-white p-1 text-[10px] sm:min-h-16 sm:p-2 sm:text-xs" style="border: 3px solid {{ $postiAssegnati->has($chiavePosto) ? '#dc2626' : '#16a34a' }};" data-bus="{{ $indiceBus }}" data-seat="{{ $posto }}">
                                                            <div class="font-bold text-slate-700">{{ $posto }}</div>
                                                            @if ($postiAssegnati->has($chiavePosto))
                                                                @php($clienteSeduto = $postiAssegnati->get($chiavePosto))
                                                                <div draggable="true" data-client-id="{{ $clienteSeduto->id }}" data-client-name="{{ $clienteSeduto->cognome }} {{ $clienteSeduto->nome }}" data-seat-client class="mt-1 cursor-grab truncate rounded bg-blue-100 px-1 py-1 text-[9px] text-blue-800 active:cursor-grabbing sm:mt-2 sm:text-[11px]">{{ $clienteSeduto->cognome }} {{ $clienteSeduto->nome }}</div>
                                                            @else
                                                                <div class="seat-placeholder mt-1 h-3 rounded border border-dashed border-slate-300 sm:mt-2 sm:h-5"></div>
                                                            @endif
                                                        </div>
                                                    @else
                                                        <div aria-hidden="true" class="min-h-11 min-w-0 rounded-lg border-2 border-dashed border-slate-200 bg-slate-50 sm:min-h-16"></div>
                                                    @endif
                                                @endfor
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                <aside class="hidden w-full rounded bg-gray-50 p-4 shadow md:block md:w-72 md:flex-none">
                    <h4 class="mb-3 font-semibold">Clienti disponibili</h4>
                    <div id="clienti-disponibili" class="space-y-2" ondragover="event.preventDefault()" ondrop="rimuoviPosto(event)">
                        @forelse ($clientiDisponibili as $cliente)
                            <div draggable="true" data-client-id="{{ $cliente->id }}" data-client-name="{{ $cliente->cognome }} {{ $cliente->nome }}" class="client-card cursor-grab rounded border bg-white p-3 text-sm shadow-sm active:cursor-grabbing"><div class="font-medium">{{ $cliente->cognome }} {{ $cliente->nome }}</div><div class="text-xs text-gray-500">Nessun posto assegnato</div></div>
                        @empty
                            <p id="nessun-cliente-disponibile" class="rounded border border-dashed p-3 text-sm text-gray-500">Tutti i clienti hanno un posto assegnato.</p>
                        @endforelse
                    </div>
                </aside>
            </div>
        @endif

        @if ($busTrasporti->isNotEmpty())
        <div x-cloak :style="tab === 'tappe' ? 'display: grid; grid-template-columns: minmax(0, 1fr) 300px; gap: 1.5rem;' : 'display: none;'" class="viaggio-print-panel viaggio-print-layout mx-auto max-w-6xl">
            <section class="rounded bg-white p-6 shadow">
                <div class="mb-5 flex items-center justify-between gap-4">
                    <div><h3 class="text-lg font-semibold">Tappe di raccolta</h3><p class="mt-1 text-sm text-gray-500">Crea le fermate e assegna i partecipanti con il drag & drop.</p></div>
                    <button type="button" id="mostra-form-tappa" class="whitespace-nowrap rounded bg-blue-600 px-4 py-2 text-sm text-white">Aggiungi tappa</button>
                </div>
                <form id="form-tappa" class="mb-5 hidden rounded border border-blue-200 bg-blue-50 p-4" data-url="{{ route('viaggi.tappe-raccolta.store', $viaggio) }}">
                    <div class="grid grid-cols-1 gap-3 md:grid-cols-[1fr_160px_auto] md:items-end">
                        <div><label for="tappa-nome" class="mb-1 block text-sm font-medium">Nome tappa</label><input id="tappa-nome" name="nome" required class="w-full rounded border p-2"></div>
                        <div><label for="tappa-orario" class="mb-1 block text-sm font-medium">Orario</label><input id="tappa-orario" name="orario" type="time" required class="w-full rounded border p-2"></div>
                        <button class="rounded bg-green-600 px-4 py-2 text-sm text-white">Crea</button>
                    </div>
                </form>
                <div id="tappe-raccolta-lista" class="space-y-4">
                    @forelse ($tappeRaccolta as $tappa)
                        <div class="tappa-card rounded border p-4" data-tappa-id="{{ $tappa->id }}" data-tappa-url="{{ route('viaggi.tappe-raccolta.clienti.store', [$viaggio, $tappa]) }}">
                            <div class="mb-3 flex items-center justify-between"><h4 class="font-semibold">{{ $tappa->nome }} <span class="font-normal text-gray-500">- {{ $tappa->orario->format('H:i') }}</span></h4><span class="text-xs text-gray-500">Trascina qui i clienti</span></div>
                            <div class="tappa-clienti min-h-16 space-y-2 rounded border-2 border-dashed border-blue-200 p-3" data-drop-tappa ondragover="event.preventDefault()" ondrop="assegnaTappa(event, this.closest('[data-tappa-id]').dataset.tappaId, this.closest('[data-tappa-id]').dataset.tappaUrl)">
                                @foreach ($tappa->clienti as $cliente)
                                    <div class="flex items-center justify-between rounded bg-blue-50 px-3 py-2 text-sm" data-tappa-cliente="{{ $cliente->id }}" data-tappa-cliente-name="{{ $cliente->cognome }} {{ $cliente->nome }}"><span>{{ $cliente->cognome }} {{ $cliente->nome }}</span><button type="button" title="Rimuovi dalla tappa" aria-label="Rimuovi dalla tappa" class="text-red-600" onclick="rimuoviDaTappa(event, {{ $tappa->id }}, {{ $cliente->id }})">&#10005;</button></div>
                                @endforeach
                                @if ($tappa->clienti->isEmpty())<span class="text-sm text-gray-400">Nessun cliente assegnato.</span>@endif
                            </div>
                        </div>
                    @empty
                        <p id="nessuna-tappa" class="rounded border border-dashed p-4 text-sm text-gray-500">Nessuna tappa creata.</p>
                    @endforelse
                </div>
            </section>
            <aside class="rounded bg-gray-50 p-4 shadow">
                <h4 class="mb-3 font-semibold">Partecipanti disponibili</h4>
                <div id="clienti-disponibili-tappe" class="space-y-2">
                    @forelse ($clientiDisponibiliTappe as $cliente)
                        <div draggable="true" ondragstart="event.dataTransfer.setData('text/plain', this.dataset.tappaClientId)" data-tappa-client-id="{{ $cliente->id }}" data-tappa-client-name="{{ $cliente->cognome }} {{ $cliente->nome }}" class="cursor-grab rounded border bg-white p-3 text-sm shadow-sm active:cursor-grabbing">{{ $cliente->cognome }} {{ $cliente->nome }}</div>
                    @empty
                        <p id="nessun-cliente-tappa" class="rounded border border-dashed p-3 text-sm text-gray-500">Tutti i partecipanti sono assegnati.</p>
                    @endforelse
                </div>
            </aside>
        </div>
        @endif

        <div x-show="tab === 'partecipanti'" x-cloak class="viaggio-print-panel mx-auto max-w-6xl overflow-hidden rounded bg-white shadow">
            @if ($viaggio->pratiche->flatMap->clienti->isEmpty())
                <p class="p-8 text-center text-gray-500">Nessun partecipante associato a questo viaggio.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50 text-left text-gray-600"><tr><th class="px-4 py-3">Cliente</th><th class="px-4 py-3">Cellulare</th><th class="px-4 py-3">Città di residenza</th><th class="px-4 py-3 text-right">Acconto</th><th class="px-4 py-3 text-right">Saldo</th><th class="px-4 py-3 text-center">Note</th><th class="px-4 py-3 text-right">Pratica</th></tr></thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach ($viaggio->pratiche as $pratica)
                                @foreach ($pratica->clienti as $cliente)
                                    <tr>
                                        <td class="px-4 py-3 font-medium">{{ $cliente->cognome }} {{ $cliente->nome }}</td>
                                        <td class="px-4 py-3"><x-contact-phone :number="$cliente->cellulare" /></td>
                                        <td class="px-4 py-3">{{ $cliente->citta ?: '-' }}</td>
                                        <td class="px-4 py-3 text-right">{{ number_format($pratica->acconto, 2, ',', '.') }} EUR</td>
                                        <td class="px-4 py-3 text-right">{{ number_format($pratica->saldo, 2, ',', '.') }} EUR</td>
                                        <td class="px-4 py-3 text-center"><x-note-tooltip :note="$pratica->note" /></td>
                                        <td class="px-4 py-3 text-right"><a href="{{ route('pratiche.edit', $pratica) }}" class="text-blue-600 hover:underline">Apri pratica #{{ $pratica->id }}</a></td>
                                    </tr>
                                @endforeach
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>

@if ($busTrasporti->isNotEmpty())
    <script>
        let clienteTrascinato = null;
        let clienteSelezionato = null;
        let timeoutFeedbackPosti = null;
        const menuClientiPosti = document.createElement('div');
        menuClientiPosti.className = 'fixed z-50 hidden max-h-[60vh] overflow-y-auto rounded-lg border border-slate-200 bg-white p-2 shadow-xl md:hidden';
        menuClientiPosti.setAttribute('role', 'listbox');
        menuClientiPosti.setAttribute('aria-label', 'Seleziona il cliente per il posto');
        document.body.appendChild(menuClientiPosti);

        const feedbackPosti = document.createElement('p');
        feedbackPosti.className = 'fixed bottom-4 left-1/2 z-50 hidden -translate-x-1/2 rounded px-4 py-3 text-sm shadow-lg';
        feedbackPosti.setAttribute('role', 'status');
        feedbackPosti.setAttribute('aria-live', 'polite');
        document.body.appendChild(feedbackPosti);

        function mostraFeedbackPosti(messaggio, errore = false) {
            feedbackPosti.textContent = messaggio;
            feedbackPosti.className = `fixed bottom-4 left-1/2 z-50 -translate-x-1/2 rounded px-4 py-3 text-sm shadow-lg ${errore ? 'bg-red-700 text-white' : 'bg-slate-800 text-white'}`;
            window.clearTimeout(timeoutFeedbackPosti);
            timeoutFeedbackPosti = window.setTimeout(() => feedbackPosti.classList.add('hidden'), 4000);
        }

        function menuPostiMobile() {
            return window.matchMedia('(max-width: 767px)').matches;
        }

        function chiudiMenuClientiPosti() {
            menuClientiPosti.classList.add('hidden');
            menuClientiPosti.replaceChildren();
        }

        function sincronizzaListaClientiDisponibili() {
            const lista = document.getElementById('clienti-disponibili');
            if (!lista) return;

            const clienti = lista.querySelectorAll('.client-card');
            document.getElementById('nessun-cliente-disponibile')?.remove();
            if (clienti.length === 0) {
                const messaggio = document.createElement('p');
                messaggio.id = 'nessun-cliente-disponibile';
                messaggio.className = 'rounded border border-dashed p-3 text-sm text-gray-500';
                messaggio.textContent = 'Tutti i clienti hanno un posto assegnato.';
                lista.appendChild(messaggio);
            }
        }

        function collegaTrascinamento(cliente) {
            cliente.addEventListener('dragstart', () => {
                clienteTrascinato = cliente.dataset.clientId;
            });
            cliente.addEventListener('click', () => {
                if (!menuPostiMobile()) selezionaCliente(cliente.dataset.clientId);
            });
        }

        function deselezionaCliente() {
            clienteSelezionato = null;
            document.querySelectorAll('[data-client-id]').forEach((cliente) => {
                cliente.classList.remove('ring-2', 'ring-blue-600');
            });
            document.querySelectorAll('[data-bus-selection-status]').forEach((status) => {
                status.textContent = '';
            });
        }

        function selezionaCliente(id) {
            if (clienteSelezionato === String(id)) {
                deselezionaCliente();
                return;
            }
            clienteSelezionato = String(id);

            document.querySelectorAll('[data-client-id]').forEach((cliente) => {
                const selezionato = clienteSelezionato !== null && cliente.dataset.clientId === clienteSelezionato;
                cliente.classList.toggle('ring-2', selezionato);
                cliente.classList.toggle('ring-blue-600', selezionato);
            });

            document.querySelectorAll('[data-bus-selection-status]').forEach((status) => {
                const cliente = clienteSelezionato
                    ? document.querySelector(`[data-client-id="${CSS.escape(clienteSelezionato)}"]`)
                    : null;
                status.textContent = cliente
                    ? `${cliente.dataset.clientName} selezionato. Clicca su un posto disponibile.`
                    : '';
            });
        }

        function creaSchedaCliente(id, nome) {
            const scheda = document.createElement('div');
            scheda.draggable = true;
            scheda.dataset.clientId = id;
            scheda.dataset.clientName = nome;
            scheda.className = 'client-card cursor-grab rounded border bg-white p-3 text-sm shadow-sm active:cursor-grabbing';
            scheda.innerHTML = `<div class="font-medium"></div><div class="text-xs text-gray-500">Nessun posto assegnato</div>`;
            scheda.querySelector('.font-medium').textContent = nome;
            collegaTrascinamento(scheda);
            return scheda;
        }

        function mostraClienteDisponibile(id, nome) {
            const lista = document.getElementById('clienti-disponibili');
            if (!lista) return;
            if (!lista.querySelector(`[data-client-id="${CSS.escape(String(id))}"]`)) lista.appendChild(creaSchedaCliente(id, nome));
            sincronizzaListaClientiDisponibili();
        }

        function aggiornaSedile(sedile, id, nome) {
            sedile.querySelector('[data-seat-client]')?.remove();
            sedile.querySelector('.seat-placeholder')?.remove();
            sedile.style.border = '3px solid #dc2626';
            const cliente = document.createElement('div');
            cliente.draggable = true;
            cliente.dataset.clientId = String(id);
            cliente.dataset.clientName = nome;
            cliente.dataset.seatClient = '';
            cliente.className = 'mt-1 cursor-grab truncate rounded bg-blue-100 px-1 py-1 text-[9px] text-blue-800 active:cursor-grabbing sm:mt-2 sm:text-[11px]';
            cliente.textContent = nome;
            collegaTrascinamento(cliente);
            sedile.appendChild(cliente);
            sedile.setAttribute('aria-label', `Posto ${sedile.dataset.seat}, occupato da ${nome}`);
        }

        function liberaSedileUI(sedile, cliente) {
            cliente.remove();
            sedile.style.border = '3px solid #16a34a';
            sedile.setAttribute('aria-label', `Posto ${sedile.dataset.seat}, disponibile`);
            const placeholder = document.createElement('div');
            placeholder.className = 'seat-placeholder mt-1 h-3 rounded border border-dashed border-slate-300 sm:mt-2 sm:h-5';
            sedile.appendChild(placeholder);
        }

        function apriMenuClientiPosto(sedile) {
            const clienti = [...document.querySelectorAll('#clienti-disponibili .client-card')];
            const occupante = sedile.querySelector('[data-seat-client]');
            menuClientiPosti.replaceChildren();

            if (occupante) {
                const liberaPosto = document.createElement('button');
                liberaPosto.type = 'button';
                liberaPosto.setAttribute('role', 'option');
                liberaPosto.className = 'block w-full rounded px-3 py-2 text-left text-sm font-semibold text-red-700 hover:bg-red-50 focus:bg-red-50 focus:outline-none';
                liberaPosto.textContent = 'Libera posto';
                liberaPosto.addEventListener('click', async () => {
                    const liberato = await rimuoviPosto(null, occupante.dataset.clientId);
                    if (liberato) chiudiMenuClientiPosti();
                });
                menuClientiPosti.appendChild(liberaPosto);

                if (clienti.length > 0) {
                    const separatore = document.createElement('p');
                    separatore.className = 'border-t px-3 pb-1 pt-2 text-xs font-semibold uppercase tracking-wide text-slate-500';
                    separatore.textContent = 'Sostituisci con';
                    menuClientiPosti.appendChild(separatore);
                }
            }

            if (clienti.length === 0 && !occupante) {
                const messaggio = document.createElement('p');
                messaggio.className = 'px-3 py-2 text-sm text-slate-600';
                messaggio.textContent = 'Tutti i clienti hanno già un posto.';
                menuClientiPosti.appendChild(messaggio);
            } else {
                clienti.forEach((cliente) => {
                    const opzione = document.createElement('button');
                    opzione.type = 'button';
                    opzione.setAttribute('role', 'option');
                    opzione.className = 'block w-full rounded px-3 py-2 text-left text-sm hover:bg-blue-50 focus:bg-blue-50 focus:outline-none';
                    opzione.textContent = cliente.dataset.clientName;
                    opzione.addEventListener('click', async () => {
                        const assegnata = await salvaAssegnazionePosto(
                            cliente.dataset.clientId,
                            sedile.dataset.bus,
                            sedile.dataset.seat
                        );
                        if (assegnata) chiudiMenuClientiPosti();
                    });
                    menuClientiPosti.appendChild(opzione);
                });
            }

            menuClientiPosti.classList.remove('hidden');
            const rettangolo = sedile.getBoundingClientRect();
            const larghezza = Math.min(288, window.innerWidth - 24);
            menuClientiPosti.style.width = `${larghezza}px`;
            const altezza = menuClientiPosti.getBoundingClientRect().height;
            const sinistra = Math.max(12, Math.min(rettangolo.left, window.innerWidth - larghezza - 12));
            const dallAlto = rettangolo.bottom + altezza + 8 <= window.innerHeight
                ? rettangolo.bottom + 4
                : Math.max(8, rettangolo.top - altezza - 4);
            menuClientiPosti.style.left = `${sinistra}px`;
            menuClientiPosti.style.top = `${dallAlto}px`;
        }

        async function salvaAssegnazionePosto(clienteId, bus, posto) {
            const response = await fetch('{{ route('viaggi.posti.store', $viaggio) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ cliente_id: clienteId, bus, posto }),
            }).catch((errore) => {
                console.error('Assegnazione posto bus non riuscita.', errore);
                mostraFeedbackPosti('Impossibile salvare il posto. Verifica la connessione e riprova.', true);
                return null;
            });

            if (!response) return false;
            if (!response.ok) {
                console.error('Assegnazione posto bus rifiutata.', response.status);
                mostraFeedbackPosti('Impossibile assegnare il posto. Aggiorna la pagina e riprova.', true);
                return false;
            }

            const sorgente = document.querySelector(`[data-client-id="${CSS.escape(String(clienteId))}"]`);
            const sedileDestinazione = document.querySelector(`[data-bus="${CSS.escape(String(bus))}"][data-seat="${CSS.escape(String(posto))}"]`);
            if (!sorgente || !sedileDestinazione) {
                window.location.reload();
                return true;
            }

            const sedileOrigine = sorgente.closest('[data-seat]');
            const occupante = sedileDestinazione.querySelector('[data-seat-client]');
            const nomeSorgente = sorgente.dataset.clientName;

            if (occupante && occupante.dataset.clientId !== String(clienteId)) {
                mostraClienteDisponibile(occupante.dataset.clientId, occupante.dataset.clientName);
            }
            if (sedileOrigine && sedileOrigine !== sedileDestinazione) {
                liberaSedileUI(sedileOrigine, sorgente);
            } else if (!sedileOrigine) {
                sorgente.remove();
            }
            aggiornaSedile(sedileDestinazione, clienteId, nomeSorgente);
            sincronizzaListaClientiDisponibili();
            clienteTrascinato = null;
            deselezionaCliente();

            return true;
        }

        async function assegnaPosto(event, bus, posto) {
            event.preventDefault();
            const idCliente = event.dataTransfer?.getData('text/plain') || clienteTrascinato;
            if (!idCliente) return;

            await salvaAssegnazionePosto(idCliente, bus, posto);
            clienteTrascinato = null;
        }

        async function rimuoviPosto(event, idCliente = null) {
            event?.preventDefault();
            const idDaRimuovere = idCliente || event?.dataTransfer?.getData('text/plain') || clienteTrascinato;
            if (!idDaRimuovere) return;

            const sorgente = document.querySelector(`[data-client-id="${CSS.escape(String(idDaRimuovere))}"]`);
            const sedileOrigine = sorgente?.closest('[data-seat]');
            if (!sorgente || !sedileOrigine) return;

            const response = await fetch('{{ route('viaggi.posti.store', $viaggio) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ cliente_id: idDaRimuovere, bus: sedileOrigine.dataset.bus, posto: null }),
            }).catch((errore) => {
                console.error('Rilascio posto bus non riuscito.', errore);
                mostraFeedbackPosti('Impossibile liberare il posto. Verifica la connessione e riprova.', true);
                return null;
            });

            if (!response) return false;
            if (!response.ok) {
                console.error('Rilascio posto bus rifiutato.', response.status);
                mostraFeedbackPosti('Impossibile liberare il posto. Aggiorna la pagina e riprova.', true);
                return false;
            }

            const nome = sorgente.dataset.clientName;
            liberaSedileUI(sedileOrigine, sorgente);
            mostraClienteDisponibile(idDaRimuovere, nome);
            clienteTrascinato = null;
            deselezionaCliente();

            return true;
        }

        document.querySelectorAll('[data-client-id]').forEach(collegaTrascinamento);
        document.querySelectorAll('[data-seat]').forEach((sedile) => {
            sedile.addEventListener('dragover', (event) => event.preventDefault());
            sedile.addEventListener('drop', (event) => assegnaPosto(event, sedile.dataset.bus, sedile.dataset.seat));
            sedile.addEventListener('click', async () => {
                const occupante = sedile.querySelector('[data-seat-client]');
                if (menuPostiMobile()) {
                    apriMenuClientiPosto(sedile);
                    return;
                }

                chiudiMenuClientiPosti();
                if (!occupante && clienteSelezionato) {
                    await salvaAssegnazionePosto(clienteSelezionato, sedile.dataset.bus, sedile.dataset.seat);
                }
            });
            sedile.addEventListener('dblclick', (event) => {
                const occupante = sedile.querySelector('[data-seat-client]');
                if (occupante) rimuoviPosto(event, occupante.dataset.clientId);
            });
            sedile.addEventListener('keydown', (event) => {
                if (event.key === 'Enter' || event.key === ' ') {
                    event.preventDefault();
                    sedile.click();
                }
            });
        });

        document.addEventListener('click', (event) => {
            if (!menuClientiPosti.contains(event.target) && !event.target.closest('[data-seat]')) {
                chiudiMenuClientiPosti();
            }
        });
        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') chiudiMenuClientiPosti();
        });
        window.addEventListener('scroll', chiudiMenuClientiPosti, true);
        window.addEventListener('resize', chiudiMenuClientiPosti);
        sincronizzaListaClientiDisponibili();
    </script>
@endif

<script>
    const csrfTokenTappe = document.querySelector('meta[name="csrf-token"]')?.content;
    const formTappa = document.getElementById('form-tappa');
    const listaTappe = document.getElementById('tappe-raccolta-lista');
    const listaClientiTappe = document.getElementById('clienti-disponibili-tappe');
    let clienteTappaTrascinato = null;

    document.querySelectorAll('[data-tappa-client-id]').forEach((cliente) => {
        cliente.addEventListener('dragstart', (event) => {
            clienteTappaTrascinato = cliente.dataset.tappaClientId;
            event.dataTransfer.setData('text/plain', clienteTappaTrascinato);
            event.dataTransfer.effectAllowed = 'move';
        });
    });

    document.getElementById('mostra-form-tappa')?.addEventListener('click', () => {
        formTappa.classList.toggle('hidden');
        if (!formTappa.classList.contains('hidden')) document.getElementById('tappa-nome').focus();
    });

    formTappa?.addEventListener('submit', async (event) => {
        event.preventDefault();
        const response = await fetch(formTappa.dataset.url, {
            method: 'POST',
            headers: {'X-CSRF-TOKEN': csrfTokenTappe, 'Accept': 'application/json', 'Content-Type': 'application/json'},
            body: JSON.stringify({nome: document.getElementById('tappa-nome').value, orario: document.getElementById('tappa-orario').value}),
        });
        if (!response.ok) return;
        const tappa = await response.json();
        document.getElementById('nessuna-tappa')?.remove();
        listaTappe.insertAdjacentHTML('beforeend', `<div class="tappa-card rounded border p-4" data-tappa-id="${tappa.id}" data-tappa-url="${formTappa.dataset.url}/${tappa.id}/clienti"><div class="mb-3 flex items-center justify-between"><h4 class="font-semibold">${escapeHtmlTappa(tappa.nome)} <span class="font-normal text-gray-500">- ${tappa.orario}</span></h4><span class="text-xs text-gray-500">Trascina qui i clienti</span></div><div class="tappa-clienti min-h-16 space-y-2 rounded border-2 border-dashed border-blue-200 p-3" data-drop-tappa ondragover="event.preventDefault()" ondrop="assegnaTappa(event, this.closest('[data-tappa-id]').dataset.tappaId, this.closest('[data-tappa-id]').dataset.tappaUrl)"><span class="text-sm text-gray-400">Nessun cliente assegnato.</span></div></div>`);
        formTappa.reset();
        formTappa.classList.add('hidden');
    });

    function escapeHtmlTappa(value) {
        const element = document.createElement('span');
        element.textContent = value;
        return element.innerHTML;
    }

    function aggiungiClienteDisponibileTappa(id, nome) {
        document.getElementById('nessun-cliente-tappa')?.remove();
        if (listaClientiTappe.querySelector(`[data-tappa-client-id="${CSS.escape(String(id))}"]`)) return;
        const cliente = document.createElement('div');
        cliente.draggable = true;
        cliente.dataset.tappaClientId = id;
        cliente.dataset.tappaClientName = nome;
        cliente.className = 'cursor-grab rounded border bg-white p-3 text-sm shadow-sm active:cursor-grabbing';
        cliente.ondragstart = (event) => event.dataTransfer.setData('text/plain', String(id));
        cliente.textContent = nome;
        cliente.addEventListener('dragstart', (event) => {
            clienteTappaTrascinato = String(id);
            event.dataTransfer.setData('text/plain', clienteTappaTrascinato);
            event.dataTransfer.effectAllowed = 'move';
        });
        listaClientiTappe.appendChild(cliente);
    }

    async function assegnaTappa(event, tappaId, url) {
        event.preventDefault();
        clienteTappaTrascinato = event.dataTransfer?.getData('text/plain') || clienteTappaTrascinato;
        if (!clienteTappaTrascinato) return;
        const lista = event.currentTarget;
        const cliente = document.querySelector(`[data-tappa-client-id="${CSS.escape(String(clienteTappaTrascinato))}"]`);
        const response = await fetch(url, {method: 'POST', headers: {'X-CSRF-TOKEN': csrfTokenTappe, 'Accept': 'application/json', 'Content-Type': 'application/json'}, body: JSON.stringify({cliente_id: clienteTappaTrascinato})});
        if (response.ok && cliente) {
            lista.querySelector('[data-tappa-empty]')?.remove();
            lista.querySelector('span.text-sm.text-gray-400')?.remove();
            const card = document.createElement('div');
            card.className = 'flex items-center justify-between rounded bg-blue-50 px-3 py-2 text-sm';
            card.dataset.tappaCliente = clienteTappaTrascinato;
            card.dataset.tappaClienteName = cliente.dataset.tappaClientName;
            card.innerHTML = `<span>${escapeHtmlTappa(cliente.dataset.tappaClientName)}</span><button type="button" title="Rimuovi dalla tappa" aria-label="Rimuovi dalla tappa" class="text-red-600">&#10005;</button>`;
            card.querySelector('button').addEventListener('click', (clickEvent) => rimuoviDaTappa(clickEvent, tappaId, card.dataset.tappaCliente));
            lista.appendChild(card);
            cliente.remove();
        }
        clienteTappaTrascinato = null;
    }

    async function rimuoviDaTappa(event, tappaId, clienteId) {
        event.preventDefault();
        event.stopPropagation();
        const card = event.currentTarget?.closest('[data-tappa-cliente]')
            ?? document.querySelector(`[data-tappa-cliente="${CSS.escape(String(clienteId))}"]`);
        const url = `{{ url('/viaggi/' . $viaggio->id . '/tappe-raccolta') }}/${tappaId}/clienti/${clienteId}`;
        const response = await fetch(url, {method: 'DELETE', headers: {'X-CSRF-TOKEN': csrfTokenTappe, 'Accept': 'application/json'}});
        if (response.ok) {
            const nome = card?.dataset.tappaClienteName ?? '';
            if (nome) aggiungiClienteDisponibileTappa(clienteId, nome);
            card?.remove();
            const contenitore = document.querySelector(`[data-tappa-id="${CSS.escape(String(tappaId))}"] [data-drop-tappa]`);
            if (contenitore && !contenitore.querySelector('[data-tappa-cliente]')) {
                contenitore.insertAdjacentHTML('beforeend', '<span class="tappa-empty-message text-sm text-gray-400">Nessun cliente assegnato.</span>');
            }
        }
    }
</script>