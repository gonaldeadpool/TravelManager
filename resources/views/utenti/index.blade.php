<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Utenti</h2>
            <a href="{{ route('utenti.create') }}" class="rounded bg-blue-600 px-4 py-2 text-white">Nuovo utente</a>
        </div>
    </x-slot>

    <div class="p-6">
        @if (session('success'))
            <div class="mx-auto mb-4 max-w-5xl rounded border border-green-400 bg-green-100 px-4 py-3 text-green-700">{{ session('success') }}</div>
        @endif
        <div class="mx-auto max-w-5xl overflow-x-auto rounded bg-white shadow">
            <table class="w-full text-left">
                <thead class="border-b bg-gray-50 text-sm text-gray-600"><tr><th class="px-4 py-3">Nome</th><th class="px-4 py-3">Email</th><th class="px-4 py-3">Ruolo</th><th class="px-4 py-3 text-right">Azioni</th></tr></thead>
                <tbody class="divide-y">
                    @foreach ($users as $utente)
                        <tr>
                            <td class="px-4 py-3 font-medium">{{ $utente->name }}</td>
                            <td class="px-4 py-3">{{ $utente->email }}</td>
                            <td class="px-4 py-3">{{ ucfirst($utente->role) }}</td>
                            <td class="px-4 py-3"><div class="flex justify-end gap-3"><a href="{{ route('utenti.edit', $utente) }}" class="text-blue-600 hover:underline">Modifica</a>@if (!$utente->is(auth()->user()))<form method="POST" action="{{ route('utenti.destroy', $utente) }}" onsubmit="return confirm('Eliminare questo utente?')">@csrf @method('DELETE')<button class="text-red-600 hover:underline">Elimina</button></form>@endif</div></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
