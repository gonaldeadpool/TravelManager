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
                        <td class="break-words px-2 py-3 sm:px-4">
                            @php
                                $cellulare = $cliente->cellulare ?? '';
                                $numeroTelefono = preg_replace('/(?!^\+)[^\d]/', '', $cellulare);
                                $numeroWhatsApp = preg_replace('/\D+/', '', $cellulare);
                            @endphp
                            <div class="md:hidden" x-data="{ open: false }" @click.outside="open = false">
                                @if ($cellulare)
                                    <button type="button" @click="open = !open" :aria-expanded="open" class="break-words text-left text-blue-600 underline decoration-dotted underline-offset-2">
                                        {{ $cellulare }}
                                    </button>
                                    <div x-cloak x-show="open" class="mt-2 flex gap-2">
                                        <a href="tel:{{ $numeroTelefono }}" aria-label="Chiama {{ $cellulare }}" title="Chiama" class="inline-flex h-9 w-9 items-center justify-center rounded-full bg-green-100 text-green-700 hover:bg-green-200">
                                            <svg aria-hidden="true" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.8 19.8 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.12 3.18 2 2 0 0 1 4.11 1h3a2 2 0 0 1 2 1.72c.12.96.35 1.9.69 2.8a2 2 0 0 1-.45 2.11L8.08 8.92a16 16 0 0 0 6 6l1.29-1.27a2 2 0 0 1 2.11-.45c.9.34 1.84.57 2.8.69A2 2 0 0 1 22 16.92z" />
                                            </svg>
                                        </a>
                                        <a href="https://wa.me/{{ $numeroWhatsApp }}" target="_blank" rel="noopener noreferrer" aria-label="Apri WhatsApp per {{ $cellulare }}" title="WhatsApp" class="inline-flex h-9 w-9 items-center justify-center rounded-full bg-green-100 text-green-700 hover:bg-green-200">
                                            <svg aria-hidden="true" class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor">
                                                <path d="M20.52 3.48A11.86 11.86 0 0 0 12.08 0C5.5 0 .14 5.35.14 11.94c0 2.1.55 4.15 1.6 5.96L.05 24l6.25-1.64a11.9 11.9 0 0 0 5.77 1.47h.01c6.58 0 11.94-5.36 11.94-11.94 0-3.19-1.24-6.19-3.5-8.41ZM12.08 21.8h-.01a9.9 9.9 0 0 1-5.04-1.38l-.36-.21-3.71.97.99-3.62-.24-.37a9.87 9.87 0 0 1-1.52-5.25c0-5.46 4.44-9.9 9.9-9.9a9.83 9.83 0 0 1 7 2.9 9.83 9.83 0 0 1 2.9 7c0 5.46-4.44 9.9-9.91 9.9Zm5.43-7.41c-.3-.15-1.77-.87-2.05-.97-.28-.1-.47-.15-.67.15-.2.3-.77.97-.94 1.17-.17.2-.35.22-.65.07-.3-.15-1.26-.46-2.4-1.47-.89-.79-1.49-1.77-1.66-2.07-.17-.3-.02-.46.13-.61.13-.13.3-.35.45-.52.15-.17.2-.3.3-.5.1-.2.05-.37-.02-.52-.08-.15-.67-1.62-.92-2.22-.24-.58-.49-.5-.67-.51h-.57c-.2 0-.52.08-.8.37-.27.3-1.04 1.02-1.04 2.49s1.07 2.89 1.22 3.09c.15.2 2.1 3.2 5.08 4.49.71.31 1.27.5 1.7.64.72.23 1.37.2 1.89.12.58-.09 1.77-.72 2.02-1.42.25-.7.25-1.3.17-1.42-.07-.13-.27-.2-.57-.35Z" />
                                            </svg>
                                        </a>
                                    </div>
                                @else
                                    <span>-</span>
                                @endif
                            </div>
                            <span class="hidden md:inline">{{ $cellulare ?: '-' }}</span>
                        </td>
                        <td class="break-all px-2 py-3 sm:px-4 sm:break-normal">{{ $cliente->email ?: '-' }}</td>
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
                        <td colspan="5" class="px-4 py-3 text-center text-sm text-gray-400">{{ $indice === 0 ? 'Nessun cliente trovato.' : '' }}</td>
                    </tr>
                @endfor
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $clienti->links() }}</div>
