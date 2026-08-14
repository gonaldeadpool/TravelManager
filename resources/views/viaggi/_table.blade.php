@php
    $ordinamenti = $ordinamenti ?? [];
    $sortMap = collect($ordinamenti)
        ->values()
        ->mapWithKeys(fn ($entry, $index) => [
            $entry['field'] => [
                'direction' => $entry['direction'],
                'priority' => $index + 1,
            ],
        ]);

    $sortInfo = function (string $field) use ($sortMap): array {
        $entry = $sortMap->get($field);

        return [
            'direction' => $entry['direction'] ?? null,
            'priority' => $entry['priority'] ?? null,
        ];
    };
@endphp

<div class="overflow-x-auto bg-white shadow rounded">
        <table class="w-full text-left">
            <thead class="border-b bg-gray-50 text-sm text-gray-600">
                <tr>
                    <th class="px-4 py-3">Locandina</th>
                    @php($viaggioSort = $sortInfo('viaggio'))
                    <th class="px-4 py-3">
                        <button type="button" data-sort-field="viaggio" class="inline-flex items-center gap-1 hover:text-gray-900">
                            <span>Viaggio</span>
                            @if ($viaggioSort['direction'] === 'asc')
                                <span aria-hidden="true">↑{{ $viaggioSort['priority'] }}</span>
                            @elseif ($viaggioSort['direction'] === 'desc')
                                <span aria-hidden="true">↓{{ $viaggioSort['priority'] }}</span>
                            @endif
                        </button>
                    </th>

                    @php($tipologiaSort = $sortInfo('tipologia'))
                    <th class="px-4 py-3">
                        <button type="button" data-sort-field="tipologia" class="inline-flex items-center gap-1 hover:text-gray-900">
                            <span>Tipologia</span>
                            @if ($tipologiaSort['direction'] === 'asc')
                                <span aria-hidden="true">↑{{ $tipologiaSort['priority'] }}</span>
                            @elseif ($tipologiaSort['direction'] === 'desc')
                                <span aria-hidden="true">↓{{ $tipologiaSort['priority'] }}</span>
                            @endif
                        </button>
                    </th>

                    @php($destinazioneSort = $sortInfo('destinazione'))
                    <th class="px-4 py-3">
                        <button type="button" data-sort-field="destinazione" class="inline-flex items-center gap-1 hover:text-gray-900">
                            <span>Destinazione</span>
                            @if ($destinazioneSort['direction'] === 'asc')
                                <span aria-hidden="true">↑{{ $destinazioneSort['priority'] }}</span>
                            @elseif ($destinazioneSort['direction'] === 'desc')
                                <span aria-hidden="true">↓{{ $destinazioneSort['priority'] }}</span>
                            @endif
                        </button>
                    </th>

                    @php($periodoSort = $sortInfo('periodo'))
                    <th class="px-4 py-3">
                        <button type="button" data-sort-field="periodo" class="inline-flex items-center gap-1 hover:text-gray-900">
                            <span>Periodo</span>
                            @if ($periodoSort['direction'] === 'asc')
                                <span aria-hidden="true">↑{{ $periodoSort['priority'] }}</span>
                            @elseif ($periodoSort['direction'] === 'desc')
                                <span aria-hidden="true">↓{{ $periodoSort['priority'] }}</span>
                            @endif
                        </button>
                    </th>

                    @php($durataSort = $sortInfo('durata'))
                    <th class="px-4 py-3">
                        <button type="button" data-sort-field="durata" class="inline-flex items-center gap-1 hover:text-gray-900">
                            <span>Durata</span>
                            @if ($durataSort['direction'] === 'asc')
                                <span aria-hidden="true">↑{{ $durataSort['priority'] }}</span>
                            @elseif ($durataSort['direction'] === 'desc')
                                <span aria-hidden="true">↓{{ $durataSort['priority'] }}</span>
                            @endif
                        </button>
                    </th>

                    @php($prezzoSort = $sortInfo('prezzo'))
                    <th class="px-4 py-3">
                        <button type="button" data-sort-field="prezzo" class="inline-flex items-center gap-1 hover:text-gray-900">
                            <span>Prezzo</span>
                            @if ($prezzoSort['direction'] === 'asc')
                                <span aria-hidden="true">↑{{ $prezzoSort['priority'] }}</span>
                            @elseif ($prezzoSort['direction'] === 'desc')
                                <span aria-hidden="true">↓{{ $prezzoSort['priority'] }}</span>
                            @endif
                        </button>
                    </th>

                    @php($minimoSort = $sortInfo('minimo'))
                    <th class="px-4 py-3">
                        <button type="button" data-sort-field="minimo" class="inline-flex items-center gap-1 hover:text-gray-900">
                            <span>Minimo</span>
                            @if ($minimoSort['direction'] === 'asc')
                                <span aria-hidden="true">↑{{ $minimoSort['priority'] }}</span>
                            @elseif ($minimoSort['direction'] === 'desc')
                                <span aria-hidden="true">↓{{ $minimoSort['priority'] }}</span>
                            @endif
                        </button>
                    </th>

                    @php($partecipantiSort = $sortInfo('partecipanti'))
                    <th class="px-4 py-3">
                        <button type="button" data-sort-field="partecipanti" class="inline-flex items-center gap-1 hover:text-gray-900">
                            <span>Partecipanti</span>
                            @if ($partecipantiSort['direction'] === 'asc')
                                <span aria-hidden="true">↑{{ $partecipantiSort['priority'] }}</span>
                            @elseif ($partecipantiSort['direction'] === 'desc')
                                <span aria-hidden="true">↓{{ $partecipantiSort['priority'] }}</span>
                            @endif
                        </button>
                    </th>
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
                @for ($indice = $viaggi->count(); $indice < 5; $indice++)
                    <tr class="h-16">
                        <td colspan="10" class="px-4 py-3 text-center text-sm text-gray-400">{{ $indice === 0 ? 'Nessun viaggio trovato.' : '' }}</td>
                    </tr>
                @endfor
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $viaggi->links() }}</div>
