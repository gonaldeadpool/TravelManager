<x-app-layout>

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Clienti
        </h2>
    </x-slot>

    <div class="p-6">

        @if($clienti->count() === 0)

            <p>Nessun cliente presente.</p>

        @else

        <div class="overflow-x-auto mt-4">

            <form method="GET" class="mb-4">

                <div class="flex gap-2">

                    <input type="text"
                        id="ricerca"
                        name="ricerca"
                        value="{{ $ricerca }}"
                        placeholder="Cerca cliente"
                        class="border rounded px-3 py-2 w-80">

                    <a href="{{ route('clienti.create') }}"
                        class="bg-green-500 text-white px-4 py-2 rounded">
                        Nuovo Cliente
                    </a>     

                </div>

            </form>

            <div id="clienti-table-container">

                @include('clienti._table')

            </div>

        </div>

        @endif

    </div>

</x-app-layout>

<script>
document.addEventListener('DOMContentLoaded', function () {

    let timer;

    const ricerca = document.getElementById('ricerca');

    ricerca.addEventListener('input', function () {

        clearTimeout(timer);

        timer = setTimeout(async () => {

            const response = await fetch(
                `/clienti/search?q=${encodeURIComponent(ricerca.value)}`
            );

            const html = await response.text();

            document.getElementById('clienti-table-container')
                .innerHTML = html;

        }, 400);

    });

});
</script>
