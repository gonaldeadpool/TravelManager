<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">Calendario viaggi</h2>
    </x-slot>

    <div class="p-6">
        <div class="mx-auto max-w-7xl rounded bg-white p-4 shadow sm:p-6">
            <div id="calendario-viaggi" data-eventi-url="{{ route('calendario.eventi') }}" data-nuovo-viaggio-url="{{ route('viaggi.create') }}"></div>
        </div>
    </div>
</x-app-layout>