<?php

namespace App\Http\Controllers;

use App\Models\Viaggio;
use App\Support\LocalStoragePaths;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ViaggioController extends Controller
{
    public function index(Request $request): View
    {
        $ricerca = $request->input('ricerca');
        $mostraPassati = $request->boolean('mostra_passati');

        $viaggi = $this->queryRicerca($ricerca, $mostraPassati)
            ->with('pratiche.clienti')
            ->orderBy('data_partenza')
            ->paginate(10)
            ->withQueryString();

        return view('viaggi', [
            'viaggi' => $viaggi,
            'ricerca' => $ricerca,
            'mostraPassati' => $mostraPassati,
        ]);
    }

    public function search(Request $request): View
    {
        $viaggi = $this->queryRicerca($request->input('q'), $request->boolean('mostra_passati'))
            ->with('pratiche.clienti')
            ->orderBy('data_partenza')
            ->paginate(10)
            ->withQueryString();

        return view('viaggi._table', compact('viaggi'));
    }

    public function create(Request $request): View
    {
        $data = $request->date('data_partenza');

        return view('viaggi-create', [
            'viaggio' => new Viaggio([
                'trasporti' => [],
                'sistemazioni' => [],
                'data_partenza' => $data,
                'data_rientro' => $data,
            ]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateViaggio($request);
        $validated['trasporti'] = $this->normalizzaOpzioni($request->input('trasporti', []));
        $validated['sistemazioni'] = $this->normalizzaSistemazioni($request);
        $validated['prezzi_cabine'] = $this->normalizzaPrezziCabine($request);

        if ($request->hasFile('locandina')) {
            LocalStoragePaths::ensureDirectories();
            $file = $request->file('locandina');
            $nome = \Illuminate\Support\Str::uuid()->toString() . '.' . $file->getClientOriginalExtension();
            LocalStoragePaths::disk(LocalStoragePaths::locandine())->putFileAs('', $file, $nome);
            $validated['locandina'] = $nome;
        }

        Viaggio::create($validated);

        return redirect()->route('viaggi.index')->with('success', 'Viaggio creato correttamente.');
    }

    public function edit(Viaggio $viaggio): View
    {
        return view('viaggi-edit', compact('viaggio'));
    }

    public function show(Viaggio $viaggio): View
    {
        $viaggio->load(['pratiche.clienti']);

        return view('viaggi-show', [
            'viaggio' => $viaggio,
            'numeroPartecipanti' => $viaggio->pratiche->flatMap->clienti->unique('id')->count(),
            'importoAcconto' => $viaggio->pratiche->sum('acconto'),
            'importoSaldo' => $viaggio->pratiche->sum('saldo'),
        ]);
    }

    public function update(Request $request, Viaggio $viaggio): RedirectResponse
    {
        $validated = $this->validateViaggio($request);
        $validated['trasporti'] = $this->normalizzaOpzioni($request->input('trasporti', []));
        $validated['sistemazioni'] = $this->normalizzaSistemazioni($request);
        $validated['prezzi_cabine'] = $this->normalizzaPrezziCabine($request);

        if ($request->hasFile('locandina')) {
            if ($viaggio->locandina) {
                $this->eliminaLocandina($viaggio->locandina);
            }

            LocalStoragePaths::ensureDirectories();
            $file = $request->file('locandina');
            $nome = \Illuminate\Support\Str::uuid()->toString() . '.' . $file->getClientOriginalExtension();
            LocalStoragePaths::disk(LocalStoragePaths::locandine())->putFileAs('', $file, $nome);
            $validated['locandina'] = $nome;
        }

        $viaggio->update($validated);

        return redirect()->route('viaggi.index')->with('success', 'Viaggio aggiornato correttamente.');
    }

    public function destroy(Viaggio $viaggio): RedirectResponse
    {
        if ($viaggio->locandina) {
            $this->eliminaLocandina($viaggio->locandina);
        }

        $viaggio->delete();

        return redirect()->route('viaggi.index')->with('success', 'Viaggio eliminato correttamente.');
    }

    public function downloadLocandina(Viaggio $viaggio)
    {
        $disk = LocalStoragePaths::disk(LocalStoragePaths::locandine());
        $percorso = $viaggio->locandina;

        if (! $disk->exists($percorso)) {
            $disk = Storage::disk('public');
        }

        abort_unless($disk->exists($percorso), 404);

        return $disk->response($percorso);
    }

    private function validateViaggio(Request $request): array
    {
        return $request->validate([
            'nome' => ['required', 'string', 'max:150'],
            'tipologia' => ['required', 'in:viaggio,tour,crociera'],
            'destinazione' => ['required', 'string', 'max:150'],
            'data_partenza' => ['required', 'date'],
            'data_rientro' => ['required', 'date', 'after_or_equal:data_partenza'],
            'prezzo' => ['nullable', 'required_unless:tipologia,crociera', 'numeric', 'min:0'],
            'minimo_partecipanti' => ['required', 'integer', 'min:1'],
            'massimo_partecipanti' => ['nullable', 'integer', 'gte:minimo_partecipanti'],
            'data_acconto' => ['nullable', 'date'],
            'importo_minimo_acconto' => ['nullable', 'numeric', 'min:0'],
            'data_saldo' => ['nullable', 'date', 'after_or_equal:data_acconto'],
            'prezzi_cabine' => ['nullable', 'array'],
            'prezzi_cabine.*.tipo' => ['required', 'in:interna,vista_mare,balcone'],
            'prezzi_cabine.*.prezzo' => ['required_if:tipologia,crociera', 'nullable', 'numeric', 'min:0'],
            'eta_gratuita' => ['nullable', 'integer', 'min:0', 'max:17'],
            'note' => ['nullable', 'string'],
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

    private function queryRicerca(?string $ricerca, bool $mostraPassati = false)
    {
        return Viaggio::query()
            ->when(! $mostraPassati, fn ($query) => $query->whereDate('data_partenza', '>=', today()))
            ->when($ricerca, function ($query, $ricerca) {
            $query->where(function ($query) use ($ricerca) {
                $query->where('nome', 'ilike', "%{$ricerca}%")
                    ->orWhere('destinazione', 'ilike', "%{$ricerca}%")
                    ->orWhere('tipologia', 'ilike', "%{$ricerca}%");
            });
        });
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

    private function normalizzaSistemazioni(Request $request): array
    {
        $sistemazioni = $request->input('sistemazioni', []);

        if ($request->input('tipologia') !== 'crociera') {
            $sistemazioni = collect($sistemazioni)
                ->reject(fn ($sistemazione) => ($sistemazione['tipo'] ?? null) === 'cabina')
                ->all();
        }

        return $this->normalizzaOpzioni($sistemazioni);
    }

    private function normalizzaPrezziCabine(Request $request): array
    {
        if ($request->input('tipologia') !== 'crociera') {
            return [];
        }

        return collect($request->input('prezzi_cabine', []))
            ->filter(fn ($cabina) => filled($cabina['tipo'] ?? null))
            ->map(fn ($cabina) => [
                'tipo' => $cabina['tipo'],
                'prezzo' => $cabina['prezzo'] ?? null,
            ])
            ->values()
            ->all();
    }

    private function eliminaLocandina(string $percorso): void
    {
        LocalStoragePaths::disk(LocalStoragePaths::locandine())->delete($percorso);
        Storage::disk('public')->delete($percorso);
    }
}
