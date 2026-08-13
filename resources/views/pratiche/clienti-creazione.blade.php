<x-app-layout>
    <x-slot name="header"><h2 class="text-xl font-semibold leading-tight text-gray-800">Seleziona clienti per la nuova pratica</h2></x-slot>

    <div class="p-6">
        @if ($errors->any())
            <div class="mx-auto mb-4 max-w-7xl rounded border border-red-400 bg-red-100 px-4 py-3 text-red-700"><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
        @endif
        <div class="mx-auto max-w-7xl">
            <div class="mb-6 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div><h3 class="text-lg font-semibold">Elenco clienti</h3><p class="mt-1 text-sm text-gray-500">Seleziona i partecipanti disponibili per {{ $viaggioSelezionato?->nome ?? 'il viaggio selezionato' }}.</p></div>
                <div class="flex gap-3"><a href="{{ route('clienti.create') }}" target="_blank" class="rounded bg-green-600 px-4 py-2 text-white">Nuova anagrafica</a><a href="{{ route('pratiche.create', ['bozza' => 1]) }}" class="rounded border px-4 py-2 text-gray-700">Torna alla pratica</a></div>
            </div>
            <form method="GET" class="mb-4 flex gap-2"><label for="ricerca-clienti" class="sr-only">Cerca cliente</label><input id="ricerca-clienti" type="search" name="ricerca" value="{{ $ricerca }}" placeholder="Cerca per nome, cognome o email" class="w-full rounded border px-3 py-2 md:max-w-md"><button class="rounded border px-4 py-2 text-gray-700">Cerca</button></form>
            <form method="POST" action="{{ route('pratiche.creazione.clienti.store') }}">
                @csrf
                <div class="overflow-x-auto rounded bg-white shadow">
                    <table class="w-full text-left"><thead class="border-b bg-gray-50 text-sm text-gray-600"><tr><th class="w-12 px-4 py-3"><span class="sr-only">Seleziona</span></th><th class="px-4 py-3">Nome</th><th class="px-4 py-3">Cognome</th><th class="px-4 py-3">Email</th><th class="px-4 py-3">Telefono</th></tr></thead>
                        <tbody class="divide-y">@forelse ($clienti as $cliente)<tr><td class="px-4 py-3"><input type="checkbox" name="clienti[]" value="{{ $cliente->id }}" @checked(in_array($cliente->id, $clientiSelezionati)) class="rounded border-gray-300 text-blue-600"></td><td class="px-4 py-3 font-medium">{{ $cliente->nome }}</td><td class="px-4 py-3">{{ $cliente->cognome }}</td><td class="px-4 py-3">{{ $cliente->email ?: '-' }}</td><td class="px-4 py-3">{{ $cliente->telefono ?: '-' }}</td></tr>@empty<tr><td colspan="5" class="px-4 py-8 text-center text-gray-500">Nessun cliente trovato.</td></tr>@endforelse</tbody>
                    </table>
                </div>
                <div class="mt-4 flex flex-col gap-4 md:flex-row md:items-center md:justify-between"><div>{{ $clienti->links() }}</div><button class="rounded bg-blue-600 px-4 py-2 text-white">Conferma clienti selezionati</button></div>
            </form>
        </div>
    </div>
</x-app-layout>