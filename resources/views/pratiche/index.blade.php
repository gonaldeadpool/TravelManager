<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Pratiche</h2>
            <a href="{{ route('pratiche.create') }}" class="rounded bg-green-600 px-4 py-2 text-white">Nuova pratica</a>
        </div>
    </x-slot>

    <div class="p-6">
        @if (session('success'))
            <div class="mx-auto mb-4 max-w-7xl rounded border border-green-400 bg-green-100 px-4 py-3 text-green-700">{{ session('success') }}</div>
        @endif

        @if ($viaggioFiltrato)
            <div class="mx-auto mb-4 flex max-w-7xl items-center justify-between gap-4 rounded border border-blue-200 bg-blue-50 px-4 py-3 text-blue-800">
                <span>Pratiche del viaggio: <strong>{{ $viaggioFiltrato->nome }}</strong></span>
                <a href="{{ route('pratiche.index') }}" class="text-sm underline">Mostra tutte</a>
            </div>
        @endif

        <div class="mx-auto max-w-7xl overflow-hidden rounded bg-white shadow">
            @if ($pratiche->isEmpty())
                <p class="p-8 text-center text-gray-500">Nessuna pratica presente.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50 text-left text-gray-600">
                            <tr>
                                <th class="px-4 py-3">Viaggio</th>
                                <th class="px-4 py-3">Clienti</th>
                                <th class="px-4 py-3 text-right">Totale</th>
                                <th class="px-4 py-3 text-right">Residuo</th>
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
                                    <td class="px-4 py-3 text-right"><a href="{{ route('pratiche.edit', $pratica) }}" class="text-blue-600 hover:underline">Apri</a></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="border-t px-4 py-3">{{ $pratiche->links() }}</div>
            @endif
        </div>
    </div>
</x-app-layout>