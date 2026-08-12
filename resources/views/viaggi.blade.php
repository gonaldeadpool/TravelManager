<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Viaggi</h2>
    </x-slot>

    <div class="p-6">
        @if (session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif

        <div class="flex items-center justify-between gap-4 mb-6">
            <div>
                <h3 class="text-lg font-semibold">Elenco viaggi</h3>
                <p class="text-sm text-gray-500 mt-1">Gestisci programmi, disponibilità e locandine.</p>
            </div>
            <a href="{{ route('viaggi.create') }}" class="bg-green-600 text-white px-4 py-2 rounded whitespace-nowrap">Nuovo viaggio</a>
        </div>

        @if ($viaggi->isEmpty())
            <div class="bg-white shadow rounded p-8 text-center text-gray-500">Nessun viaggio presente.</div>
        @else
            <div class="overflow-x-auto bg-white shadow rounded">
                <table class="w-full text-left">
                    <thead class="border-b bg-gray-50 text-sm text-gray-600">
                        <tr>
                            <th class="px-4 py-3">Viaggio</th>
                            <th class="px-4 py-3">Organizzazione</th>
                            <th class="px-4 py-3">Destinazione</th>
                            <th class="px-4 py-3">Periodo</th>
                            <th class="px-4 py-3 text-right">Azioni</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @foreach ($viaggi as $viaggio)
                            <tr>
                                <td class="px-4 py-3 font-medium">{{ $viaggio->nome }}</td>
                                <td class="px-4 py-3">{{ $viaggio->gestione === 'tour_operator' ? 'Tour operator' : 'Interno' }}</td>
                                <td class="px-4 py-3">{{ $viaggio->destinazione }}</td>
                                <td class="px-4 py-3 whitespace-nowrap">{{ $viaggio->data_partenza->format('d/m/Y') }} - {{ $viaggio->data_rientro->format('d/m/Y') }}</td>
                                <td class="px-4 py-3">
                                    <div class="flex justify-end gap-3">
                                        <a href="{{ route('viaggi.edit', $viaggio) }}" class="text-blue-600 hover:underline">Modifica</a>
                                        <form method="POST" action="{{ route('viaggi.destroy', $viaggio) }}" onsubmit="return confirm('Vuoi eliminare questo viaggio?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:underline">Cancella</button>
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
    </div>
</x-app-layout>
