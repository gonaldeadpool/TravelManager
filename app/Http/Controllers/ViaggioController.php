<?php

namespace App\Http\Controllers;

use App\Models\Viaggio;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ViaggioController extends Controller
{
    public function index(): View
    {
        return view('viaggi', [
            'viaggi' => Viaggio::orderBy('data_partenza')->paginate(10),
        ]);
    }

    public function create(): View
    {
        return view('viaggi-create', [
            'viaggio' => new Viaggio(['trasporti' => [], 'sistemazioni' => []]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateViaggio($request);
        $validated['trasporti'] = $this->normalizzaOpzioni($request->input('trasporti', []));
        $validated['sistemazioni'] = $this->normalizzaOpzioni($request->input('sistemazioni', []));

        if ($request->hasFile('locandina')) {
            $validated['locandina'] = $request->file('locandina')->store('locandine', 'public');
        }

        Viaggio::create($validated);

        return redirect()->route('viaggi.index')->with('success', 'Viaggio creato correttamente.');
    }

    public function edit(Viaggio $viaggio): View
    {
        return view('viaggi-edit', compact('viaggio'));
    }

    public function update(Request $request, Viaggio $viaggio): RedirectResponse
    {
        $validated = $this->validateViaggio($request);
        $validated['trasporti'] = $this->normalizzaOpzioni($request->input('trasporti', []));
        $validated['sistemazioni'] = $this->normalizzaOpzioni($request->input('sistemazioni', []));

        if ($request->hasFile('locandina')) {
            if ($viaggio->locandina) {
                Storage::disk('public')->delete($viaggio->locandina);
            }

            $validated['locandina'] = $request->file('locandina')->store('locandine', 'public');
        }

        $viaggio->update($validated);

        return redirect()->route('viaggi.index')->with('success', 'Viaggio aggiornato correttamente.');
    }

    public function destroy(Viaggio $viaggio): RedirectResponse
    {
        if ($viaggio->locandina) {
            Storage::disk('public')->delete($viaggio->locandina);
        }

        $viaggio->delete();

        return redirect()->route('viaggi.index')->with('success', 'Viaggio eliminato correttamente.');
    }

    private function validateViaggio(Request $request): array
    {
        return $request->validate([
            'nome' => ['required', 'string', 'max:150'],
            'gestione' => ['required', 'in:tour_operator,interno'],
            'destinazione' => ['required', 'string', 'max:150'],
            'data_partenza' => ['required', 'date'],
            'data_rientro' => ['required', 'date', 'after_or_equal:data_partenza'],
            'locandina' => ['nullable', 'file', 'image', 'max:5120'],
            'trasporti' => ['nullable', 'array'],
            'trasporti.*.tipo' => ['required', 'in:bus,aereo,treno'],
            'trasporti.*.posti' => ['nullable', 'integer', 'min:1'],
            'sistemazioni' => ['nullable', 'array'],
            'sistemazioni.*.tipo' => ['required', 'in:camera,cabina'],
            'sistemazioni.*.formato' => ['required', 'in:singola,doppia,tripla,quadrupla'],
            'sistemazioni.*.quantita' => ['nullable', 'integer', 'min:1'],
        ]);
    }

    private function normalizzaOpzioni(array $opzioni): array
    {
        return collect($opzioni)
            ->filter(fn ($opzione) => filled($opzione['tipo'] ?? null))
            ->map(fn ($opzione) => collect($opzione)
                ->only(['tipo', 'posti', 'formato', 'quantita'])
                ->map(fn ($valore) => is_string($valore) ? trim($valore) : $valore)
                ->all())
            ->values()
            ->all();
    }
}
