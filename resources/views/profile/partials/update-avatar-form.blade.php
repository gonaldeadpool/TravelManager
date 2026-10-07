<section x-data="{ anteprima: null }">
    <header>
        <h2 class="text-lg font-medium text-gray-900">Immagine di profilo</h2>
        <p class="mt-1 text-sm text-gray-600">Carica una foto (JPG, PNG o WebP, massimo 2 MB): verrà mostrata in forma circolare.</p>
    </header>

    <div class="mt-6 flex items-center gap-5">
        <div class="h-24 w-24 shrink-0 overflow-hidden rounded-full border border-gray-200 bg-gray-100">
            <img x-show="anteprima" x-cloak :src="anteprima" alt="Anteprima immagine di profilo" class="h-full w-full object-cover">
            <div x-show="!anteprima" class="h-full w-full">
                <x-avatar :user="$user" size="h-24 w-24 text-2xl" />
            </div>
        </div>

        <form method="post" action="{{ route('profile.avatar.update') }}" enctype="multipart/form-data" class="min-w-0 flex-1 space-y-3">
            @csrf
            <input type="file" name="avatar" id="avatar" accept="image/png,image/jpeg,image/webp" required
                @change="const file = $event.target.files[0]; anteprima = file ? URL.createObjectURL(file) : null"
                class="block w-full text-sm text-gray-600 file:mr-3 file:rounded file:border-0 file:bg-gray-100 file:px-3 file:py-2 file:text-sm file:text-gray-700 hover:file:bg-gray-200">
            <x-input-error :messages="$errors->get('avatar')" />
            <div class="flex items-center gap-3">
                <x-primary-button>Salva immagine</x-primary-button>
                @if (session('status') === 'avatar-updated')
                    <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2000)" class="text-sm text-gray-600">Immagine aggiornata.</p>
                @elseif (session('status') === 'avatar-removed')
                    <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2000)" class="text-sm text-gray-600">Immagine rimossa.</p>
                @endif
            </div>
        </form>
    </div>

    @if ($user->avatar_path)
        <form method="post" action="{{ route('profile.avatar.destroy') }}" class="mt-3">
            @csrf
            @method('delete')
            <button type="submit" class="text-sm text-red-600 hover:underline">Rimuovi immagine</button>
        </form>
    @endif
</section>
