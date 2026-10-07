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
        <table class="table-fixed w-full text-left md:table-auto">
            <thead class="border-b bg-gray-50 text-sm text-gray-600">
                <tr>
                    @php
                        $clienteSort = $sortInfo('cliente');
                    @endphp
                    <th class="px-4 py-3">
                        <button type="button" data-sort-field="cliente" class="inline-flex items-center gap-1 hover:text-gray-900">
                            <span>Cliente</span>
                            @if ($clienteSort['direction'] === 'asc')
                                <span aria-hidden="true">↑{{ $clienteSort['priority'] }}</span>
                            @elseif ($clienteSort['direction'] === 'desc')
                                <span aria-hidden="true">↓{{ $clienteSort['priority'] }}</span>
                            @endif
                        </button>
                    </th>

                    @php
                        $cellulareSort = $sortInfo('cellulare');
                    @endphp
                    <th class="px-2 py-3 sm:px-4">
                        <button type="button" data-sort-field="cellulare" class="inline-flex items-center gap-1 hover:text-gray-900">
                            <span>Cellulare</span>
                            @if ($cellulareSort['direction'] === 'asc')
                                <span aria-hidden="true">↑{{ $cellulareSort['priority'] }}</span>
                            @elseif ($cellulareSort['direction'] === 'desc')
                                <span aria-hidden="true">↓{{ $cellulareSort['priority'] }}</span>
                            @endif
                        </button>
                    </th>

                    @php
                        $emailSort = $sortInfo('email');
                    @endphp
                    <th class="px-2 py-3 sm:px-4">
                        <button type="button" data-sort-field="email" class="inline-flex items-center gap-1 hover:text-gray-900">
                            <span>Email</span>
                            @if ($emailSort['direction'] === 'asc')
                                <span aria-hidden="true">↑{{ $emailSort['priority'] }}</span>
                            @elseif ($emailSort['direction'] === 'desc')
                                <span aria-hidden="true">↓{{ $emailSort['priority'] }}</span>
                            @endif
                        </button>
                    </th>

                    @php
                        $documentiSort = $sortInfo('documenti');
                    @endphp
                    <th class="hidden px-4 py-3 lg:table-cell">
                        <button type="button" data-sort-field="documenti" class="inline-flex items-center gap-1 hover:text-gray-900">
                            <span>Documenti</span>
                            @if ($documentiSort['direction'] === 'asc')
                                <span aria-hidden="true">↑{{ $documentiSort['priority'] }}</span>
                            @elseif ($documentiSort['direction'] === 'desc')
                                <span aria-hidden="true">↓{{ $documentiSort['priority'] }}</span>
                            @endif
                        </button>
                    </th>
                    <th class="hidden px-4 py-3 text-right md:table-cell">Azioni</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @foreach ($clienti as $cliente)
                    <tr>
                        <td class="break-words px-2 py-3 font-medium sm:px-4"><a href="{{ route('clienti.riepilogo', $cliente) }}" class="text-blue-600 hover:underline">{{ $cliente->cognome }} {{ $cliente->nome }}</a></td>
                        <td class="break-words px-2 py-3 sm:px-4"><x-contact-phone :number="$cliente->cellulare" /></td>
                        <td class="break-all px-2 py-3 sm:px-4 sm:break-normal"><x-contact-email :cliente="$cliente" /></td>
                        <td class="hidden px-4 py-3 lg:table-cell">
                            @php
                                $documentiPerTipo = $cliente->documenti->keyBy('tipo');
                            @endphp
                            <div class="flex items-center gap-2">
                                @foreach ([
                                    'carta_identita' => ['label' => 'CI', 'title' => "Anteprima carta d'identità", 'class' => 'bg-sky-500 hover:bg-sky-600'],
                                    'passaporto' => ['label' => 'PP', 'title' => 'Anteprima passaporto', 'class' => 'bg-red-500 hover:bg-red-600'],
                                    'patente' => ['label' => 'P', 'title' => 'Anteprima patente', 'class' => 'bg-pink-400 hover:bg-pink-500'],
                                ] as $tipo => $icona)
                                    @if ($documentiPerTipo->has($tipo))
                                        @php
                                            $documento = $documentiPerTipo->get($tipo);
                                            $soglia = $scadenzeDocumenti[$tipo] ?? $scadenzeDocumenti['altro'];
                                            $inScadenza = $documento->scadenza && $documento->scadenza->lte(now()->startOfDay()->addDays($soglia));
                                        @endphp
                                        <a href="{{ route('clienti.documenti.download', [$cliente, $documento]) }}" target="_blank" title="{{ $inScadenza ? 'Documento in scadenza' : $icona['title'] }}" aria-label="{{ $inScadenza ? 'Documento in scadenza' : $icona['title'] }}" class="inline-flex h-8 w-8 items-center justify-center text-xs font-bold transition {{ $inScadenza ? '' : 'rounded-full ' . $icona['class'] }}" style="color: {{ $inScadenza ? '#854d0e' : '#ffffff' }}; background-color: {{ $inScadenza ? '#facc15' : ($tipo === 'carta_identita' ? '#0ea5e9' : ($tipo === 'passaporto' ? '#ef4444' : '#ec4899')) }}; {{ $inScadenza ? 'clip-path: polygon(50% 0%, 100% 100%, 0% 100%);' : '' }}">
                                            @if ($inScadenza)
                                                <span aria-hidden="true" style="padding-top: 9px; color: #854d0e; font-size: 9px; line-height: 1;">{{ $icona['label'] }}</span>
                                            @else
                                                {{ $icona['label'] }}
                                            @endif
                                        </a>
                                    @endif
                                @endforeach
                                @if ($documentiPerTipo->intersectByKeys(['carta_identita' => true, 'passaporto' => true, 'patente' => true])->isEmpty())
                                    <span class="text-sm text-gray-400">-</span>
                                @endif
                            </div>
                        </td>
                        <td class="hidden px-4 py-3 md:table-cell">
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
                @for ($indice = $clienti->count(); $indice < 5; $indice++)
                    <tr class="h-16">
                        <td class="px-2 py-3 sm:px-4"></td>
                        <td class="px-2 py-3 sm:px-4"></td>
                        <td class="px-2 py-3 sm:px-4"></td>
                        <td class="hidden px-4 py-3 lg:table-cell"></td>
                        <td class="hidden px-4 py-3 md:table-cell"></td>
                    </tr>
                @endfor
            </tbody>
        </table>
        @if ($clienti->isEmpty())
            <p class="px-4 py-6 text-center text-sm text-gray-400">Nessun cliente trovato.</p>
        @endif
    </div>

    <div class="mt-4">{{ $clienti->links() }}</div>
