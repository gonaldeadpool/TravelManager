@php
    $uid = $uid ?? $cliente->id;
    $dialogId = 'email-cliente-dialog-' . $uid;
    $apriDialog = (string) old('email_cliente_uid') === (string) $uid;
@endphp

<dialog
    id="{{ $dialogId }}"
    @if ($apriDialog) data-open-on-load @endif
    class="m-auto max-h-[90vh] w-[calc(100%-2rem)] max-w-2xl overflow-y-auto rounded border-0 bg-white p-0 text-left shadow-xl backdrop:bg-gray-900/50"
    onclick="if (event.target === this) this.close()"
>
    <form method="POST" action="{{ route('clienti.email', $cliente) }}" class="space-y-5 p-6">
        @csrf
        <input type="hidden" name="email_cliente_uid" value="{{ $uid }}">
        <div class="flex items-start justify-between gap-4 border-b pb-4">
            <div>
                <h3 class="text-lg font-semibold text-gray-900">Invia email</h3>
                <p class="mt-1 text-sm text-gray-500">{{ $cliente->cognome }} {{ $cliente->nome }}</p>
            </div>
            <button type="button" onclick="document.getElementById('{{ $dialogId }}').close()" aria-label="Chiudi" class="inline-flex h-8 w-8 items-center justify-center rounded text-gray-500 hover:bg-gray-100">&times;</button>
        </div>

        <div>
            <label for="recipients_{{ $uid }}" class="mb-1 block text-sm font-medium text-gray-800">Destinatari</label>
            <textarea id="recipients_{{ $uid }}" name="recipients" rows="2" required class="w-full rounded border p-2">{{ $apriDialog ? old('recipients') : $cliente->email }}</textarea>
            <p class="mt-1 text-xs text-gray-500">Puoi aggiungere altri indirizzi separandoli con virgola, punto e virgola o a capo.</p>
            @if ($apriDialog)
                @error('recipients')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            @endif
        </div>

        <div>
            <label for="subject_{{ $uid }}" class="mb-1 block text-sm font-medium text-gray-800">Oggetto</label>
            <input id="subject_{{ $uid }}" name="subject" value="{{ $apriDialog ? old('subject') : '' }}" required maxlength="255" class="w-full rounded border p-2">
            @if ($apriDialog)
                @error('subject')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            @endif
        </div>

        <div>
            <label for="body_{{ $uid }}" class="mb-1 block text-sm font-medium text-gray-800">Messaggio</label>
            <textarea id="body_{{ $uid }}" name="body" rows="6" maxlength="10000" class="w-full rounded border p-2">{{ $apriDialog ? old('body') : '' }}</textarea>
            @if ($apriDialog)
                @error('body')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            @endif
        </div>

        <div class="flex justify-end gap-3 border-t pt-4">
            <button type="button" onclick="document.getElementById('{{ $dialogId }}').close()" class="rounded border px-4 py-2 text-sm text-gray-700">Annulla</button>
            <button type="submit" class="rounded bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">Invia email</button>
        </div>
    </form>
</dialog>
