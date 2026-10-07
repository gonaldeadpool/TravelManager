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
        <table class="table-fixed min-w-full divide-y divide-gray-200 text-sm md:table-auto">
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
                    <th class="hidden px-4 py-3 text-right sm:table-cell">
                        <button type="button" data-sort-field="totale" data-sort-current="{{ $totaleSort['direction'] ?? '' }}" class="inline-flex items-center gap-1 rounded text-right hover:text-gray-900" title="Ordina per totale">
                            <span>Totale</span>
                            @if ($totaleSort['direction'] === 'asc')
                                <span aria-hidden="true">↑{{ $totaleSort['priority'] }}</span>
                            @elseif ($totaleSort['direction'] === 'desc')
                                <span aria-hidden="true">↓{{ $totaleSort['priority'] }}</span>
                            @endif
                        </button>
                    </th>

                    @php($accontoSort = $sortInfo('acconto'))
                    <th class="hidden px-4 py-3 text-right md:table-cell">
                        <button type="button" data-sort-field="acconto" data-sort-current="{{ $accontoSort['direction'] ?? '' }}" class="inline-flex items-center gap-1 rounded text-right hover:text-gray-900" title="Ordina per acconto">
                            <span>Acconto</span>
                            @if ($accontoSort['direction'] === 'asc')
                                <span aria-hidden="true">↑{{ $accontoSort['priority'] }}</span>
                            @elseif ($accontoSort['direction'] === 'desc')
                                <span aria-hidden="true">↓{{ $accontoSort['priority'] }}</span>
                            @endif
                        </button>
                    </th>

                    @php($residuoSort = $sortInfo('residuo'))
                    <th class="hidden px-4 py-3 text-right lg:table-cell">
                        <button type="button" data-sort-field="residuo" data-sort-current="{{ $residuoSort['direction'] ?? '' }}" class="inline-flex items-center gap-1 rounded text-right hover:text-gray-900" title="Ordina per residuo">
                            <span>Residuo</span>
                            @if ($residuoSort['direction'] === 'asc')
                                <span aria-hidden="true">↑{{ $residuoSort['priority'] }}</span>
                            @elseif ($residuoSort['direction'] === 'desc')
                                <span aria-hidden="true">↓{{ $residuoSort['priority'] }}</span>
                            @endif
                        </button>
                    </th>
                    <th class="hidden px-4 py-3 text-center sm:table-cell">Note</th>
                    <th class="hidden px-4 py-3 lg:table-cell"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @foreach ($pratiche as $pratica)
                    <tr>
                        <td class="break-words px-2 py-3 font-medium sm:px-4">{{ $pratica->viaggio->nome }}</td>
                        <td class="break-words px-2 py-3 sm:px-4"><a href="{{ route('pratiche.show', $pratica) }}" class="font-medium text-blue-700 hover:underline" aria-label="Apri il riepilogo della pratica {{ $pratica->id }}">{{ $pratica->clienti->map(fn ($cliente) => $cliente->cognome . ' ' . $cliente->nome)->join(', ') }}</a></td>
                        <td class="hidden px-4 py-3 text-right sm:table-cell">{{ number_format($pratica->totale, 2, ',', '.') }} EUR</td>
                        <td class="hidden px-4 py-3 text-right md:table-cell">{{ number_format($pratica->acconto, 2, ',', '.') }} EUR</td>
                        <td class="hidden px-4 py-3 text-right lg:table-cell">{{ number_format($pratica->totale - $pratica->acconto - $pratica->saldo, 2, ',', '.') }} EUR</td>
                        <td class="hidden px-4 py-3 text-center sm:table-cell"><x-note-tooltip :note="$pratica->note" /></td>
                        <td class="hidden px-4 py-3 lg:table-cell"><div class="flex justify-end gap-2">
                            <button type="button" onclick="const dialog = document.getElementById('pdf-pratica-dialog-{{ $pratica->id }}'); const frame = document.getElementById('pdf-pratica-frame-{{ $pratica->id }}'); if (!frame.dataset.loaded) { frame.src = frame.dataset.src; frame.dataset.loaded = 'true'; } dialog.showModal()" title="Stampa pratica" aria-label="Anteprima di stampa pratica {{ $pratica->id }}" class="inline-flex h-8 w-8 items-center justify-center rounded text-gray-700 hover:bg-gray-100"><svg aria-hidden="true" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 0-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg></button>
                            <button type="button" onclick="document.getElementById('email-pratica-dialog-{{ $pratica->id }}').showModal()" title="Invia pratica via email" aria-label="Invia pratica {{ $pratica->id }} via email" class="inline-flex h-8 w-8 items-center justify-center rounded text-gray-700 hover:bg-gray-100"><svg aria-hidden="true" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/></svg></button>
                            <a href="{{ route('pratiche.edit', $pratica) }}" title="Modifica pratica" aria-label="Modifica pratica" class="inline-flex h-8 w-8 items-center justify-center rounded text-blue-600 hover:bg-blue-50"><svg aria-hidden="true" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9" /><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4Z" /></svg></a>
                            <form method="POST" action="{{ route('pratiche.destroy', $pratica) }}" onsubmit="return confirm('Vuoi eliminare questa pratica?');">@csrf @method('DELETE')<button type="submit" title="Elimina pratica" aria-label="Elimina pratica" class="inline-flex h-8 w-8 items-center justify-center rounded text-red-600 hover:bg-red-50"><svg aria-hidden="true" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18" /><path d="M8 6V4h8v2" /><path d="M19 6l-1 14H6L5 6" /><path d="M10 11v5M14 11v5" /></svg></button></form>
                        </div>
                        @include('pratiche._pdf-dialog', ['pratica' => $pratica])
                        @include('pratiche._email-dialog', ['pratica' => $pratica])
                        </td>
                    </tr>
                @endforeach
                @for ($indice = $pratiche->count(); $indice < 5; $indice++)
                    <tr class="h-16">
                        <td class="px-2 py-3 sm:px-4"></td>
                        <td class="px-2 py-3 sm:px-4"></td>
                        <td class="hidden px-4 py-3 sm:table-cell"></td>
                        <td class="hidden px-4 py-3 md:table-cell"></td>
                        <td class="hidden px-4 py-3 lg:table-cell"></td>
                        <td class="hidden px-4 py-3 sm:table-cell"></td>
                        <td class="hidden px-4 py-3 lg:table-cell"></td>
                    </tr>
                @endfor
            </tbody>
        </table>
        @if ($pratiche->isEmpty())
            <p class="px-4 py-6 text-center text-sm text-gray-400">Nessuna pratica trovata.</p>
        @endif
    </div>
    <div class="border-t px-4 py-3">{{ $pratiche->links() }}</div>