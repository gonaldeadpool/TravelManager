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