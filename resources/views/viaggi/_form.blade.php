@php
    $trasporti = old('trasporti', $viaggio->trasporti ?? []);
    $sistemazioni = old('sistemazioni', $viaggio->sistemazioni ?? []);
@endphp

<div x-data="{
    trasporti: @js($trasporti),
    sistemazioni: @js($sistemazioni),
    aggiungiTrasporto() { this.trasporti.push({ tipo: 'bus', posti: '' }) },
    aggiungiSistemazione() { this.sistemazioni.push({ tipo: 'camera', formato: 'doppia', quantita: '' }) },
    rimuovi(array, indice) { array.splice(indice, 1) }
}" class="space-y-6">
    <div class="bg-white shadow rounded p-6">
        <h3 class="text-lg font-semibold mb-4">Informazioni del viaggio</h3>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="md:col-span-2">
                <label for="nome" class="block mb-1">Nome viaggio *</label>
                <input id="nome" type="text" name="nome" value="{{ old('nome', $viaggio->nome ?? '') }}" required class="border rounded w-full p-2">
            </div>

            <div>
                <label for="gestione" class="block mb-1">Organizzazione *</label>
                <select id="gestione" name="gestione" required class="border rounded w-full p-2">
                    <option value="tour_operator" @selected(old('gestione', $viaggio->gestione ?? '') === 'tour_operator')>Tour operator</option>
                    <option value="interno" @selected(old('gestione', $viaggio->gestione ?? '') === 'interno')>Organizzazione interna</option>
                </select>
            </div>

            <div>
                <label for="destinazione" class="block mb-1">Dove *</label>
                <input id="destinazione" type="text" name="destinazione" value="{{ old('destinazione', $viaggio->destinazione ?? '') }}" required class="border rounded w-full p-2">
            </div>

            <div>
                <label for="data_partenza" class="block mb-1">Dal *</label>
                <input id="data_partenza" type="date" name="data_partenza" value="{{ old('data_partenza', optional($viaggio->data_partenza)->format('Y-m-d')) }}" required class="border rounded w-full p-2">
            </div>

            <div>
                <label for="data_rientro" class="block mb-1">Al *</label>
                <input id="data_rientro" type="date" name="data_rientro" value="{{ old('data_rientro', optional($viaggio->data_rientro)->format('Y-m-d')) }}" required class="border rounded w-full p-2">
            </div>

            <div class="md:col-span-2">
                <label for="locandina" class="block mb-1">Locandina</label>
                <input id="locandina" type="file" name="locandina" accept="image/*" class="border rounded w-full p-2">
                @if ($viaggio->locandina)
                    <a href="{{ asset('storage/' . $viaggio->locandina) }}" target="_blank" class="inline-block mt-2 text-sm text-blue-600 hover:underline">Visualizza locandina attuale</a>
                @endif
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
                            <option value="cabina">Cabina</option>
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
