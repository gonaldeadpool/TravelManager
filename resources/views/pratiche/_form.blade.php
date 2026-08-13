@php
    $bozza = $bozza ?? [];
    $numeroClienti = $pratica->clienti->count();
    $dataAcconto = old('data_acconto', optional($pratica->data_acconto)->format('Y-m-d') ?? ($bozza['data_acconto'] ?? ''));
    $dataSaldo = old('data_saldo', optional($pratica->data_saldo)->format('Y-m-d') ?? ($bozza['data_saldo'] ?? ''));
    $gratuiti = old('gratuiti', $pratica->exists ? $pratica->clienti->filter(fn ($cliente) => $cliente->pivot->gratuito)->pluck('id')->all() : ($bozza['gratuiti'] ?? []));
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
                <select id="viaggio_id" name="viaggio_id" x-model="viaggioId" @change="ricalcolaTotale()" required class="w-full rounded border p-2">
                    <option value="">Seleziona un viaggio</option>
                    @foreach ($viaggi as $viaggio)
                        <option value="{{ $viaggio->id }}" data-prezzo="{{ $viaggio->prezzo }}" @selected(old('viaggio_id', $pratica->viaggio_id ?? ($bozza['viaggio_id'] ?? null)) == $viaggio->id)>
                            {{ $viaggio->nome }}
                            @if ($viaggio->prezzo !== null)
                                - {{ number_format($viaggio->prezzo, 2, ',', '.') }} EUR
                            @endif
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="totale" class="mb-1 block font-medium">Totale</label>
                <div class="flex gap-2"><input id="totale" type="number" min="0" step="0.01" name="totale" x-model="totale" @input="totaleForzato = true" required class="w-full rounded border p-2"><button type="button" @click="totaleForzato = false; ricalcolaTotale()" class="rounded border px-3 text-sm text-gray-700">Ricalcola</button></div>
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
                    <div class="grid grid-cols-[auto_1fr_auto] items-center gap-3 rounded border p-3">
                        <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="gratuiti[]" value="{{ $cliente->id }}" x-model="gratuiti" @change="ricalcolaTotale(true)" @checked(in_array($cliente->id, $gratuiti)) class="rounded border-gray-300 text-blue-600"> Gratuito</label>
                        <span>{{ $cliente->cognome }} {{ $cliente->nome }}</span>
                        <button type="submit" form="rimuovi-cliente-{{ $cliente->id }}" title="Rimuovi cliente" aria-label="Rimuovi cliente" class="inline-flex h-8 w-8 items-center justify-center rounded text-red-600 hover:bg-red-50">
                            <svg aria-hidden="true" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M3 6h18" />
                                <path d="M8 6V4h8v2" />
                                <path d="M19 6l-1 14H6L5 6" />
                                <path d="M10 11v5M14 11v5" />
                            </svg>
                        </button>
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
                    <div class="grid grid-cols-[auto_1fr_auto] items-center gap-3 rounded border p-3">
                        <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="gratuiti[]" value="{{ $cliente->id }}" x-model="gratuiti" @change="ricalcolaTotale(true)" @checked(in_array($cliente->id, $gratuiti)) class="rounded border-gray-300 text-blue-600"> Gratuito</label>
                        <span>{{ $cliente->cognome }} {{ $cliente->nome }}</span>
                        <button type="button" @click="rimuoviCliente({{ $cliente->id }})" title="Rimuovi cliente" aria-label="Rimuovi cliente" class="inline-flex h-8 w-8 items-center justify-center rounded text-red-600 hover:bg-red-50">
                            <svg aria-hidden="true" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M3 6h18" />
                                <path d="M8 6V4h8v2" />
                                <path d="M19 6l-1 14H6L5 6" />
                                <path d="M10 11v5M14 11v5" />
                            </svg>
                        </button>
                    </div>
                @empty
                    <p class="text-sm text-gray-500">Seleziona almeno un cliente prima di creare la pratica.</p>
                @endforelse
            </div>
        </div>
    @endif

    <div class="rounded bg-white p-6 shadow">
        <h3 class="mb-4 text-lg font-semibold">Pagamenti</h3>
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
            <div><label for="acconto" class="mb-1 block">Acconto</label><input id="acconto" type="number" min="0" step="0.01" name="acconto" x-model="acconto" class="w-full rounded border p-2"></div>
            <div><label for="data_acconto" class="mb-1 block">Data acconto</label><input id="data_acconto" type="date" name="data_acconto" x-model="dataAcconto" class="w-full rounded border p-2"></div>
            <div><label for="saldo" class="mb-1 block">Saldo</label><input id="saldo" type="number" min="0" step="0.01" name="saldo" x-model="saldo" class="w-full rounded border p-2"></div>
            <div><label for="data_saldo" class="mb-1 block">Data saldo</label><input id="data_saldo" type="date" name="data_saldo" x-model="dataSaldo" class="w-full rounded border p-2"></div>
            <div><label class="mb-1 block">Residuo</label><div class="rounded border bg-gray-50 p-2" x-text="formatoEuro(residuo)"></div></div>
        </div>
    </div>

    <div class="rounded bg-white p-6 shadow"><label for="note" class="mb-1 block text-lg font-semibold">Note e richieste del cliente</label><textarea id="note" name="note" rows="4" class="w-full rounded border p-2">{{ old('note', $pratica->note ?? ($bozza['note'] ?? '')) }}</textarea></div>

    <div class="flex items-center justify-between"><button type="submit" class="rounded bg-blue-600 px-4 py-2 text-white">{{ $pratica->exists ? 'Salva modifiche' : 'Crea pratica' }}</button></div>
</div>

<script>
    function praticaForm() {
        return {
            viaggioId: @js(old('viaggio_id', $pratica->viaggio_id ?? ($bozza['viaggio_id'] ?? ''))),
            totale: @js(old('totale', $pratica->exists ? $pratica->totale : ($bozza['totale'] ?? ''))),
            acconto: @js(old('acconto', $pratica->exists ? $pratica->acconto : ($bozza['acconto'] ?? 0))),
            saldo: @js(old('saldo', $pratica->exists ? $pratica->saldo : ($bozza['saldo'] ?? 0))),
            dataAcconto: @js($dataAcconto),
            dataSaldo: @js($dataSaldo),
            gratuiti: @js($gratuiti),
            numeroClienti: @js($numeroClienti),
            totaleForzato: {{ $pratica->exists || old('totale') ? 'true' : 'false' }},
            init() {
                this.$watch('acconto', (valore) => { if (Number(valore) > 0 && !this.dataAcconto) this.dataAcconto = new Date().toISOString().slice(0, 10); });
                this.$watch('saldo', (valore) => { if (Number(valore) > 0 && !this.dataSaldo) this.dataSaldo = new Date().toISOString().slice(0, 10); });
                @if (! $pratica->exists && $numeroClienti)
                    this.ricalcolaTotale(true);
                @endif
            },
            get residuo() { return (Number(this.totale) || 0) - (Number(this.acconto) || 0) - (Number(this.saldo) || 0); },
            ricalcolaTotale(forza = false) {
                if (this.totaleForzato && !forza) return;
                const select = document.getElementById('viaggio_id');
                const prezzo = select.options[select.selectedIndex]?.dataset.prezzo;

                if (prezzo === undefined || prezzo === '') {
                    this.totale = '';
                    return;
                }

                this.totale = this.numeroClienti ? (Number(prezzo) * (this.numeroClienti - this.gratuiti.length)).toFixed(2) : '';
            },
            rimuoviCliente(id) {
                document.querySelector(`input[name="clienti[]"][value="${id}"]`)?.remove();
                document.querySelector(`input[name="gratuiti[]"][value="${id}"]`)?.closest('div.rounded.border')?.remove();
                this.numeroClienti--;
                this.gratuiti = this.gratuiti.filter((clienteId) => Number(clienteId) !== id);
                this.ricalcolaTotale(true);
            },
            formatoEuro(valore) { return new Intl.NumberFormat('it-IT', { style: 'currency', currency: 'EUR' }).format(valore); },
        }
    }
</script>