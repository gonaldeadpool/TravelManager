@php($dialogId = 'email-pratica-dialog-' . $pratica->id)

<dialog
    id="{{ $dialogId }}"
    @if ((string) old('email_pratica_id') === (string) $pratica->id || (request()->routeIs('pratiche.show') && request()->boolean('email'))) data-open-on-load @endif
    class="pratica-screen-only m-auto max-h-[90vh] w-[calc(100%-2rem)] max-w-2xl overflow-y-auto rounded border-0 bg-white p-0 shadow-xl backdrop:bg-gray-900/50"
    onclick="if (event.target === this) this.close()"
    aria-labelledby="email-pratica-title-{{ $pratica->id }}"
>
    <form method="POST" action="{{ route('pratiche.riepilogo.email', $pratica) }}" class="space-y-5 p-6">
        @csrf
        <input type="hidden" name="email_pratica_id" value="{{ $pratica->id }}">
        <div class="flex items-start justify-between gap-4 border-b pb-4">
            <div>
                <h3 id="email-pratica-title-{{ $pratica->id }}" class="text-lg font-semibold text-gray-900">Invia riepilogo pratica</h3>
                <p class="mt-1 text-sm text-gray-500">Pratica #{{ $pratica->id }} · PDF allegato</p>
            </div>
            <button type="button" onclick="document.getElementById('{{ $dialogId }}').close()" aria-label="Chiudi" class="inline-flex h-8 w-8 items-center justify-center rounded text-gray-500 hover:bg-gray-100">&times;</button>
        </div>

        <fieldset>
            <legend class="mb-2 text-sm font-medium text-gray-800">Destinatari suggeriti</legend>
            <div class="max-h-36 space-y-2 overflow-y-auto rounded border p-3">
                @php($indirizziSelezionati = collect(old('client_recipients', []))->map(fn ($email) => mb_strtolower((string) $email))->all())
                @php($indirizziSuggeriti = 0)
                @foreach ($pratica->clienti as $cliente)
                    @if (filter_var($cliente->email, FILTER_VALIDATE_EMAIL))
                        @php($indirizziSuggeriti++)
                        <label class="flex items-center gap-2 text-sm">
                            <input type="checkbox" name="client_recipients[]" value="{{ $cliente->email }}" @checked(in_array(mb_strtolower($cliente->email), $indirizziSelezionati, true)) class="rounded border-gray-300 text-blue-600">
                            <span>{{ $cliente->cognome }} {{ $cliente->nome }}</span>
                            <span class="text-gray-500">&lt;{{ $cliente->email }}&gt;</span>
                        </label>
                    @endif
                @endforeach
                @if ($indirizziSuggeriti === 0)
                    <p class="text-sm text-gray-500">Nessun cliente della pratica ha un indirizzo email valido.</p>
                @endif
            </div>
            @error('client_recipients')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            @error('client_recipients.*')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </fieldset>

        <div>
            <label for="manual_recipients_{{ $pratica->id }}" class="mb-1 block text-sm font-medium text-gray-800">Altri indirizzi</label>
            <textarea id="manual_recipients_{{ $pratica->id }}" name="manual_recipients" rows="2" placeholder="nome@esempio.it; altro@esempio.it" class="w-full rounded border p-2">{{ old('manual_recipients') }}</textarea>
            <p class="mt-1 text-xs text-gray-500">Puoi inserire più indirizzi separandoli con virgola, punto e virgola o a capo.</p>
            @error('manual_recipients')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="email_subject_{{ $pratica->id }}" class="mb-1 block text-sm font-medium text-gray-800">Oggetto</label>
            <input id="email_subject_{{ $pratica->id }}" name="subject" value="{{ old('subject', 'Riepilogo pratica #' . $pratica->id . ' - ' . $pratica->viaggio->nome) }}" required maxlength="255" class="w-full rounded border p-2">
            @error('subject')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="email_body_{{ $pratica->id }}" class="mb-1 block text-sm font-medium text-gray-800">Messaggio</label>
            <textarea id="email_body_{{ $pratica->id }}" name="body" rows="6" maxlength="10000" placeholder="Aggiungi un messaggio" class="w-full rounded border p-2">{{ old('body', "Buongiorno,\n\nin allegato inviamo il riepilogo della pratica.\n\nCordiali saluti") }}</textarea>
            @error('body')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>

        <div class="flex justify-end gap-3 border-t pt-4">
            <button type="button" onclick="document.getElementById('{{ $dialogId }}').close()" class="rounded border px-4 py-2 text-sm text-gray-700">Annulla</button>
            <button type="submit" class="rounded bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">Invia email</button>
        </div>
    </form>
</dialog>