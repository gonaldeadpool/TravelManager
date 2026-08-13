<x-app-layout>
    <x-slot name="header"><h2 class="text-xl font-semibold leading-tight text-gray-800">Nuova pratica</h2></x-slot>
    <div class="p-6"><form method="POST" action="{{ route('pratiche.store') }}" class="mx-auto max-w-6xl">@csrf @include('pratiche._form')</form></div>
</x-app-layout>