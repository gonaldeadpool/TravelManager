<x-app-layout>
    <x-slot name="header"><h2 class="text-xl font-semibold leading-tight text-gray-800">Nuovo utente</h2></x-slot>
    <div class="p-6"><div class="mx-auto max-w-3xl rounded bg-white p-6 shadow"><form method="POST" action="{{ route('utenti.store') }}" class="space-y-5">@csrf @include('utenti._form', ['submitLabel' => 'Crea utente'])</form></div></div>
</x-app-layout>
