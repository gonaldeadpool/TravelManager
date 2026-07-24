<x-app-layout>

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Nuovo Cliente
        </h2>
    </x-slot>

    <div class="p-6">

        @if ($errors->any())

            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">

                <ul>

                    @foreach ($errors->all() as $error)

                        <li>{{ $error }}</li>

                    @endforeach

                </ul>

            </div>

        @endif

        <form method="POST" action="{{ route('clienti.store') }}">

            @csrf

            <div class="mb-4">
                <label class="block mb-1">Nome</label>
                <input type="text"
                       name="nome"
                       class="border rounded w-full p-2">
            </div>

            <div class="mb-4">
                <label class="block mb-1">Cognome</label>
                <input type="text"
                       name="cognome"
                       class="border rounded w-full p-2">
            </div>

            <button type="submit"
                    class="bg-blue-500 text-white px-4 py-2 rounded">
                Salva Cliente
            </button>

        </form>

    </div>

</x-app-layout>
