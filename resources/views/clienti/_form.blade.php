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
                <span data-documenti-counter class="text-sm text-gray-400">{{ $documenti->count() }} documenti</span>
            </div>

            <div id="documenti-elenco" class="divide-y rounded border">
                @if ($documenti->isEmpty())
                    <div data-empty-documenti class="rounded border border-dashed border-gray-300 bg-gray-50 p-8 text-center">
                        <p class="font-medium text-gray-700">Nessun documento presente</p>
                        <p class="text-sm text-gray-500 mt-1">I documenti caricati appariranno qui.</p>
                    </div>
                @else
                    @foreach ($documenti as $documento)
                        @include('clienti._documento', ['cliente' => $cliente, 'documento' => $documento])
                    @endforeach
                @endif
            </div>
        </div>

        <div class="bg-white shadow rounded p-6">
            <h3 class="text-lg font-semibold mb-4">Aggiungi documento</h3>

            @if (isset($cliente))
            <div id="documento-upload-box" data-upload-url="{{ route('clienti.documenti.store', $cliente) }}" class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div>
                    <label for="documento_tipo" class="block mb-1">Tipo documento</label>
                    <select id="documento_tipo" class="border rounded w-full p-2">
                        <option value="">Seleziona un tipo</option>
                        <option value="carta_identita">Carta d'identità</option>
                        <option value="passaporto">Passaporto</option>
                        <option value="patente">Patente</option>
                        <option value="altro">Altro</option>
                    </select>
                </div>

                <div>
                    <label for="documento_numero" class="block mb-1">Numero documento</label>
                    <input id="documento_numero" type="text" class="border rounded w-full p-2">
                </div>

                <div>
                    <label for="documento_scadenza" class="block mb-1">Data di scadenza</label>
                    <input id="documento_scadenza" type="date" class="border rounded w-full p-2">
                </div>

                <div>
                    <label for="documento_file" class="block mb-1">File</label>
                    <input id="documento_file" type="file" accept=".pdf,.jpeg,.jpg,application/pdf,image/jpeg" class="border rounded w-full p-2">
                </div>

                <div class="md:col-span-2">
                    <div id="documento-preview" class="hidden items-center gap-3 rounded border border-dashed border-gray-300 bg-gray-50 p-3"></div>
                    <p id="documento-upload-error" class="mt-2 hidden text-sm text-red-600"></p>
                    <button id="documento-upload-button" type="button" class="mt-3 rounded bg-gray-800 px-4 py-2 text-sm text-white">Aggiungi documento</button>
                </div>
            </div>
            @else
                <p class="rounded border border-dashed border-gray-300 bg-gray-50 p-4 text-sm text-gray-500">Salva prima il cliente per poter aggiungere documenti.</p>
            @endif
        </div>
    </div>
</div>

@if (isset($cliente))
<script>
document.addEventListener('DOMContentLoaded', function () {
    const box = document.getElementById('documento-upload-box');
    const fileInput = document.getElementById('documento_file');
    const preview = document.getElementById('documento-preview');
    const error = document.getElementById('documento-upload-error');
    const button = document.getElementById('documento-upload-button');
    const counter = document.querySelector('[data-documenti-counter]');

    fileInput.addEventListener('change', function () {
        const file = fileInput.files[0];
        preview.innerHTML = '';
        preview.classList.toggle('hidden', !file);
        preview.classList.toggle('flex', Boolean(file));

        if (!file) return;

        if (file.type === 'application/pdf') {
            preview.innerHTML = '<div class="flex h-14 w-12 items-center justify-center rounded border bg-red-50 text-xs font-bold text-red-700">PDF</div><span class="text-sm text-gray-700"></span>';
            preview.querySelector('span').textContent = file.name;
            return;
        }

        const image = document.createElement('img');
        image.src = URL.createObjectURL(file);
        image.alt = 'Anteprima documento';
        image.className = 'h-14 w-12 rounded border object-cover';
        preview.append(image, Object.assign(document.createElement('span'), { textContent: file.name, className: 'text-sm text-gray-700' }));
    });

    document.addEventListener('click', async function (event) {
        const deleteButton = event.target.closest('[data-documento-delete]');
        if (!deleteButton || !confirm('Vuoi eliminare questo documento?')) return;

        deleteButton.disabled = true;
        const response = await fetch(deleteButton.dataset.documentoDelete, {
            method: 'DELETE',
            headers: {
                Accept: 'application/json',
                'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
            },
        });

        if (response.ok) {
            deleteButton.closest('[data-documento-item]').remove();
            if (counter) counter.textContent = Math.max(0, Number(counter.textContent.split(' ')[0]) - 1) + ' documenti';
        } else {
            deleteButton.disabled = false;
        }
    });

    button.addEventListener('click', async function () {
        error.classList.add('hidden');
        const file = fileInput.files[0];
        if (!file) {
            error.textContent = 'Seleziona un file PDF, JPEG o JPG.';
            error.classList.remove('hidden');
            return;
        }

        button.disabled = true;
        button.textContent = 'Caricamento...';
        const dati = new FormData();
        dati.append('_token', document.querySelector('input[name="_token"]').value);
        dati.append('documento_tipo', document.getElementById('documento_tipo').value);
        dati.append('documento_numero', document.getElementById('documento_numero').value);
        dati.append('documento_scadenza', document.getElementById('documento_scadenza').value);
        dati.append('documento_file', file);

        try {
            const response = await fetch(box.dataset.uploadUrl, { method: 'POST', body: dati, headers: { Accept: 'application/json' } });
            const result = await response.json();
            if (!response.ok) throw new Error(Object.values(result.errors || {}).flat()[0] || 'Upload non riuscito.');

            document.querySelector('[data-empty-documenti]')?.remove();
            document.getElementById('documenti-elenco').insertAdjacentHTML('beforeend', result.html);
            fileInput.value = '';
            document.getElementById('documento_numero').value = '';
            document.getElementById('documento_scadenza').value = '';
            preview.innerHTML = '';
            preview.classList.add('hidden');
            preview.classList.remove('flex');
            if (counter) counter.textContent = result.count + ' documenti';
        } catch (uploadError) {
            error.textContent = uploadError.message;
            error.classList.remove('hidden');
        } finally {
            button.disabled = false;
            button.textContent = 'Aggiungi documento';
        }
    });
});
</script>
@endif