<x-app-layout>

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Clienti
        </h2>
    </x-slot>

    <div class="p-6">

        <a href="{{ route('clienti.create') }}"
           class="bg-green-500 text-white px-4 py-2 rounded">
            Nuovo Cliente
        </a>

        <h2 class="text-xl font-bold mt-6 mb-4">
            Elenco Clienti
        </h2>

        @if($clienti->count() === 0)

            <p>Nessun cliente presente.</p>

        @else

        <div class="overflow-x-auto mt-4">

            <table class="min-w-full border border-gray-300">

                <thead class="bg-gray-100">

                    <tr>
                        <th class="border px-4 py-2 text-left">ID</th>
                        <th class="border px-4 py-2 text-left">Nome</th>
                        <th class="border px-4 py-2 text-left">Cognome</th>
                        <th class="border px-4 py-2 text-left">Azioni</th>
                    </tr>

                </thead>

                <tbody>

                    @foreach($clienti as $cliente)

                        <tr>

                            <td class="border px-4 py-2">
                                {{ $cliente->id }}
                            </td>

                            <td class="border px-4 py-2">
                                {{ $cliente->nome }}
                            </td>

                            <td class="border px-4 py-2">
                                {{ $cliente->cognome }}
                            </td>

                            <td class="border px-4 py-2">
                                <a href="{{ route('clienti.edit', $cliente->id) }}"
                                class="text-blue-600 hover:underline">
                                    Modifica
                                </a>
                            </td>

                            <td class="border px-4 py-2">

                                <form method="POST" action="{{ route('clienti.destroy', $cliente->id) }}"
                                    class="inline">

                                    @csrf
                                    @method('DELETE')

                                    <button type="submit"
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

        </div>

        @endif

    </div>

</x-app-layout>