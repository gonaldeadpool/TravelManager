@php
    $trasporti = old('trasporti', $viaggio->trasporti ?? []);
    $sistemazioni = old('sistemazioni', $viaggio->sistemazioni ?? []);
@endphp

<div x-data="{
    trasporti: @js($trasporti),
    sistemazioni: @js($sistemazioni),
    tipologia: @js(old('tipologia', $viaggio->tipologia ?? 'viaggio')),
    locandinaPreview: @js($viaggio->locandina ? asset('storage/' . $viaggio->locandina) : null),
    aggiungiTrasporto() { this.trasporti.push({ tipo: 'bus', posti: '' }) },
    aggiungiSistemazione() { this.sistemazioni.push({ tipo: 'camera', formato: 'doppia', quantita: '' }) },
    cambiaTipologia() {
        if (this.tipologia !== 'crociera') {
            this.sistemazioni = this.sistemazioni.filter(sistemazione => sistemazione.tipo !== 'cabina');
        }
    },
    rimuovi(array, indice) { array.splice(indice, 1) },
    durata() {
        if (!this.dataPartenza || !this.dataRientro) return null;

        const partenza = new Date(this.dataPartenza + 'T00:00:00');
        const rientro = new Date(this.dataRientro + 'T00:00:00');
        const notti = Math.round((rientro - partenza) / 86400000);

        return notti >= 0 ? { giorni: notti + 1, notti } : null;
    },
    anteprimaLocandina(evento) {
        const file = evento.target.files[0];

        if (!file) {
            return;
        }

        const lettore = new FileReader();
        lettore.onload = (eventoLettura) => this.locandinaPreview = eventoLettura.target.result;
        lettore.readAsDataURL(file);
    },
    dataPartenza: @js(old('data_partenza', optional($viaggio->data_partenza)->format('Y-m-d'))),
    dataRientro: @js(old('data_rientro', optional($viaggio->data_rientro)->format('Y-m-d')))
}" x-init="cambiaTipologia()" class="grid grid-cols-1 gap-6 lg:grid-cols-[280px_minmax(0,1fr)] lg:items-start">
    <aside class="bg-white shadow rounded p-6 lg:sticky lg:top-6">
        <h3 class="text-lg font-semibold mb-4">Locandina</h3>

        <div class="flex min-h-[360px] items-center justify-center rounded border border-dashed border-gray-300 bg-gray-50 p-4">
            <template x-if="locandinaPreview">
                <img :src="locandinaPreview" alt="Anteprima locandina" class="max-h-[330px] w-full rounded object-contain">
            </template>
            <p x-show="!locandinaPreview" class="text-center text-sm text-gray-500">Nessuna locandina selezionata.</p>
        </div>

        <label for="locandina" class="mt-4 block mb-1 text-sm font-medium">Carica locandina</label>
        <input id="locandina" type="file" name="locandina" accept="image/*" @change="anteprimaLocandina($event)" class="w-full rounded border p-2 text-sm">

        @if ($viaggio->locandina)
            <a href="{{ asset('storage/' . $viaggio->locandina) }}" target="_blank" class="mt-3 inline-block text-sm text-blue-600 hover:underline">Apri locandina attuale</a>
        @endif
    </aside>

    <div class="space-y-6">
        <div class="bg-white shadow rounded p-6">
            <h3 class="text-lg font-semibold mb-4">Informazioni del viaggio</h3>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <div>
                <label for="nome" class="block mb-1">Nome viaggio *</label>
                <input id="nome" type="text" name="nome" value="{{ old('nome', $viaggio->nome ?? '') }}" required class="border rounded w-full p-2">
            </div>

            <div>
                <label for="tipologia" class="block mb-1">Tipo di esperienza *</label>
                <select id="tipologia" name="tipologia" x-model="tipologia" @change="cambiaTipologia()" required class="border rounded w-full p-2">
                    <option value="viaggio" @selected(old('tipologia', $viaggio->tipologia ?? 'viaggio') === 'viaggio')>Viaggio</option>
                    <option value="tour" @selected(old('tipologia', $viaggio->tipologia ?? '') === 'tour')>Tour</option>
                    <option value="crociera" @selected(old('tipologia', $viaggio->tipologia ?? '') === 'crociera')>Crociera</option>
                </select>
            </div>

            <div>
                <label for="destinazione" class="block mb-1">Dove *</label>
                <input id="destinazione" type="text" name="destinazione" value="{{ old('destinazione', $viaggio->destinazione ?? '') }}" required class="border rounded w-full p-2">
            </div>

            <div>
                <label for="minimo_partecipanti" class="block mb-1">Minimo partecipanti *</label>
                <input id="minimo_partecipanti" type="number" name="minimo_partecipanti" min="1" value="{{ old('minimo_partecipanti', $viaggio->minimo_partecipanti ?? '') }}" required class="border rounded w-full p-2">
            </div>

            <div>
                <label for="data_partenza" class="block mb-1">Dal *</label>
                <input id="data_partenza" type="date" name="data_partenza" x-model="dataPartenza" value="{{ old('data_partenza', optional($viaggio->data_partenza)->format('Y-m-d')) }}" required class="border rounded w-full p-2">
            </div>

            <div>
                <label for="data_rientro" class="block mb-1">Al *</label>
                <input id="data_rientro" type="date" name="data_rientro" x-model="dataRientro" value="{{ old('data_rientro', optional($viaggio->data_rientro)->format('Y-m-d')) }}" required class="border rounded w-full p-2">
            </div>

            <div>
                <label for="prezzo" class="block mb-1">Prezzo a persona *</label>
                <div class="relative">
                    <input id="prezzo" type="number" name="prezzo" min="0" step="0.01" value="{{ old('prezzo', $viaggio->prezzo ?? '') }}" required class="border rounded w-full p-2 pr-8">
                    <span class="absolute right-3 top-2 text-gray-500">EUR</span>
                </div>
            </div>

            <div class="md:col-span-2" x-show="durata()" x-cloak>
                <div class="rounded border border-blue-200 bg-blue-50 px-4 py-3 text-sm text-blue-800">
                    Durata: <strong x-text="durata()?.giorni"></strong> giorni e <strong x-text="durata()?.notti"></strong> notti
                </div>
            </div>

            <div class="md:col-span-2">
                <label for="note" class="block mb-1">Note</label>
                <textarea id="note" name="note" rows="4" class="border rounded w-full p-2" placeholder="Aggiungi eventuali note sul viaggio">{{ old('note', $viaggio->note ?? '') }}</textarea>
            </div>

            </div>
        </div>

        <div class="bg-white shadow rounded p-6">
        <div class="flex items-center justify-between gap-4 mb-4">
            <div>
                <h3 class="text-lg font-semibold">Mezzi di trasporto</h3>
                <p class="text-sm text-gray-500 mt-1">Definisci il mezzo e i posti disponibili.</p>
            </div>
            <button type="button" @click="aggiungiTrasporto()" class="bg-gray-800 text-white px-3 py-2 rounded text-sm">Aggiungi mezzo</button>
        </div>

        <div class="space-y-3">
            <template x-for="(trasporto, indice) in trasporti" :key="indice">
                <div class="grid grid-cols-1 md:grid-cols-[1fr_1fr_auto] gap-3 items-end rounded border p-3">
                    <div>
                        <label class="block mb-1">Mezzo</label>
                        <select :name="`trasporti[${indice}][tipo]`" x-model="trasporto.tipo" class="border rounded w-full p-2">
                            <option value="bus">Bus</option>
                            <option value="aereo">Aereo</option>
                            <option value="treno">Treno</option>
                        </select>
                    </div>
                    <div>
                        <label class="block mb-1">Posti disponibili</label>
                        <input type="number" min="1" :name="`trasporti[${indice}][posti]`" x-model="trasporto.posti" class="border rounded w-full p-2">
                    </div>
                    <button type="button" @click="rimuovi(trasporti, indice)" class="text-red-600 px-2 py-2 text-sm">Rimuovi</button>
                </div>
            </template>
            <p x-show="trasporti.length === 0" class="text-sm text-gray-500">Nessun mezzo configurato.</p>
        </div>
        </div>

        <div class="bg-white shadow rounded p-6">
        <div class="flex items-center justify-between gap-4 mb-4">
            <div>
                <h3 class="text-lg font-semibold">Camere e cabine</h3>
                <p class="text-sm text-gray-500 mt-1">Indica la sistemazione, il formato e la quantità disponibile.</p>
            </div>
            <button type="button" @click="aggiungiSistemazione()" class="bg-gray-800 text-white px-3 py-2 rounded text-sm">Aggiungi sistemazione</button>
        </div>

        <div class="space-y-3">
            <template x-for="(sistemazione, indice) in sistemazioni" :key="indice">
                <div class="grid grid-cols-1 md:grid-cols-[1fr_1fr_1fr_auto] gap-3 items-end rounded border p-3">
                    <div>
                        <label class="block mb-1">Tipo</label>
                        <select :name="`sistemazioni[${indice}][tipo]`" x-model="sistemazione.tipo" class="border rounded w-full p-2">
                            <option value="camera">Camera</option>
                            <option value="cabina" x-show="tipologia === 'crociera'">Cabina</option>
                        </select>
                    </div>
                    <div>
                        <label class="block mb-1">Formato</label>
                        <select :name="`sistemazioni[${indice}][formato]`" x-model="sistemazione.formato" class="border rounded w-full p-2">
                            <option value="singola">Singola</option>
                            <option value="doppia">Doppia</option>
                            <option value="tripla">Tripla</option>
                            <option value="quadrupla">Quadrupla</option>
                        </select>
                    </div>
                    <div>
                        <label class="block mb-1">Quantità</label>
                        <input type="number" min="1" :name="`sistemazioni[${indice}][quantita]`" x-model="sistemazione.quantita" class="border rounded w-full p-2">
                    </div>
                    <button type="button" @click="rimuovi(sistemazioni, indice)" class="text-red-600 px-2 py-2 text-sm">Rimuovi</button>
                </div>
            </template>
            <p x-show="sistemazioni.length === 0" class="text-sm text-gray-500">Nessuna sistemazione configurata.</p>
        </div>
        </div>
    </div>
</div>
