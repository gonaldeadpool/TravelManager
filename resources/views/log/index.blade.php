<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">Log</h2>
    </x-slot>

    <div class="p-6">
        <p class="mx-auto mb-4 max-w-5xl text-sm text-gray-500">Scarica i file di log per analizzare eventuali errori dell'applicazione o del server.</p>

        <div class="mx-auto max-w-5xl overflow-x-auto rounded bg-white shadow">
            <table class="w-full text-left">
                <thead class="border-b bg-gray-50 text-sm text-gray-600">
                    <tr>
                        <th class="px-4 py-3">Log</th>
                        <th class="px-4 py-3">Dimensione</th>
                        <th class="px-4 py-3 text-right">Azioni</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @foreach ($log as $voce)
                        <tr>
                            <td class="px-4 py-3 font-medium">{{ $voce['etichetta'] }}</td>
                            <td class="px-4 py-3">{{ $voce['esiste'] ? number_format($voce['dimensione'] / 1024, 1) . ' KB' : '-' }}</td>
                            <td class="px-4 py-3 text-right">
                                @if ($voce['esiste'])
                                    <a href="{{ route('log.download', $voce['tipo']) }}" class="text-blue-600 hover:underline">Scarica</a>
                                @else
                                    <span class="text-gray-400">Non disponibile</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
