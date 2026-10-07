<form method="POST" action="{{ route('pratiche.creazione.clienti.store') }}">
    @csrf
    <div class="overflow-x-auto rounded bg-white shadow">
        <table class="w-full text-left">
            <thead class="border-b bg-gray-50 text-sm text-gray-600">
                <tr>
                    <th class="w-12 px-4 py-3"><span class="sr-only">Seleziona</span></th>
                    <th class="px-4 py-3">Nome</th>
                    <th class="px-4 py-3">Cognome</th>
                    <th class="px-4 py-3">Email</th>
                    <th class="px-4 py-3">Telefono</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse ($clienti as $cliente)
                    <tr>
                        <td class="px-4 py-3"><input type="checkbox" name="clienti[]" value="{{ $cliente->id }}" @checked(in_array($cliente->id, $clientiSelezionati)) class="rounded border-gray-300 text-blue-600"></td>
                        <td class="px-4 py-3 font-medium">{{ $cliente->nome }}</td>
                        <td class="px-4 py-3">{{ $cliente->cognome }}</td>
                        <td class="px-4 py-3"><x-contact-email :cliente="$cliente" :defer="true" /></td>
                        <td class="px-4 py-3"><x-contact-phone :number="$cliente->telefono" /></td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-8 text-center text-gray-500">Nessun cliente trovato.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>{{ $clienti->links() }}</div>
        <button class="rounded bg-blue-600 px-4 py-2 text-white">Conferma clienti selezionati</button>
    </div>
</form>
