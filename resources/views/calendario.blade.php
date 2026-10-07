<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">Calendario viaggi</h2>
    </x-slot>

    <div class="p-2 sm:p-6">
        <div class="mx-auto max-w-7xl overflow-hidden rounded bg-white p-2 shadow sm:p-6">
            <div id="calendario-viaggi" data-eventi-url="{{ route('calendario.eventi') }}" data-nuovo-viaggio-url="{{ route('viaggi.create') }}"></div>
        </div>
    </div>
</x-app-layout>