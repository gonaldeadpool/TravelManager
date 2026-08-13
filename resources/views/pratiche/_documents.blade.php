<div class="mx-auto max-w-6xl rounded bg-white p-6 shadow">
    <h3 class="mb-4 text-lg font-semibold">Documenti della pratica</h3>
    <form method="POST" action="{{ route('pratiche.documenti.store', $pratica) }}" enctype="multipart/form-data" class="mb-5 flex flex-col gap-3 sm:flex-row">
        @csrf
        <input type="file" name="documento_file" required class="w-full rounded border p-2">
        <button class="rounded bg-blue-600 px-4 py-2 text-white">Allega documento</button>
    </form>
    <div class="space-y-2">
        @forelse ($pratica->documenti as $documento)
            <div class="flex items-center justify-between gap-3 rounded border p-3">
                <a href="{{ route('pratiche.documenti.download', [$pratica, $documento]) }}" target="_blank" class="min-w-0 truncate text-blue-600 hover:underline">{{ $documento->nome_originale }}</a>
                <form method="POST" action="{{ route('pratiche.documenti.destroy', [$pratica, $documento]) }}">
                    @csrf
                    @method('DELETE')
                    <button class="text-sm text-red-600 hover:underline">Elimina</button>
                </form>
            </div>
        @empty
            <p class="text-sm text-gray-500">Nessun documento allegato.</p>
        @endforelse
    </div>
</div>