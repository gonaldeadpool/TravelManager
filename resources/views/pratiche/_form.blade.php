@php
    $bozza = $bozza ?? [];
    $dataAcconto = old('data_acconto', optional($pratica->data_acconto)->format('Y-m-d') ?? ($bozza['data_acconto'] ?? ''));
    $dataSaldo = old('data_saldo', optional($pratica->data_saldo)->format('Y-m-d') ?? ($bozza['data_saldo'] ?? ''));
    $gratuiti = old('gratuiti', $pratica->exists ? $pratica->clienti->filter(fn ($cliente) => $cliente->pivot->gratuito)->pluck('id')->all() : ($bozza['gratuiti'] ?? []));
    $ridotti = old('ridotti', $pratica->exists ? $pratica->clienti->filter(fn ($cliente) => $cliente->pivot->ridotto)->pluck('id')->all() : ($bozza['ridotti'] ?? []));
@endphp

@if ($errors->any())
    <div class="mb-4 rounded border border-red-400 bg-red-100 px-4 py-3 text-red-700"><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
@endif
@if (session('success'))
    <div class="mb-4 rounded border border-green-400 bg-green-100 px-4 py-3 text-green-700">{{ session('success') }}</div>
@endif

<div x-data="praticaForm()" class="space-y-6">
    <div class="rounded bg-white p-6 shadow">
        <div class="mb-4 flex items-center justify-between gap-4"><h3 class="text-lg font-semibold">Dati pratica</h3><a href="{{ route('pratiche.index') }}" class="text-sm text-blue-600 hover:underline">Torna all'elenco</a></div>
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <div>
                <label for="viaggio_id" class="mb-1 block font-medium">Viaggio</label>
                @if ($pratica->exists)
                    <input type="hidden" name="viaggio_id" value="{{ old('viaggio_id', $pratica->viaggio_id) }}">
                @endif

                <select
                    id="viaggio_id"
                    name="viaggio_id"
                    x-model="viaggioId"
                    x-ref="viaggioSelect"
                    @change="aggiornaViaggio()"
                    required
                    @disabled($pratica->exists)
                    class="w-full rounded border p-2 disabled:cursor-not-allowed disabled:bg-gray-100 disabled:text-gray-600"
                >
                    <option value="">Seleziona un viaggio</option>
                    @foreach ($viaggi as $viaggio)
                        <option value="{{ $viaggio->id }}" data-prezzo="{{ $viaggio->prezzo }}" data-quota-ridotto="{{ $viaggio->quota_ridotto }}" data-quota-fissa="{{ $viaggio->quota_fissa }}" data-tipologia="{{ $viaggio->tipologia }}" data-prezzi-cabine="{{ json_encode($viaggio->prezzi_cabine ?? []) }}" @selected(old('viaggio_id', $pratica->viaggio_id ?? ($bozza['viaggio_id'] ?? null)) == $viaggio->id)>
                            {{ $viaggio->nome }}
                            @if ($viaggio->prezzo !== null)
                                - {{ number_format($viaggio->prezzo, 2, ',', '.') }} EUR
                            @endif
                        </option>
                    @endforeach
                </select>
            </div>
            <div x-show="tipologiaViaggio === 'crociera'" x-cloak>
                <label for="cabina" class="mb-1 block font-medium">Cabina *</label>
                <select id="cabina" name="cabina" x-model="cabina" @change="aggiornaTotaleQuote()" :required="tipologiaViaggio === 'crociera'" :disabled="tipologiaViaggio !== 'crociera'" class="w-full rounded border p-2 disabled:bg-gray-100">
                    <option value="">Seleziona una cabina</option>
                    <template x-for="opzione in prezziCabine" :key="opzione.tipo">
                        <option :value="opzione.tipo" x-text="etichettaCabina(opzione)"></option>
                    </template>
                </select>
            </div>
        </div>
    </div>

    @if ($pratica->exists)
        <div class="rounded bg-white p-6 shadow">
            <div class="mb-4 flex items-center justify-between gap-4">
                <h3 class="text-lg font-semibold">Clienti selezionati</h3>
                <a href="{{ route('pratiche.clienti.select', $pratica) }}" class="rounded bg-blue-600 px-4 py-2 text-sm text-white">Aggiungi clienti</a>
            </div>
            <div class="space-y-2">
                @forelse ($pratica->clienti as $cliente)
                    <div class="grid grid-cols-[auto_auto_auto_minmax(0,1fr)] items-center gap-2 rounded border p-2">
                        <label class="flex items-center gap-1 whitespace-nowrap text-xs sm:gap-2 sm:text-sm"><input type="checkbox" name="gratuiti[]" value="{{ $cliente->id }}" x-model="gratuiti" @change="impostaTariffa({{ $cliente->id }}, 'gratuito')" @checked(in_array($cliente->id, $gratuiti)) class="rounded border-gray-300 text-blue-600"> Gratuito</label>
                        <label class="flex items-center gap-1 whitespace-nowrap text-xs sm:gap-2 sm:text-sm"><input type="checkbox" name="ridotti[]" value="{{ $cliente->id }}" x-model="ridotti" @change="impostaTariffa({{ $cliente->id }}, 'ridotto')" @checked(in_array($cliente->id, $ridotti)) class="rounded border-gray-300 text-blue-600"> Ridotto</label>
                        <button type="submit" form="rimuovi-cliente-{{ $cliente->id }}" title="Deseleziona cliente" aria-label="Deseleziona {{ $cliente->cognome }} {{ $cliente->nome }}" class="inline-flex h-8 w-8 items-center justify-center rounded text-red-600 hover:bg-red-50">
                            <svg aria-hidden="true" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M3 6h18" />
                                <path d="M8 6V4h8v2" />
                                <path d="M19 6l-1 14H6L5 6" />
                                <path d="M10 11v5M14 11v5" />
                            </svg>
                        </button>
                        <span class="min-w-0 truncate">{{ $cliente->cognome }} {{ $cliente->nome }}</span>
                    </div>
                @empty
                    <p class="text-sm text-gray-500">Nessun cliente selezionato.</p>
                @endforelse
            </div>
        </div>
    @else
        <div class="rounded bg-white p-6 shadow">
            <div class="mb-4 flex items-center justify-between gap-4">
                <h3 class="text-lg font-semibold">Clienti selezionati</h3>
                <button type="submit" formaction="{{ route('pratiche.creazione.bozza') }}" formmethod="POST" formnovalidate class="rounded bg-blue-600 px-4 py-2 text-sm text-white">Seleziona clienti</button>
            </div>
            <div class="space-y-2">
                @forelse ($pratica->clienti as $cliente)
                    <input type="hidden" name="clienti[]" value="{{ $cliente->id }}">
                    <div class="grid grid-cols-[auto_auto_auto_minmax(0,1fr)] items-center gap-2 rounded border p-2">
                        <label class="flex items-center gap-1 whitespace-nowrap text-xs sm:gap-2 sm:text-sm"><input type="checkbox" name="gratuiti[]" value="{{ $cliente->id }}" x-model="gratuiti" @change="impostaTariffa({{ $cliente->id }}, 'gratuito')" @checked(in_array($cliente->id, $gratuiti)) class="rounded border-gray-300 text-blue-600"> Gratuito</label>
                        <label class="flex items-center gap-1 whitespace-nowrap text-xs sm:gap-2 sm:text-sm"><input type="checkbox" name="ridotti[]" value="{{ $cliente->id }}" x-model="ridotti" @change="impostaTariffa({{ $cliente->id }}, 'ridotto')" @checked(in_array($cliente->id, $ridotti)) class="rounded border-gray-300 text-blue-600"> Ridotto</label>
                        <button type="button" @click="rimuoviCliente({{ $cliente->id }})" title="Deseleziona cliente" aria-label="Deseleziona {{ $cliente->cognome }} {{ $cliente->nome }}" class="inline-flex h-8 w-8 items-center justify-center rounded text-red-600 hover:bg-red-50">
                            <svg aria-hidden="true" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M3 6h18" />
                                <path d="M8 6V4h8v2" />
                                <path d="M19 6l-1 14H6L5 6" />
                                <path d="M10 11v5M14 11v5" />
                            </svg>
                        </button>
                        <span class="min-w-0 truncate">{{ $cliente->cognome }} {{ $cliente->nome }}</span>
                    </div>
                @empty
                    <p class="text-sm text-gray-500">Seleziona almeno un cliente prima di creare la pratica.</p>
                @endforelse
            </div>
        </div>
    @endif

    <section class="rounded bg-white p-6 shadow">
        <h3 class="mb-4 text-lg font-semibold">Riepilogo importi</h3>
        <div class="grid grid-cols-2 gap-3">
            <div class="min-w-0 space-y-1">
                <label for="totale_quote" class="font-medium">Totale quote</label>
                <div class="relative"><input id="totale_quote" type="text" inputmode="decimal" :value="formattaImporto(totaleQuote)" placeholder="0,00" readonly class="w-full rounded border bg-gray-50 p-2 pr-12 text-right"><input type="hidden" name="totale_quote" :value="numeroImporto(totaleQuote)"><span class="absolute right-3 top-2 text-sm text-gray-500">EUR</span></div>
            </div>
            <div class="min-w-0 space-y-1">
                <label for="assicurazione_annullamento" class="font-medium">Assicurazione annullamento</label>
                <div class="relative"><input id="assicurazione_annullamento" type="text" inputmode="decimal" x-model="assicurazioneAnnullamento" @blur="assicurazioneAnnullamento = formattaImporto(assicurazioneAnnullamento)" placeholder="0,00" class="w-full rounded border p-2 pr-12 text-right"><input type="hidden" name="assicurazione_annullamento" :value="numeroImporto(assicurazioneAnnullamento)"><span class="absolute right-3 top-2 text-sm text-gray-500">EUR</span></div>
            </div>
            <div class="min-w-0 space-y-1">
                <label for="supplemento_singola" class="font-medium">Supplemento singola</label>
                <div class="relative"><input id="supplemento_singola" type="text" inputmode="decimal" x-model="supplementoSingola" @blur="supplementoSingola = formattaImporto(supplementoSingola)" placeholder="0,00" class="w-full rounded border p-2 pr-12 text-right"><input type="hidden" name="supplemento_singola" :value="numeroImporto(supplementoSingola)"><span class="absolute right-3 top-2 text-sm text-gray-500">EUR</span></div>
            </div>
            <div class="min-w-0 space-y-1">
                <label for="sconto" class="font-medium">Sconto</label>
                <div class="relative"><input id="sconto" type="text" inputmode="decimal" x-model="sconto" @blur="sconto = formattaImporto(sconto)" placeholder="0,00" class="w-full rounded border p-2 pr-12 text-right"><input type="hidden" name="sconto" :value="numeroImporto(sconto)"><span class="absolute right-3 top-2 text-sm text-gray-500">EUR</span></div>
            </div>
            <div class="col-span-2 min-w-0 space-y-1 border-t pt-3">
                <label for="totale" class="font-semibold">Totale</label>
                <div class="relative"><input id="totale" type="text" inputmode="decimal" :value="formattaImporto(totale)" placeholder="0,00" readonly class="w-full rounded border bg-gray-50 p-2 pr-12 text-right font-semibold"><input type="hidden" name="totale" :value="numeroImporto(totale)"><span class="absolute right-3 top-2 text-sm text-gray-500">EUR</span></div>
            </div>
        </div>
    </section>

    <div class="rounded bg-white p-6 shadow">
        <h3 class="mb-4 text-lg font-semibold">Pagamenti</h3>
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-2">
            <div><label for="acconto" class="mb-1 block">Acconto</label><div class="relative"><input id="acconto" type="text" inputmode="decimal" x-model="acconto" @blur="acconto = formattaImporto(acconto)" placeholder="0,00" class="w-full rounded border p-2 pr-12 text-right"><input type="hidden" name="acconto" :value="numeroImporto(acconto)"><span class="absolute right-3 top-2 text-sm text-gray-500">EUR</span></div></div>
            <div><label for="data_acconto" class="mb-1 block">Data acconto</label><input id="data_acconto" type="date" name="data_acconto" x-model="dataAcconto" class="w-full rounded border p-2"></div>
            <div><label for="saldo" class="mb-1 block">Saldo</label><div class="relative"><input id="saldo" type="text" inputmode="decimal" x-model="saldo" @blur="saldo = formattaImporto(saldo)" placeholder="0,00" class="w-full rounded border p-2 pr-12 text-right"><input type="hidden" name="saldo" :value="numeroImporto(saldo)"><span class="absolute right-3 top-2 text-sm text-gray-500">EUR</span></div></div>
            <div><label for="data_saldo" class="mb-1 block">Data saldo</label><input id="data_saldo" type="date" name="data_saldo" x-model="dataSaldo" class="w-full rounded border p-2"></div>
            <div><label class="mb-1 block">Residuo</label><div class="rounded border bg-gray-50 p-2 text-right" x-text="formatoEuro(residuo)"></div></div>
        </div>
    </div>

    <div class="rounded bg-white p-6 shadow"><label for="note" class="mb-1 block text-lg font-semibold">Note e richieste del cliente</label><textarea id="note" name="note" rows="4" class="w-full rounded border p-2">{{ old('note', $pratica->note ?? ($bozza['note'] ?? '')) }}</textarea></div>

    <div class="flex items-center justify-between"><button type="submit" class="rounded bg-blue-600 px-4 py-2 text-white">{{ $pratica->exists ? 'Salva modifiche' : 'Crea pratica' }}</button></div>
</div>

<script>
    function praticaForm() {
        return {
            viaggioId: @js(old('viaggio_id', $pratica->viaggio_id ?? ($bozza['viaggio_id'] ?? ''))),
            cabina: @js(old('cabina', $pratica->cabina ?? ($bozza['cabina'] ?? ''))),
            tipologiaViaggio: '',
            prezziCabine: [],
            quotaViaggio: 0,
            quotaRidottoViaggio: null,
            quotaFissaViaggio: null,
            clientiIds: @js($pratica->clienti->pluck('id')->map(fn ($id) => (int) $id)->values()),
            totaleQuote: @js(old('totale_quote', $pratica->exists ? $pratica->totale_quote : ($bozza['totale_quote'] ?? 0))),
            sconto: @js(old('sconto', $pratica->exists ? $pratica->sconto : ($bozza['sconto'] ?? 0))),
            assicurazioneAnnullamento: @js(old('assicurazione_annullamento', $pratica->exists ? $pratica->assicurazione_annullamento : ($bozza['assicurazione_annullamento'] ?? 0))),
            supplementoSingola: @js(old('supplemento_singola', $pratica->exists ? $pratica->supplemento_singola : ($bozza['supplemento_singola'] ?? 0))),
            acconto: @js(old('acconto', $pratica->exists ? $pratica->acconto : ($bozza['acconto'] ?? 0))),
            saldo: @js(old('saldo', $pratica->exists ? $pratica->saldo : ($bozza['saldo'] ?? 0))),
            dataAcconto: @js($dataAcconto),
            dataSaldo: @js($dataSaldo),
            gratuiti: @js($gratuiti),
            ridotti: @js($ridotti),
            init() {
                this.ridotti = this.ridotti.filter((id) => !this.gratuiti.some((gratuitoId) => Number(gratuitoId) === Number(id)));
                this.$watch('acconto', (valore) => { if (this.valoreImporto(valore) > 0 && !this.dataAcconto) this.dataAcconto = new Date().toISOString().slice(0, 10); });
                this.$watch('saldo', (valore) => { if (this.valoreImporto(valore) > 0 && !this.dataSaldo) this.dataSaldo = new Date().toISOString().slice(0, 10); });
                this.assicurazioneAnnullamento = this.formattaImporto(this.assicurazioneAnnullamento);
                this.supplementoSingola = this.formattaImporto(this.supplementoSingola);
                this.sconto = this.formattaImporto(this.sconto);
                this.acconto = this.formattaImporto(this.acconto);
                this.saldo = this.formattaImporto(this.saldo);
                this.aggiornaViaggio();
            },
            get totale() {
                return Math.max(0, this.valoreImporto(this.totaleQuote)
                    + this.valoreImporto(this.assicurazioneAnnullamento)
                    + this.valoreImporto(this.supplementoSingola)
                    - this.valoreImporto(this.sconto)).toFixed(2);
            },
            get residuo() { return this.valoreImporto(this.totale) - this.valoreImporto(this.acconto) - this.valoreImporto(this.saldo); },
            aggiornaViaggio() {
                const opzione = this.$refs.viaggioSelect.selectedOptions[0];
                this.tipologiaViaggio = opzione?.dataset.tipologia || '';
                this.quotaViaggio = Number(opzione?.dataset.prezzo) || 0;
                const quotaRidotta = opzione?.dataset.quotaRidotto;
                this.quotaRidottoViaggio = quotaRidotta === undefined || quotaRidotta === '' ? null : Number(quotaRidotta);
                const quotaFissa = opzione?.dataset.quotaFissa;
                this.quotaFissaViaggio = quotaFissa === undefined || quotaFissa === '' ? null : Number(quotaFissa);
                try {
                    this.prezziCabine = JSON.parse(opzione?.dataset.prezziCabine || '[]');
                } catch {
                    this.prezziCabine = [];
                }
                if (!this.prezziCabine.some((cabina) => cabina.tipo === this.cabina)) this.cabina = '';
                this.aggiornaTotaleQuote();
            },
            aggiornaTotaleQuote() {
                const opzioneCabina = this.prezziCabine.find((cabina) => cabina.tipo === this.cabina);
                const quotaBase = this.tipologiaViaggio === 'crociera'
                    ? (Number(opzioneCabina?.prezzo) || 0)
                    : this.quotaViaggio;
                const quotaRidotta = this.tipologiaViaggio === 'crociera' && this.quotaFissaViaggio !== null
                    ? this.quotaFissaViaggio
                    : this.quotaRidottoViaggio;
                this.totaleQuote = this.clientiIds.reduce((totale, id) => {
                    if (this.gratuiti.some((gratuitoId) => Number(gratuitoId) === id)) return totale;
                    if (this.ridotti.some((ridottoId) => Number(ridottoId) === id) && quotaRidotta !== null) {
                        return totale + quotaRidotta;
                    }
                    return totale + quotaBase;
                }, 0).toFixed(2);
            },
            impostaTariffa(id, tariffa) {
                if (tariffa === 'gratuito') {
                    this.ridotti = this.ridotti.filter((ridottoId) => Number(ridottoId) !== id);
                } else {
                    this.gratuiti = this.gratuiti.filter((gratuitoId) => Number(gratuitoId) !== id);
                }
                this.aggiornaTotaleQuote();
            },
            rimuoviCliente(id) {
                document.querySelector(`input[name="clienti[]"][value="${id}"]`)?.remove();
                document.querySelector(`input[name="gratuiti[]"][value="${id}"]`)?.closest('div.rounded.border')?.remove();
                this.clientiIds = this.clientiIds.filter((clienteId) => clienteId !== id);
                this.gratuiti = this.gratuiti.filter((clienteId) => Number(clienteId) !== id);
                this.ridotti = this.ridotti.filter((clienteId) => Number(clienteId) !== id);
                this.aggiornaTotaleQuote();
            },
            etichettaCabina(cabina) {
                const nome = ({ interna: 'Cabina interna', vista_mare: 'Cabina vista mare', balcone: 'Cabina con balcone' })[cabina.tipo] || cabina.tipo;
                const prezzo = new Intl.NumberFormat('it-IT', { maximumFractionDigits: 2 }).format(Number(cabina.prezzo) || 0);
                return `${nome} - ${prezzo}€`;
            },
            formatoEuro(valore) { return new Intl.NumberFormat('it-IT', { style: 'currency', currency: 'EUR' }).format(valore); },
            numeroImporto(valore) {
                const testo = String(valore ?? '').trim().replace(/\s/g, '');
                if (!testo) return '';
                const normalizzato = testo.includes(',') ? testo.replace(/\./g, '').replace(',', '.') : testo;
                const numero = Number(normalizzato);
                return Number.isFinite(numero) ? numero.toFixed(2) : '';
            },
            formattaImporto(valore) {
                const numero = this.numeroImporto(valore);
                return numero ? numero.replace('.', ',') : '';
            },
            valoreImporto(valore) { return Number(this.numeroImporto(valore)) || 0; },
        }
    }
</script>