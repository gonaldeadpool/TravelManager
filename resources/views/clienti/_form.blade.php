<div x-data="{ tab: 'anagrafica' }">

    <div class="border-b border-gray-200 mb-6">
        <nav class="flex gap-6" aria-label="Sezioni cliente">
            <button
                type="button"
                @click="tab = 'anagrafica'"
                :class="tab === 'anagrafica' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700'"
                class="border-b-2 px-1 pb-3 text-sm font-semibold transition">
                Dati cliente
            </button>
            <button
                type="button"
                @click="tab = 'documenti'"
                :class="tab === 'documenti' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700'"
                class="border-b-2 px-1 pb-3 text-sm font-semibold transition">
                Documenti
            </button>
        </nav>
    </div>

    <div x-show="tab === 'anagrafica'" x-cloak class="space-y-6">

    {{-- DATI ANAGRAFICI --}}
    <div class="bg-white shadow rounded p-6">

        <h3 class="text-lg font-semibold mb-4">
            Dati Anagrafici
        </h3>

        <div class="grid grid-cols-2 gap-4">

            <div>
                <label class="block mb-1">Nome *</label>

                <input
                    type="text"
                    name="nome"
                    value="{{ old('nome', $cliente->nome ?? '') }}"
                    class="border rounded w-full p-2">
            </div>

            <div>
                <label class="block mb-1">Cognome *</label>

                <input
                    type="text"
                    name="cognome"
                    value="{{ old('cognome', $cliente->cognome ?? '') }}"
                    class="border rounded w-full p-2">
            </div>

            <div>
                <label class="block mb-1">Data di Nascita</label>

                <input
                    type="date"
                    name="data_nascita"
                    value="{{ old('data_nascita', $cliente->data_nascita ?? '') }}"
                    class="border rounded w-full p-2">
            </div>

            <div>
                <label class="block mb-1">Luogo di Nascita</label>

                <input
                    type="text"
                    name="luogo_nascita"
                    value="{{ old('luogo_nascita', $cliente->luogo_nascita ?? '') }}"
                    class="border rounded w-full p-2">
            </div>

            <div class="col-span-2">
                <label class="block mb-1">Codice Fiscale</label>

                <input
                    type="text"
                    name="codice_fiscale"
                    value="{{ old('codice_fiscale', $cliente->codice_fiscale ?? '') }}"
                    class="border rounded w-full p-2">
            </div>

        </div>

    </div>

    {{-- CONTATTI --}}
    <div class="bg-white shadow rounded p-6">

        <h3 class="text-lg font-semibold mb-4">
            Contatti
        </h3>

        <div class="grid grid-cols-2 gap-4">

            <div>
                <label class="block mb-1">Telefono</label>

                <input
                    type="text"
                    name="telefono"
                    value="{{ old('telefono', $cliente->telefono ?? '') }}"
                    class="border rounded w-full p-2">
            </div>

            <div>
                <label class="block mb-1">Cellulare</label>

                <input
                    type="text"
                    name="cellulare"
                    value="{{ old('cellulare', $cliente->cellulare ?? '') }}"
                    class="border rounded w-full p-2">
            </div>

            <div class="col-span-2">
                <label class="block mb-1">Email</label>

                <input
                    type="email"
                    name="email"
                    value="{{ old('email', $cliente->email ?? '') }}"
                    class="border rounded w-full p-2">
            </div>

        </div>

    </div>

    {{-- INDIRIZZO --}}
    <div class="bg-white shadow rounded p-6">

        <h3 class="text-lg font-semibold mb-4">
            Indirizzo
        </h3>

        <div class="grid grid-cols-2 gap-4">

            <div class="col-span-2">
                <label class="block mb-1">Indirizzo</label>

                <input
                    type="text"
                    name="indirizzo"
                    value="{{ old('indirizzo', $cliente->indirizzo ?? '') }}"
                    class="border rounded w-full p-2">
            </div>

            <div>
                <label class="block mb-1">CAP</label>

                <input
                    type="text"
                    name="cap"
                    value="{{ old('cap', $cliente->cap ?? '') }}"
                    class="border rounded w-full p-2">
            </div>

            <div>
                <label class="block mb-1">Città</label>

                <input
                    type="text"
                    name="citta"
                    value="{{ old('citta', $cliente->citta ?? '') }}"
                    class="border rounded w-full p-2">
            </div>

            <div>
                <label class="block mb-1">Provincia</label>

                <input
                    type="text"
                    name="provincia"
                    value="{{ old('provincia', $cliente->provincia ?? '') }}"
                    class="border rounded w-full p-2">
            </div>

            <div>
                <label class="block mb-1">Nazione</label>

                <input
                    type="text"
                    name="nazione"
                    value="{{ old('nazione', $cliente->nazione ?? '') }}"
                    class="border rounded w-full p-2">
            </div>

        </div>

    </div>

    {{-- NOTE --}}
    <div class="bg-white shadow rounded p-6">

        <h3 class="text-lg font-semibold mb-4">
            Note
        </h3>

        <textarea
            name="note"
            rows="5"
            class="border rounded w-full p-2">{{ old('note', $cliente->note ?? '') }}</textarea>

    </div>

    </div>

    <div x-show="tab === 'documenti'" x-cloak class="space-y-6">
        <div class="bg-white shadow rounded p-6">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-lg font-semibold">Documenti del cliente</h3>
                    <p class="text-sm text-gray-500 mt-1">Tieni qui i documenti utili per viaggi e pratiche.</p>
                </div>
                <span class="text-sm text-gray-400">0 documenti</span>
            </div>

            <div class="rounded border border-dashed border-gray-300 bg-gray-50 p-8 text-center">
                <p class="font-medium text-gray-700">Nessun documento presente</p>
                <p class="text-sm text-gray-500 mt-1">I documenti caricati appariranno qui.</p>
            </div>
        </div>

        <div class="bg-white shadow rounded p-6">
            <h3 class="text-lg font-semibold mb-4">Aggiungi documento</h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="documento_tipo" class="block mb-1">Tipo documento</label>
                    <select id="documento_tipo" name="documento_tipo" class="border rounded w-full p-2">
                        <option value="">Seleziona un tipo</option>
                        <option value="carta_identita">Carta d'identità</option>
                        <option value="passaporto">Passaporto</option>
                        <option value="patente">Patente</option>
                        <option value="altro">Altro</option>
                    </select>
                </div>

                <div>
                    <label for="documento_numero" class="block mb-1">Numero documento</label>
                    <input id="documento_numero" type="text" name="documento_numero" class="border rounded w-full p-2">
                </div>

                <div>
                    <label for="documento_scadenza" class="block mb-1">Data di scadenza</label>
                    <input id="documento_scadenza" type="date" name="documento_scadenza" class="border rounded w-full p-2">
                </div>

                <div>
                    <label for="documento_file" class="block mb-1">File</label>
                    <input id="documento_file" type="file" name="documento_file" class="border rounded w-full p-2">
                </div>
            </div>
        </div>
    </div>
</div>