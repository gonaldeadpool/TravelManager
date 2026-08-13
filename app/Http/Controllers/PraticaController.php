<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\AppSetting;
use App\Models\Pratica;
use App\Models\PraticaDocumento;
use App\Models\Viaggio;
use App\Support\LocalStoragePaths;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PraticaController extends Controller
{
    public function index(Request $request): View
    {
        $viaggioId = $request->integer('viaggio_id');
        $viaggio = $viaggioId ? Viaggio::findOrFail($viaggioId) : null;
        $ricerca = $request->input('ricerca');
        $mostraPassati = $request->boolean('mostra_passati');
        $pagamento = $request->input('pagamento');
        $pratiche = $this->queryElenco($ricerca, $mostraPassati, $viaggioId, $pagamento);

        return view('pratiche.index', [
            'pratiche' => $pratiche->paginate(10)->withQueryString(),
            'viaggioFiltrato' => $viaggio,
            'ricerca' => $ricerca,
            'mostraPassati' => $mostraPassati,
            'pagamento' => $pagamento,
        ]);
    }

    public function search(Request $request): View
    {
        $viaggioId = $request->integer('viaggio_id');
        $pratiche = $this->queryElenco(
            $request->input('q'),
            $request->boolean('mostra_passati'),
            $viaggioId,
            $request->input('pagamento')
        );

        return view('pratiche._table', [
            'pratiche' => $pratiche->paginate(10)->withQueryString(),
        ]);
    }

    public function create(Request $request): View
    {
        if (! $request->boolean('bozza')) {
            session()->forget('pratica_creazione');
        }

        $bozza = session('pratica_creazione', []);
        $clienti = Cliente::whereKey($bozza['clienti'] ?? [])
            ->orderBy('cognome')
            ->orderBy('nome')
            ->get();
        $pratica = new Pratica();
        $pratica->setRelation('clienti', $clienti);

        return view('pratiche.create', $this->formData($pratica) + ['bozza' => $bozza]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validatePratica($request, true);
        $pratica = Pratica::create($this->praticaData($validated));
        $pratica->clienti()->sync($this->clientiConGratuita($validated));
        session()->forget('pratica_creazione');

        return redirect()->route('pratiche.index')->with('success', 'Pratica creata correttamente.');
    }

    public function edit(Pratica $pratica): View
    {
        $pratica->load(['clienti', 'documenti']);

        return view('pratiche.edit', $this->formData($pratica));
    }

    public function update(Request $request, Pratica $pratica): RedirectResponse
    {
        $validated = $this->validatePratica($request);
        $pratica->update($this->praticaData($validated));
        $clienti = $pratica->clienti()->pluck('clienti.id')->all();
        $pratica->clienti()->syncWithoutDetaching($this->clientiConGratuita([
            'clienti' => $clienti,
            'gratuiti' => $validated['gratuiti'] ?? [],
        ]));

        return redirect()->route('pratiche.index')->with('success', 'Pratica aggiornata correttamente.');
    }

    public function selectClienti(Request $request, Pratica $pratica): View
    {
        $ricerca = $request->input('ricerca');
        $clienti = Cliente::query()->with('documenti')
            ->where(function ($query) use ($pratica) {
                $query->whereDoesntHave('pratiche', fn ($pratiche) => $pratiche->where('viaggio_id', $pratica->viaggio_id))
                    ->orWhereIn('id', $pratica->clienti()->pluck('clienti.id'));
            });

        if ($ricerca) {
            $clienti->where(function ($query) use ($ricerca) {
                $query->where('nome', 'like', "%{$ricerca}%")
                    ->orWhere('cognome', 'like', "%{$ricerca}%")
                    ->orWhere('email', 'like', "%{$ricerca}%");
            });
        }

        return view('pratiche.clienti', [
            'pratica' => $pratica->load('clienti'),
            'clienti' => $clienti->orderBy('cognome')->orderBy('nome')->paginate(10)->withQueryString(),
            'ricerca' => $ricerca,
        ]);
    }

    public function selectClientiCreazione(Request $request): View
    {
        $ricerca = $request->input('ricerca');
        $bozza = session('pratica_creazione', []);
        $viaggioId = $bozza['viaggio_id'] ?? null;
        $clientiSelezionati = $bozza['clienti'] ?? [];
        $clienti = Cliente::query();

        if ($viaggioId) {
            $clienti->where(function ($query) use ($viaggioId, $clientiSelezionati) {
                $query->whereDoesntHave('pratiche', fn ($pratiche) => $pratiche->where('viaggio_id', $viaggioId))
                    ->orWhereIn('id', $clientiSelezionati);
            });
        }

        if ($ricerca) {
            $clienti->where(function ($query) use ($ricerca) {
                $query->where('nome', 'like', "%{$ricerca}%")
                    ->orWhere('cognome', 'like', "%{$ricerca}%")
                    ->orWhere('email', 'like', "%{$ricerca}%");
            });
        }

        return view('pratiche.clienti-creazione', [
            'clienti' => $clienti->orderBy('cognome')->orderBy('nome')->paginate(10)->withQueryString(),
            'ricerca' => $ricerca,
            'clientiSelezionati' => $clientiSelezionati,
            'viaggioSelezionato' => $viaggioId ? Viaggio::find($viaggioId) : null,
        ]);
    }

    public function storeBozzaCreazione(Request $request): RedirectResponse
    {
        session(['pratica_creazione' => $request->only([
            'viaggio_id', 'totale', 'acconto', 'data_acconto', 'saldo', 'data_saldo', 'note', 'clienti', 'gratuiti',
        ])]);

        return redirect()->route('pratiche.creazione.clienti.select');
    }

    public function storeClientiCreazione(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'clienti' => ['required', 'array', 'min:1'],
            'clienti.*' => ['integer', 'distinct', 'exists:clienti,id'],
        ]);

        $bozza = session('pratica_creazione', []);
        $bozza['clienti'] = $validated['clienti'];
        session(['pratica_creazione' => $bozza]);

        return redirect()->route('pratiche.create', ['bozza' => 1]);
    }

    public function storeClienti(Request $request, Pratica $pratica): RedirectResponse
    {
        $validated = $request->validate([
            'clienti' => ['required', 'array', 'min:1'],
            'clienti.*' => ['integer', 'distinct', 'exists:clienti,id'],
        ]);

        $pratica->clienti()->syncWithoutDetaching($validated['clienti']);
        $this->ricalcolaTotale($pratica);

        return redirect()->route('pratiche.edit', $pratica)->with('success', 'Clienti aggiunti alla pratica.');
    }

    public function destroyCliente(Pratica $pratica, Cliente $cliente): RedirectResponse
    {
        $pratica->clienti()->detach($cliente);
        $this->ricalcolaTotale($pratica);

        return redirect()->route('pratiche.edit', $pratica)->with('success', 'Cliente rimosso dalla pratica.');
    }

    public function destroy(Pratica $pratica): RedirectResponse
    {
        foreach ($pratica->documenti as $documento) {
            LocalStoragePaths::disk(LocalStoragePaths::documentiPratiche())->delete($documento->percorso);
        }

        $pratica->delete();

        return redirect()->route('pratiche.index')->with('success', 'Pratica eliminata correttamente.');
    }

    public function storeDocument(Request $request, Pratica $pratica): RedirectResponse
    {
        $request->validate([
            'documento_file' => ['required', 'file', 'max:10240'],
        ]);

        LocalStoragePaths::ensureDirectories();
        $file = $request->file('documento_file');
        $nome = Str::uuid()->toString() . ($file->getClientOriginalExtension() ? '.' . $file->getClientOriginalExtension() : '');
        LocalStoragePaths::disk(LocalStoragePaths::documentiPratiche())->putFileAs('', $file, $nome);

        $pratica->documenti()->create([
            'nome_originale' => $file->getClientOriginalName(),
            'percorso' => $nome,
            'mime_type' => $file->getMimeType(),
            'dimensione' => $file->getSize(),
        ]);

        return redirect()->route('pratiche.edit', $pratica)->with('success', 'Documento allegato correttamente.');
    }

    public function downloadDocument(Pratica $pratica, PraticaDocumento $documento)
    {
        abort_unless($documento->pratica_id === $pratica->id, 404);

        $disk = LocalStoragePaths::disk(LocalStoragePaths::documentiPratiche());
        abort_unless($disk->exists($documento->percorso), 404);

        return $disk->response($documento->percorso, $documento->nome_originale);
    }

    public function destroyDocument(Pratica $pratica, PraticaDocumento $documento): RedirectResponse
    {
        abort_unless($documento->pratica_id === $pratica->id, 404);

        LocalStoragePaths::disk(LocalStoragePaths::documentiPratiche())->delete($documento->percorso);
        $documento->delete();

        return redirect()->route('pratiche.edit', $pratica)->with('success', 'Documento eliminato correttamente.');
    }

    private function formData(Pratica $pratica): array
    {
        return [
            'pratica' => $pratica,
            'viaggi' => Viaggio::orderBy('nome')->get(),
        ];
    }

    private function queryElenco(?string $ricerca, bool $mostraPassati, ?int $viaggioId, ?string $pagamento = null)
    {
        $query = Pratica::with(['viaggio', 'clienti'])
            ->when($viaggioId, fn ($query) => $query->where('viaggio_id', $viaggioId))
            ->when(! $viaggioId && ! $mostraPassati, fn ($query) => $query->whereHas('viaggio', fn ($viaggi) => $viaggi->whereDate('data_partenza', '>=', today())))
            ->when($ricerca, function ($query, $ricerca) {
                $query->where(function ($query) use ($ricerca) {
                    $query->whereHas('viaggio', function ($viaggi) use ($ricerca) {
                        $viaggi->where('nome', 'ilike', "%{$ricerca}%")
                            ->orWhere('destinazione', 'ilike', "%{$ricerca}%")
                            ->orWhere('tipologia', 'ilike', "%{$ricerca}%");
                    })->orWhereHas('clienti', function ($clienti) use ($ricerca) {
                        $clienti->where('nome', 'ilike', "%{$ricerca}%")
                            ->orWhere('cognome', 'ilike', "%{$ricerca}%");
                    });
                });
            })
            ;

        if (in_array($pagamento, ['acconto_non_versato', 'acconto_non_versato_scadenza', 'acconto_versato', 'saldo_non_versato_scadenza', 'saldo_versato'], true)) {
            $soglie = [
                'acconto' => (int) (AppSetting::where('key', 'pratiche.scadenza.acconto')->value('value') ?? 30),
                'saldo' => (int) (AppSetting::where('key', 'pratiche.scadenza.saldo')->value('value') ?? 30),
            ];
            $oggi = today();
            $ids = (clone $query)->get()->filter(fn (Pratica $pratica) => $this->statoPagamento($pratica, $oggi, $soglie) === $pagamento)->modelKeys();
            $query->whereKey($ids);
        }

        return $query->latest();
    }

    private function statoPagamento(Pratica $pratica, $oggi, array $soglie): string
    {
        $totale = (float) $pratica->totale;
        $acconto = (float) $pratica->acconto;
        $saldo = (float) $pratica->saldo;

        if ($saldo > 0 && $totale - $acconto - $saldo <= 0) {
            return 'saldo_versato';
        }

        if ($acconto <= 0) {
            $dataAcconto = $pratica->viaggio->data_acconto ?? $pratica->viaggio->data_partenza;

            return $oggi->diffInDays($dataAcconto, false) > $soglie['acconto']
                ? 'acconto_non_versato'
                : 'acconto_non_versato_scadenza';
        }

        $dataSaldo = $pratica->viaggio->data_saldo ?? $pratica->viaggio->data_partenza;

        return $oggi->diffInDays($dataSaldo, false) > $soglie['saldo']
            ? 'acconto_versato'
            : 'saldo_non_versato_scadenza';
    }

    private function validatePratica(Request $request, bool $richiedeClienti = false): array
    {
        $rules = [
            'viaggio_id' => ['required', 'exists:viaggi,id'],
            'totale' => ['required', 'numeric', 'min:0'],
            'acconto' => ['nullable', 'numeric', 'min:0'],
            'data_acconto' => ['nullable', 'date'],
            'saldo' => ['nullable', 'numeric', 'min:0'],
            'data_saldo' => ['nullable', 'date'],
            'note' => ['nullable', 'string'],
            'gratuiti' => ['nullable', 'array'],
            'gratuiti.*' => ['integer', 'distinct', 'exists:clienti,id'],
        ];

        if ($richiedeClienti) {
            $rules['clienti'] = ['required', 'array', 'min:1'];
            $rules['clienti.*'] = ['integer', 'distinct', 'exists:clienti,id'];
        }

        return $request->validate($rules);
    }

    private function clientiConGratuita(array $validated): array
    {
        $gratuiti = collect($validated['gratuiti'] ?? [])->map(fn ($id) => (int) $id)->all();

        return collect($validated['clienti'])
            ->mapWithKeys(fn ($id) => [(int) $id => ['gratuito' => in_array((int) $id, $gratuiti, true)]])
            ->all();
    }

    private function ricalcolaTotale(Pratica $pratica): void
    {
        $pratica->loadMissing(['viaggio', 'clienti']);

        if ($pratica->viaggio->prezzo === null) {
            return;
        }

        $partecipantiPaganti = $pratica->clienti->where('pivot.gratuito', false)->count();
        $pratica->update(['totale' => $pratica->viaggio->prezzo * $partecipantiPaganti]);
    }

    private function praticaData(array $validated): array
    {
        return [
            'viaggio_id' => $validated['viaggio_id'],
            'totale' => $validated['totale'],
            'acconto' => $validated['acconto'] ?? 0,
            'data_acconto' => $validated['data_acconto'] ?? null,
            'saldo' => $validated['saldo'] ?? 0,
            'data_saldo' => $validated['data_saldo'] ?? null,
            'note' => $validated['note'] ?? null,
        ];
    }
}