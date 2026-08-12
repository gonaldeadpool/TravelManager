<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Amministrazione</h2>
    </x-slot>

    <div class="p-6">
        @if (session('success'))
            <div class="mb-4 rounded border border-green-400 bg-green-100 px-4 py-3 text-green-700">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-4 rounded border border-red-400 bg-red-100 px-4 py-3 text-red-700">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="mx-auto max-w-4xl" x-data="{ tab: 'tecnica' }">
            <div class="mb-6 border-b border-gray-200">
                <nav class="flex gap-6" aria-label="Sezioni amministrazione">
                    <button type="button" @click="tab = 'tecnica'" :class="tab === 'tecnica' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700'" class="border-b-2 px-1 pb-3 text-sm font-semibold">Scheda tecnica</button>
                    <button type="button" @click="tab = 'configurazione'" :class="tab === 'configurazione' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700'" class="border-b-2 px-1 pb-3 text-sm font-semibold">Configurazione</button>
                </nav>
            </div>

            <form method="POST" action="{{ route('amministrazione.update') }}" class="space-y-6">
                @csrf
                @method('PUT')

                <div x-show="tab === 'tecnica'" x-cloak class="rounded bg-white p-6 shadow">
                    <h4 class="mb-4 font-semibold">Percorsi di archiviazione</h4>

                    <div class="space-y-4">
                        <div>
                            <label for="locandine_path" class="mb-1 block">Cartella locandine</label>
                            <input id="locandine_path" type="text" name="locandine_path" value="{{ old('locandine_path', $locandinePath) }}" required class="w-full rounded border p-2">
                            <p class="mt-1 text-sm text-gray-500">Percorso locale assoluto o relativo alla radice del progetto.</p>
                        </div>

                        <div>
                            <label for="documenti_path" class="mb-1 block">Cartella documenti</label>
                            <input id="documenti_path" type="text" name="documenti_path" value="{{ old('documenti_path', $documentiPath) }}" required class="w-full rounded border p-2">
                            <p class="mt-1 text-sm text-gray-500">I documenti saranno accessibili solo agli utenti autenticati.</p>
                        </div>
                    </div>
                </div>

                <div x-show="tab === 'configurazione'" x-cloak class="rounded bg-white p-6 shadow">
                    <h4 class="mb-4 font-semibold">Scadenza documenti</h4>
                    <p class="mb-4 text-sm text-gray-500">Indica con quanti giorni di anticipo evidenziare i documenti in scadenza nell'elenco clienti.</p>

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        @foreach ([
                            'carta_identita' => "Carta d'identità",
                            'passaporto' => 'Passaporto',
                            'patente' => 'Patente',
                            'altro' => 'Altri documenti',
                        ] as $tipo => $label)
                            <div>
                                <label for="scadenza_{{ $tipo }}" class="mb-1 block">{{ $label }}</label>
                                <div class="relative">
                                    <input id="scadenza_{{ $tipo }}" type="number" name="scadenza_{{ $tipo }}" min="0" max="3650" required value="{{ old('scadenza_' . $tipo, $scadenze[$tipo]) }}" class="w-full rounded border p-2 pr-16">
                                    <span class="absolute right-3 top-2 text-gray-500">giorni</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <button type="submit" class="rounded bg-blue-600 px-4 py-2 text-white">Salva configurazione</button>
            </form>
        </div>
    </div>
</x-app-layout>
