@if ($errors->any())
    <div class="rounded border border-red-400 bg-red-100 px-4 py-3 text-red-700"><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
@endif
<div><label for="name" class="mb-1 block">Nome</label><input id="name" name="name" value="{{ old('name', $utente->name ?? '') }}" required class="w-full rounded border p-2"></div>
<div><label for="email" class="mb-1 block">Email</label><input id="email" type="email" name="email" value="{{ old('email', $utente->email ?? '') }}" required class="w-full rounded border p-2"></div>
<div><label for="password" class="mb-1 block">Password @isset($utente)<span class="text-sm font-normal text-gray-500">(lascia vuoto per non modificarla)</span>@endisset</label><input id="password" type="password" name="password" {{ isset($utente) ? '' : 'required' }} class="w-full rounded border p-2"></div>
<div><label for="password_confirmation" class="mb-1 block">Conferma password</label><input id="password_confirmation" type="password" name="password_confirmation" {{ isset($utente) ? '' : 'required' }} class="w-full rounded border p-2"></div>
<div><label for="role" class="mb-1 block">Ruolo</label><select id="role" name="role" class="w-full rounded border p-2" x-data x-on:change="$dispatch('role-changed', { role: $event.target.value })"><option value="admin" @selected(old('role', $utente->role ?? '') === 'admin')>Admin</option><option value="operatore" @selected(old('role', $utente->role ?? '') === 'operatore')>Operatore</option></select></div>
<div x-data="{ role: @js(old('role', $utente->role ?? 'admin')) }" @role-changed.window="role = $event.detail.role" x-show="role === 'operatore'" x-cloak>
    <p class="mb-2 font-semibold">Voci di menu accessibili</p>
    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">@foreach ($menuOptions as $key => $label)<label class="flex items-center gap-2"><input type="checkbox" name="menu_permissions[]" value="{{ $key }}" @checked(in_array($key, old('menu_permissions', $utente->menu_permissions ?? []), true)) class="rounded border-gray-300 text-blue-600">{{ $label }}</label>@endforeach</div>
</div>
<div class="flex items-center justify-between"><a href="{{ route('utenti.index') }}" class="text-gray-600 hover:underline">Annulla</a><button class="rounded bg-blue-600 px-4 py-2 text-white">{{ $submitLabel }}</button></div>
