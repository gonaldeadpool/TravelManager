<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
<div class="p-6 text-gray-900">

    <h3 class="text-2xl font-bold mb-6">
        Travel Manager
    </h3>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

        <div class="bg-blue-100 p-4 rounded">
            <h4 class="font-bold">Clienti</h4>
            <p>0</p>
        </div>

        <div class="bg-green-100 p-4 rounded">
            <h4 class="font-bold">Viaggi</h4>
            <p>0</p>
        </div>

        <div class="bg-yellow-100 p-4 rounded">
            <h4 class="font-bold">Pratiche</h4>
            <p>0</p>
        </div>

    </div>

</div>
            </div>
        </div>
    </div>
</x-app-layout>
