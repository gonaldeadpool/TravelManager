<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rules;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserManagementController extends Controller
{
    private const MENUS = [
        'dashboard' => 'Dashboard',
        'clienti' => 'Clienti',
        'viaggi' => 'Viaggi',
        'calendario' => 'Calendario',
        'pratiche' => 'Pratiche',
        'amministrazione' => 'Amministrazione',
    ];

    public function index(): View
    {
        return view('utenti.index', ['users' => User::orderBy('name')->get()]);
    }

    public function create(): View
    {
        return view('utenti.create', ['menuOptions' => self::MENUS]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateUser($request, true);
        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'role' => $validated['role'],
            'menu_permissions' => $validated['role'] === 'admin' ? array_keys(self::MENUS) : Arr::wrap($validated['menu_permissions'] ?? []),
        ]);

        return redirect()->route('utenti.index')->with('success', 'Utente creato correttamente.');
    }

    public function edit(User $utente): View
    {
        return view('utenti.edit', ['utente' => $utente, 'menuOptions' => self::MENUS]);
    }

    public function update(Request $request, User $utente): RedirectResponse
    {
        $validated = $this->validateUser($request);
        $data = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'menu_permissions' => $validated['role'] === 'admin' ? array_keys(self::MENUS) : Arr::wrap($validated['menu_permissions'] ?? []),
        ];

        if (filled($validated['password'] ?? null)) {
            $data['password'] = $validated['password'];
        }

        $utente->update($data);

        return redirect()->route('utenti.index')->with('success', 'Utente aggiornato correttamente.');
    }

    public function destroy(User $utente): RedirectResponse
    {
        abort_if($utente->is(auth()->user()), 422, 'Non puoi eliminare il tuo stesso utente.');
        $utente->delete();

        return redirect()->route('utenti.index')->with('success', 'Utente eliminato correttamente.');
    }

    private function validateUser(Request $request, bool $passwordRequired = false): array
    {
        $utente = $request->route('utente');

        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->when($utente instanceof User, fn ($rule) => $rule->ignore($utente)),
            ],
            'password' => [$passwordRequired ? 'required' : 'nullable', 'confirmed', Rules\Password::defaults()],
            'role' => ['required', 'in:admin,operatore'],
            'menu_permissions' => ['nullable', 'array'],
            'menu_permissions.*' => ['string', 'in:' . implode(',', array_keys(self::MENUS))],
        ]);
    }
}
