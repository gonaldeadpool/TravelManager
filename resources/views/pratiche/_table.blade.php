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

<div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50 text-left text-gray-600">
                <tr>
                    @php($viaggioSort = $sortInfo('viaggio'))
                    <th class="px-4 py-3">
                        <button type="button" data-sort-field="viaggio" data-sort-current="{{ $viaggioSort['direction'] ?? '' }}" class="inline-flex items-center gap-1 rounded text-left hover:text-gray-900" title="Ordina per viaggio">
                            <span>Viaggio</span>
                            @if ($viaggioSort['direction'] === 'asc')
                                <span aria-hidden="true">↑{{ $viaggioSort['priority'] }}</span>
                            @elseif ($viaggioSort['direction'] === 'desc')
                                <span aria-hidden="true">↓{{ $viaggioSort['priority'] }}</span>
                            @endif
                        </button>
                    </th>

                    @php($clientiSort = $sortInfo('clienti'))
                    <th class="px-4 py-3">
                        <button type="button" data-sort-field="clienti" data-sort-current="{{ $clientiSort['direction'] ?? '' }}" class="inline-flex items-center gap-1 rounded text-left hover:text-gray-900" title="Ordina per clienti">
                            <span>Clienti</span>
                            @if ($clientiSort['direction'] === 'asc')
                                <span aria-hidden="true">↑{{ $clientiSort['priority'] }}</span>
                            @elseif ($clientiSort['direction'] === 'desc')
                                <span aria-hidden="true">↓{{ $clientiSort['priority'] }}</span>
                            @endif
                        </button>
                    </th>

                    @php($totaleSort = $sortInfo('totale'))
                    <th class="px-4 py-3 text-right">
                        <button type="button" data-sort-field="totale" data-sort-current="{{ $totaleSort['direction'] ?? '' }}" class="inline-flex items-center gap-1 rounded text-right hover:text-gray-900" title="Ordina per totale">
                            <span>Totale</span>
                            @if ($totaleSort['direction'] === 'asc')
                                <span aria-hidden="true">↑{{ $totaleSort['priority'] }}</span>
                            @elseif ($totaleSort['direction'] === 'desc')
                                <span aria-hidden="true">↓{{ $totaleSort['priority'] }}</span>
                            @endif
                        </button>
                    </th>

                    @php($residuoSort = $sortInfo('residuo'))
                    <th class="px-4 py-3 text-right">
                        <button type="button" data-sort-field="residuo" data-sort-current="{{ $residuoSort['direction'] ?? '' }}" class="inline-flex items-center gap-1 rounded text-right hover:text-gray-900" title="Ordina per residuo">
                            <span>Residuo</span>
                            @if ($residuoSort['direction'] === 'asc')
                                <span aria-hidden="true">↑{{ $residuoSort['priority'] }}</span>
                            @elseif ($residuoSort['direction'] === 'desc')
                                <span aria-hidden="true">↓{{ $residuoSort['priority'] }}</span>
                            @endif
                        </button>
                    </th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @foreach ($pratiche as $pratica)
                    <tr>
                        <td class="px-4 py-3 font-medium">{{ $pratica->viaggio->nome }}</td>
                        <td class="px-4 py-3">{{ $pratica->clienti->map(fn ($cliente) => $cliente->cognome . ' ' . $cliente->nome)->join(', ') }}</td>
                        <td class="px-4 py-3 text-right">{{ number_format($pratica->totale, 2, ',', '.') }} EUR</td>
                        <td class="px-4 py-3 text-right">{{ number_format($pratica->totale - $pratica->acconto - $pratica->saldo, 2, ',', '.') }} EUR</td>
                        <td class="px-4 py-3"><div class="flex justify-end gap-2">
                            <a href="{{ route('pratiche.edit', $pratica) }}" title="Modifica pratica" aria-label="Modifica pratica" class="inline-flex h-8 w-8 items-center justify-center rounded text-blue-600 hover:bg-blue-50"><svg aria-hidden="true" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9" /><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4Z" /></svg></a>
                            <form method="POST" action="{{ route('pratiche.destroy', $pratica) }}" onsubmit="return confirm('Vuoi eliminare questa pratica?');">@csrf @method('DELETE')<button type="submit" title="Elimina pratica" aria-label="Elimina pratica" class="inline-flex h-8 w-8 items-center justify-center rounded text-red-600 hover:bg-red-50"><svg aria-hidden="true" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18" /><path d="M8 6V4h8v2" /><path d="M19 6l-1 14H6L5 6" /><path d="M10 11v5M14 11v5" /></svg></button></form>
                        </div></td>
                    </tr>
                @endforeach
                @for ($indice = $pratiche->count(); $indice < 5; $indice++)
                    <tr class="h-16">
                        <td colspan="5" class="px-4 py-3 text-center text-sm text-gray-400">{{ $indice === 0 ? 'Nessuna pratica trovata.' : '' }}</td>
                    </tr>
                @endfor
            </tbody>
        </table>
    </div>
    <div class="border-t px-4 py-3">{{ $pratiche->links() }}</div>