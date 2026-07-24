<table class="min-w-full border border-gray-300">

    <thead class="bg-gray-100">

        <tr>
            <th class="border px-4 py-2 text-left">Nome</th>
            <th class="border px-4 py-2 text-left">Cognome</th>
            <th class="border px-4 py-2 text-left">Email</th>
            <th class="border px-4 py-2 text-left">Telefono</th>
            <th class="border px-4 py-2 text-left">Azioni</th>
        </tr>

    </thead>

    <tbody id="clienti-table-body">

        @foreach($clienti as $cliente)

        <tr>

            <td class="border px-4 py-2">
                {{ $cliente->nome }}
            </td>

            <td class="border px-4 py-2">
                {{ $cliente->cognome }}
            </td>
            <td class="border px-4 py-2">
                {{ $cliente->email }}
            </td>
            <td class="border px-4 py-2">
                {{ $cliente->telefono }}
            </td>


            <td class="border px-4 py-2">

                <a href="{{ route('clienti.edit', $cliente->id) }}"
                class="text-blue-600 hover:underline mr-3">
                    Modifica
                </a>

                <form action="{{ route('clienti.destroy', $cliente->id) }}"
                    method="POST"
                    class="inline">

                    @csrf
                    @method('DELETE')

                    <button
                        type="submit"
                        class="text-red-600 hover:underline"
                        onclick="return confirm('Sei sicuro di voler eliminare questo cliente?')">
                        Elimina
                    </button>

                </form>

            </td>

        </tr>

        @endforeach

    </tbody>
</table>