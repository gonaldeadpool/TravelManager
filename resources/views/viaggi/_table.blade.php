@if ($viaggi->isEmpty())
    <div class="bg-white shadow rounded p-8 text-center text-gray-500">Nessun viaggio trovato.</div>
@else
    <div class="overflow-x-auto bg-white shadow rounded">
        <table class="w-full text-left">
            <thead class="border-b bg-gray-50 text-sm text-gray-600">
                <tr>
                    <th class="px-4 py-3">Locandina</th>
                    <th class="px-4 py-3">Viaggio</th>
                    <th class="px-4 py-3">Tipologia</th>
                    <th class="px-4 py-3">Destinazione</th>
                    <th class="px-4 py-3">Periodo</th>
                    <th class="px-4 py-3">Durata</th>
                    <th class="px-4 py-3">Prezzo</th>
                    <th class="px-4 py-3">Minimo</th>
                    <th class="px-4 py-3">Partecipanti</th>
                    <th class="px-4 py-3 text-right">Azioni</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @foreach ($viaggi as $viaggio)
                    <tr>
                        <td class="px-4 py-3">
                            @if ($viaggio->locandina)
                                <a href="{{ route('viaggi.locandina', $viaggio) }}" target="_blank" title="Apri locandina">
                                    <img src="{{ route('viaggi.locandina', $viaggio) }}" alt="Locandina di {{ $viaggio->nome }}" class="h-16 w-12 rounded object-cover border">
                                </a>
                            @else
                                <div class="flex h-16 w-12 items-center justify-center rounded border border-dashed bg-gray-50 text-center text-xs text-gray-400">Nessuna immagine</div>
                            @endif
                        </td>
                        <td class="px-4 py-3 font-medium"><a href="{{ route('viaggi.show', $viaggio) }}" class="text-blue-600 hover:underline">{{ $viaggio->nome }}</a></td>
                        <td class="px-4 py-3">{{ ucfirst($viaggio->tipologia) }}</td>
                        <td class="px-4 py-3">{{ $viaggio->destinazione }}</td>
                        <td class="px-4 py-3 whitespace-nowrap">{{ $viaggio->data_partenza->format('d/m/Y') }} - {{ $viaggio->data_rientro->format('d/m/Y') }}</td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            {{ $viaggio->data_partenza->diffInDays($viaggio->data_rientro) + 1 }} giorni,
                            {{ $viaggio->data_partenza->diffInDays($viaggio->data_rientro) }} notti
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">{{ number_format((float) $viaggio->prezzo, 2, ',', '.') }} EUR</td>
                        <td class="px-4 py-3 whitespace-nowrap">{{ $viaggio->minimo_partecipanti ?? '-' }}</td>
                        <td class="px-4 py-3 whitespace-nowrap">{{ $viaggio->pratiche->flatMap->clienti->unique('id')->count() }}</td>
                        <td class="px-4 py-3">
                            <div class="flex justify-end gap-2">
                                <a href="{{ route('pratiche.index', ['viaggio_id' => $viaggio->id]) }}" title="Pratiche" aria-label="Apri pratiche del viaggio" class="inline-flex h-8 w-8 items-center justify-center rounded text-gray-600 hover:bg-gray-100 hover:text-gray-900">
                                    <span aria-hidden="true" class="text-sm font-bold">P</span>
                                </a>
                                <a href="{{ route('viaggi.edit', $viaggio) }}" title="Modifica viaggio" aria-label="Modifica viaggio" class="inline-flex h-8 w-8 items-center justify-center rounded text-blue-600 hover:bg-blue-50">
                                    <svg aria-hidden="true" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M12 20h9" />
                                        <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4Z" />
                                    </svg>
                                </a>
                                <form method="POST" action="{{ route('viaggi.destroy', $viaggio) }}" onsubmit="return confirm('Vuoi eliminare questo viaggio?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" title="Cancella viaggio" aria-label="Cancella viaggio" class="inline-flex h-8 w-8 items-center justify-center rounded text-red-600 hover:bg-red-50">
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

    <div class="mt-4">{{ $viaggi->links() }}</div>
@endif
