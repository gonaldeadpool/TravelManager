<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Modifica viaggio</h2>
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

        <form method="POST" action="{{ route('viaggi.update', $viaggio) }}" enctype="multipart/form-data" class="max-w-6xl mx-auto">
            @csrf
            @method('PUT')
            @include('viaggi._form')

            <div class="mt-6 flex gap-3">
                <a href="{{ route('viaggi.index') }}" class="border border-gray-300 text-gray-700 px-4 py-2 rounded">Annulla</a>
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">Aggiorna viaggio</button>
            </div>
        </form>
    </div>
</x-app-layout>
