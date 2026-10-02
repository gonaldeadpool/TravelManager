@php
    $dialogId = 'pdf-pratica-dialog-' . $pratica->id;
    $frameId = 'pdf-pratica-frame-' . $pratica->id;
@endphp

<dialog
    id="{{ $dialogId }}"
    class="m-auto h-[90vh] w-[calc(100%-2rem)] max-w-6xl overflow-hidden rounded border-0 bg-white p-0 shadow-xl backdrop:bg-gray-900/50"
    onclick="if (event.target === this) this.close()"
    aria-labelledby="pdf-pratica-title-{{ $pratica->id }}"
>
    <div class="flex h-14 items-center justify-between gap-4 border-b px-4">
        <h3 id="pdf-pratica-title-{{ $pratica->id }}" class="font-semibold text-gray-900">Anteprima di stampa · Pratica #{{ $pratica->id }}</h3>
        <div class="flex items-center gap-2">
            <button type="button" onclick="document.getElementById('{{ $frameId }}').contentWindow.print()" title="Stampa PDF" aria-label="Stampa PDF" class="inline-flex h-9 w-9 items-center justify-center rounded border text-gray-700 hover:bg-gray-100">
                <svg aria-hidden="true" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
            </button>
            <a href="{{ route('pratiche.riepilogo.pdf.download', $pratica) }}" title="Scarica PDF" aria-label="Scarica PDF" class="inline-flex h-9 w-9 items-center justify-center rounded border text-gray-700 hover:bg-gray-100">
                <svg aria-hidden="true" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
            </a>
            <button type="button" onclick="document.getElementById('{{ $dialogId }}').close()" title="Chiudi anteprima" aria-label="Chiudi anteprima" class="inline-flex h-9 w-9 items-center justify-center rounded border text-gray-700 hover:bg-gray-100">&times;</button>
        </div>
    </div>
    <iframe id="{{ $frameId }}" data-src="{{ route('pratiche.riepilogo.pdf', $pratica) }}" title="PDF pratica {{ $pratica->id }}" class="h-[calc(90vh-3.5rem)] w-full bg-gray-100"></iframe>
</dialog>