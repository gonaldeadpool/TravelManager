<div class="flex items-center justify-between gap-4 p-3" data-documento-item>
    <div class="flex min-w-0 items-center gap-3">
        @if (str_starts_with((string) $documento->mime_type, 'image/'))
            <img src="{{ route('clienti.documenti.download', [$cliente, $documento]) }}" alt="Miniatura {{ $documento->nome_originale }}" class="h-14 w-12 rounded border object-cover">
        @else
            <div class="flex h-14 w-12 items-center justify-center rounded border bg-red-50 text-xs font-bold text-red-700">PDF</div>
        @endif
        <div class="min-w-0">
            <a href="{{ route('clienti.documenti.download', [$cliente, $documento]) }}" target="_blank" class="block truncate font-medium text-blue-600 hover:underline">{{ $documento->nome_originale }}</a>
            <p class="text-sm text-gray-500">{{ ucfirst(str_replace('_', ' ', $documento->tipo ?? 'altro')) }} @if($documento->scadenza) · scade il {{ $documento->scadenza->format('d/m/Y') }} @endif</p>
        </div>
    </div>
    <button type="button" data-documento-delete="{{ route('clienti.documenti.destroy', [$cliente, $documento]) }}" class="text-sm text-red-600 hover:underline">Elimina</button>
</div>
