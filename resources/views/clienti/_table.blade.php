@if ($clienti->isEmpty())
    <div class="bg-white shadow rounded p-8 text-center text-gray-500">Nessun cliente trovato.</div>
@else
    <div class="overflow-x-auto bg-white shadow rounded">
        <table class="w-full text-left">
            <thead class="border-b bg-gray-50 text-sm text-gray-600">
                <tr>
                    <th class="px-4 py-3">Nome</th>
                    <th class="px-4 py-3">Cognome</th>
                    <th class="px-4 py-3">Email</th>
                    <th class="px-4 py-3">Telefono</th>
                    <th class="px-4 py-3 text-right">Azioni</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @foreach ($clienti as $cliente)
                    <tr>
                        <td class="px-4 py-3 font-medium">{{ $cliente->nome }}</td>
                        <td class="px-4 py-3">{{ $cliente->cognome }}</td>
                        <td class="px-4 py-3">{{ $cliente->email ?: '-' }}</td>
                        <td class="px-4 py-3">{{ $cliente->telefono ?: '-' }}</td>
                        <td class="px-4 py-3">
                            <div class="flex justify-end gap-2">
                                <a href="{{ route('clienti.edit', $cliente->id) }}" title="Modifica cliente" aria-label="Modifica cliente" class="inline-flex h-8 w-8 items-center justify-center rounded text-blue-600 hover:bg-blue-50">
                                    <svg aria-hidden="true" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M12 20h9" />
                                        <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4Z" />
                                    </svg>
                                </a>
                                <form action="{{ route('clienti.destroy', $cliente->id) }}" method="POST" onsubmit="return confirm('Sei sicuro di voler eliminare questo cliente?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" title="Elimina cliente" aria-label="Elimina cliente" class="inline-flex h-8 w-8 items-center justify-center rounded text-red-600 hover:bg-red-50">
                                        <svg aria-hidden="true" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M3 6h18" />
                                            <path d="M8 6V4h8v2" />
                                            <path d="M19 6l-1 14H6L5 6" />
                                            <path d="M10 11v5M14 11v5" />
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $clienti->links() }}</div>
@endif
