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

            <ul>

                @foreach($clienti as $cliente)

                    <li>
                        {{ $cliente->nome }}
                        {{ $cliente->cognome }}
                    </li>

                @endforeach

            </ul>

        @endif

    </div>

</x-app-layout>