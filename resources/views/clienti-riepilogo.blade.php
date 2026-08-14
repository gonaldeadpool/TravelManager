<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <h2 class="text-xl font-semibold leading-tight text-gray-800">{{ $cliente->cognome }} {{ $cliente->nome }}</h2>
            <a href="{{ route('clienti') }}" class="rounded border px-4 py-2 text-sm text-gray-700">Torna ai clienti</a>
        </div>
    </x-slot>

    <div class="p-6" x-data="{ tab: 'riepilogo' }">
        <div class="mx-auto mb-6 max-w-6xl border-b border-gray-200">
            <nav class="flex gap-6" aria-label="Sezioni cliente">
                <button type="button" @click="tab = 'riepilogo'" :class="tab === 'riepilogo' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700'" class="border-b-2 px-1 pb-3 text-sm font-semibold">Riepilogo</button>
                <button type="button" @click="tab = 'viaggi'" :class="tab === 'viaggi' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700'" class="border-b-2 px-1 pb-3 text-sm font-semibold">Viaggi</button>
            </nav>
        </div>

        <div x-show="tab === 'riepilogo'" class="mx-auto grid max-w-6xl grid-cols-1 gap-6 lg:grid-cols-[minmax(0,1fr)_280px]">
            <section class="rounded bg-white p-6 shadow">
                <h3 class="mb-4 text-lg font-semibold">Dati del cliente</h3>
                <dl class="grid grid-cols-1 gap-4 text-sm md:grid-cols-2">
                    <div><dt class="text-gray-500">Nome e cognome</dt><dd class="mt-1 font-medium">{{ $cliente->cognome }} {{ $cliente->nome }}</dd></div>
                    <div><dt class="text-gray-500">Codice fiscale</dt><dd class="mt-1 font-medium">{{ $cliente->codice_fiscale ?: '-' }}</dd></div>
                    <div><dt class="text-gray-500">Data di nascita</dt><dd class="mt-1 font-medium">{{ $cliente->data_nascita?->format('d/m/Y') ?? '-' }}</dd></div>
                    <div><dt class="text-gray-500">Luogo di nascita</dt><dd class="mt-1 font-medium">{{ $cliente->luogo_nascita ?: '-' }}</dd></div>
                    <div><dt class="text-gray-500">Email</dt><dd class="mt-1 font-medium">{{ $cliente->email ?: '-' }}</dd></div>
                    <div><dt class="text-gray-500">Telefono</dt><dd class="mt-1 font-medium">{{ $cliente->telefono ?: '-' }}</dd></div>
                    <div><dt class="text-gray-500">Cellulare</dt><dd class="mt-1 font-medium">{{ $cliente->cellulare ?: '-' }}</dd></div>
                    <div><dt class="text-gray-500">Citta</dt><dd class="mt-1 font-medium">{{ $cliente->citta ?: '-' }}</dd></div>
                    <div class="md:col-span-2"><dt class="text-gray-500">Indirizzo</dt><dd class="mt-1 font-medium">{{ trim(collect([$cliente->indirizzo, $cliente->cap, $cliente->provincia, $cliente->nazione])->filter()->implode(', ')) ?: '-' }}</dd></div>
                    <div class="md:col-span-2"><dt class="text-gray-500">Note</dt><dd class="mt-1 whitespace-pre-line font-medium">{{ $cliente->note ?: '-' }}</dd></div>
                </dl>
            </section>

            <aside class="rounded bg-white p-6 shadow">
                <h3 class="mb-4 text-lg font-semibold">Documenti</h3>
                @forelse ($cliente->documenti as $documento)
                    <a href="{{ route('clienti.documenti.download', [$cliente, $documento]) }}" target="_blank" class="mb-2 block rounded border p-3 text-sm text-blue-600 hover:bg-blue-50">{{ $documento->nome_originale }}</a>
                @empty
                    <p class="text-sm text-gray-500">Nessun documento caricato.</p>
                @endforelse
            </aside>
        </div>

        <div x-show="tab === 'viaggi'" x-cloak class="mx-auto max-w-6xl overflow-hidden rounded bg-white shadow">
            @if ($cliente->pratiche->isEmpty())
                <p class="p-8 text-center text-gray-500">Nessun viaggio associato al cliente.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50 text-left text-gray-600"><tr><th class="px-4 py-3">Viaggio</th><th class="px-4 py-3">Data</th><th class="px-4 py-3 text-right">Pratica</th></tr></thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach ($cliente->pratiche as $pratica)
                                <tr>
                                    <td class="px-4 py-3 font-medium">{{ $pratica->viaggio?->nome ?? '-' }}</td>
                                    <td class="px-4 py-3">{{ $pratica->viaggio?->data_partenza?->format('d/m/Y') ?? '-' }}</td>
                                    <td class="px-4 py-3 text-right"><a href="{{ route('pratiche.edit', $pratica) }}" class="text-blue-600 hover:underline">Apri pratica #{{ $pratica->id }}</a></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
