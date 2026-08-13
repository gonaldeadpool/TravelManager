<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <h2 class="text-xl font-semibold leading-tight text-gray-800">{{ $viaggio->nome }}</h2>
            <a href="{{ route('viaggi.index') }}" class="rounded border px-4 py-2 text-sm text-gray-700">Torna ai viaggi</a>
        </div>
    </x-slot>

    <div class="p-6" x-data="{ tab: 'riepilogo' }">
        <div class="mx-auto mb-6 max-w-6xl border-b border-gray-200">
            <nav class="flex gap-6" aria-label="Sezioni viaggio">
                <button type="button" @click="tab = 'riepilogo'" :class="tab === 'riepilogo' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700'" class="border-b-2 px-1 pb-3 text-sm font-semibold">Riepilogo</button>
                <button type="button" @click="tab = 'partecipanti'" :class="tab === 'partecipanti' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700'" class="border-b-2 px-1 pb-3 text-sm font-semibold">Partecipanti</button>
            </nav>
        </div>

        <div x-show="tab === 'riepilogo'" class="mx-auto grid max-w-6xl grid-cols-1 gap-6 lg:grid-cols-[280px_minmax(0,1fr)]">
            <aside class="rounded bg-white p-6 shadow">
                @if ($viaggio->locandina)
                    <img src="{{ route('viaggi.locandina', $viaggio) }}" alt="Locandina di {{ $viaggio->nome }}" class="max-h-[360px] w-full rounded object-contain">
                @else
                    <div class="flex min-h-[240px] items-center justify-center rounded border border-dashed bg-gray-50 p-4 text-center text-sm text-gray-500">Nessuna locandina disponibile.</div>
                @endif
            </aside>

            <div class="space-y-6">
                <section class="rounded bg-white p-6 shadow">
                    <h3 class="mb-4 text-lg font-semibold">Informazioni del viaggio</h3>
                    <dl class="grid grid-cols-1 gap-4 text-sm md:grid-cols-2">
                        <div><dt class="text-gray-500">Tipologia</dt><dd class="mt-1 font-medium">{{ ucfirst($viaggio->tipologia) }}</dd></div>
                        <div><dt class="text-gray-500">Destinazione</dt><dd class="mt-1 font-medium">{{ $viaggio->destinazione }}</dd></div>
                        <div><dt class="text-gray-500">Periodo</dt><dd class="mt-1 font-medium">{{ $viaggio->data_partenza->format('d/m/Y') }} - {{ $viaggio->data_rientro->format('d/m/Y') }}</dd></div>
                        <div><dt class="text-gray-500">Durata</dt><dd class="mt-1 font-medium">{{ $viaggio->data_partenza->diffInDays($viaggio->data_rientro) + 1 }} giorni, {{ $viaggio->data_partenza->diffInDays($viaggio->data_rientro) }} notti</dd></div>
                        <div><dt class="text-gray-500">Prezzo a persona</dt><dd class="mt-1 font-medium">{{ $viaggio->prezzo !== null ? number_format($viaggio->prezzo, 2, ',', '.') . ' EUR' : '-' }}</dd></div>
                        <div><dt class="text-gray-500">Minimo partecipanti</dt><dd class="mt-1 font-medium">{{ $viaggio->minimo_partecipanti }}</dd></div>
                        <div><dt class="text-gray-500">Massimo partecipanti</dt><dd class="mt-1 font-medium">{{ $viaggio->massimo_partecipanti ?? '-' }}</dd></div>
                        <div><dt class="text-gray-500">Numero partecipanti</dt><dd class="mt-1 font-medium">{{ $numeroPartecipanti }}</dd></div>
                        <div><dt class="text-gray-500">Data acconto</dt><dd class="mt-1 font-medium">{{ $viaggio->data_acconto?->format('d/m/Y') ?? '-' }}</dd></div>
                        <div><dt class="text-gray-500">Importo acconto</dt><dd class="mt-1 font-medium">{{ number_format($importoAcconto, 2, ',', '.') }} EUR</dd></div>
                        <div><dt class="text-gray-500">Data saldo</dt><dd class="mt-1 font-medium">{{ $viaggio->data_saldo?->format('d/m/Y') ?? '-' }}</dd></div>
                        <div class="md:col-span-2"><dt class="text-gray-500">Note</dt><dd class="mt-1 whitespace-pre-line font-medium">{{ $viaggio->note ?: '-' }}</dd></div>
                    </dl>
                </section>

                <section class="rounded bg-white p-6 shadow">
                    <h3 class="mb-3 text-lg font-semibold">Trasporti</h3>
                    <div class="space-y-2">
                        @forelse ($viaggio->trasporti ?? [] as $trasporto)
                            <p class="rounded border p-3 text-sm">{{ ucfirst($trasporto['tipo'] ?? '') }} @if (!empty($trasporto['posti'])) - {{ $trasporto['posti'] }} posti @endif</p>
                        @empty
                            <p class="text-sm text-gray-500">Nessun trasporto configurato.</p>
                        @endforelse
                    </div>
                </section>

                <section class="rounded bg-white p-6 shadow">
                    <h3 class="mb-3 text-lg font-semibold">Sistemazioni</h3>
                    <div class="space-y-2">
                        @forelse ($viaggio->sistemazioni ?? [] as $sistemazione)
                            <p class="rounded border p-3 text-sm">{{ ucfirst($sistemazione['tipo'] ?? '') }} {{ $sistemazione['formato'] ?? '' }} @if (!empty($sistemazione['quantita'])) - {{ $sistemazione['quantita'] }} disponibili @endif</p>
                        @empty
                            <p class="text-sm text-gray-500">Nessuna sistemazione configurata.</p>
                        @endforelse
                    </div>
                </section>
            </div>
        </div>

        <div x-show="tab === 'partecipanti'" x-cloak class="mx-auto max-w-6xl overflow-hidden rounded bg-white shadow">
            @if ($viaggio->pratiche->flatMap->clienti->isEmpty())
                <p class="p-8 text-center text-gray-500">Nessun partecipante associato a questo viaggio.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50 text-left text-gray-600"><tr><th class="px-4 py-3">Cliente</th><th class="px-4 py-3">Email</th><th class="px-4 py-3">Telefono</th><th class="px-4 py-3 text-right">Pratica</th></tr></thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach ($viaggio->pratiche as $pratica)
                                @foreach ($pratica->clienti as $cliente)
                                    <tr>
                                        <td class="px-4 py-3 font-medium">{{ $cliente->cognome }} {{ $cliente->nome }}</td>
                                        <td class="px-4 py-3">{{ $cliente->email ?: '-' }}</td>
                                        <td class="px-4 py-3">{{ $cliente->telefono ?: '-' }}</td>
                                        <td class="px-4 py-3 text-right"><a href="{{ route('pratiche.edit', $pratica) }}" class="text-blue-600 hover:underline">Apri pratica #{{ $pratica->id }}</a></td>
                                    </tr>
                                @endforeach
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>