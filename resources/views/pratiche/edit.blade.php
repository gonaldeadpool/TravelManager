<x-app-layout>
    <x-slot name="header"><h2 class="text-xl font-semibold leading-tight text-gray-800">Pratica #{{ $pratica->id }}</h2></x-slot>
    <div class="p-6" x-data="{ tab: 'dati' }">
        <div class="mx-auto mb-6 max-w-6xl border-b border-gray-200">
            <nav class="flex gap-6" aria-label="Sezioni pratica">
                <button type="button" @click="tab = 'dati'" :class="tab === 'dati' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700'" class="border-b-2 px-1 pb-3 text-sm font-semibold">Dati pratica</button>
                <button type="button" @click="tab = 'documenti'" :class="tab === 'documenti' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700'" class="border-b-2 px-1 pb-3 text-sm font-semibold">Documenti</button>
            </nav>
        </div>
        <form method="POST" action="{{ route('pratiche.update', $pratica) }}" x-show="tab === 'dati'" class="mx-auto max-w-6xl">@csrf @method('PUT') @include('pratiche._form')</form>
        @foreach ($pratica->clienti as $cliente)
            <form id="rimuovi-cliente-{{ $cliente->id }}" method="POST" action="{{ route('pratiche.clienti.destroy', [$pratica, $cliente]) }}">
                @csrf
                @method('DELETE')
            </form>
        @endforeach
        <div x-show="tab === 'documenti'" x-cloak>@include('pratiche._documents')</div>
    </div>
</x-app-layout>