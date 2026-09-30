<?php

namespace App\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Cliente;
use App\Models\AppSetting;
use App\Models\Pratica;
use App\Models\PraticaDocumento;
use App\Models\Viaggio;
use App\Support\LocalStoragePaths;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
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
        $sort = $request->input('sort');
        $ordinamenti = $this->parseSort($sort);
        $pratiche = $this->queryElenco($ricerca, $mostraPassati, $viaggioId, $pagamento, $ordinamenti);

        return view('pratiche.index', [
            'pratiche' => $pratiche->paginate(5)->withQueryString(),
            'viaggioFiltrato' => $viaggio,
            'ricerca' => $ricerca,
            'mostraPassati' => $mostraPassati,
            'pagamento' => $pagamento,
            'ordinamenti' => $ordinamenti,
        ]);
    }

    public function search(Request $request): View
    {
        $viaggioId = $request->integer('viaggio_id');
        $ricerca = $request->input('q');
        $mostraPassati = $request->boolean('mostra_passati');
        $pagamento = $request->input('pagamento');
        $sort = $request->input('sort');
        $ordinamenti = $this->parseSort($sort);
        $pratiche = $this->queryElenco(
            $ricerca,
            $mostraPassati,
            $viaggioId,
            $pagamento,
            $ordinamenti
        );

        return view('pratiche._table', [
            'pratiche' => $pratiche->paginate(5)
                ->withPath(route('pratiche.index'))
                ->appends(array_filter([
                    'ricerca' => $ricerca,
                    'mostra_passati' => $mostraPassati ? 1 : null,
                    'viaggio_id' => $viaggioId ?: null,
                    'pagamento' => $pagamento,
                    'sort' => empty($ordinamenti) ? null : $this->sortToString($ordinamenti),
                ])),
            'ordinamenti' => $ordinamenti,
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

        return view('pratiche.create', $this->formData($pratica, true) + ['bozza' => $bozza]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validatePratica($request, true);
        $pratica = Pratica::create($this->praticaData($validated));
        $pratica->clienti()->sync($this->clientiConTariffe($validated));
        $this->ricalcolaTotale($pratica);
        session()->forget('pratica_creazione');

        return redirect()->route('pratiche.index')->with('success', 'Pratica creata correttamente.');
    }

    public function edit(Pratica $pratica): View
    {
        $pratica->load(['clienti', 'documenti']);

        return view('pratiche.edit', $this->formData($pratica));
    }

    public function show(Pratica $pratica): View
    {
        $this->preparaRiepilogo($pratica);

        return view('pratiche.show', compact('pratica'));
    }

    public function riepilogoPdf(Pratica $pratica)
    {
        return $this->renderRiepilogoPdf($pratica, false);
    }

    public function riepilogoPdfDownload(Pratica $pratica)
    {
        return $this->renderRiepilogoPdf($pratica, true);
    }

    private function renderRiepilogoPdf(Pratica $pratica, bool $download)
    {
        $this->preparaRiepilogo($pratica);
        $nomeFile = 'riepilogo-pratica-' . $pratica->id . '.pdf';
        $pdf = Pdf::loadView('pratiche.riepilogo-pdf', compact('pratica'))->setPaper('a4');

        return $download ? $pdf->download($nomeFile) : $pdf->stream($nomeFile);
    }

    private function preparaRiepilogo(Pratica $pratica): void
    {
        $pratica->load(['viaggio', 'clienti', 'documenti']);
        $pratica->clienti->each(function (Cliente $cliente) use ($pratica) {
            $cliente->setAttribute('quota_pratica', $this->quotaCliente($pratica, $cliente));
        });
    }

    public function update(Request $request, Pratica $pratica): RedirectResponse
    {
        $validated = $this->validatePratica($request);
        $pratica->update($this->praticaData($validated));
        $clienti = $pratica->clienti()->pluck('clienti.id')->all();
        $pratica->clienti()->syncWithoutDetaching($this->clientiConTariffe([
            'clienti' => $clienti,
            'gratuiti' => $validated['gratuiti'] ?? [],
            'ridotti' => $validated['ridotti'] ?? [],
        ]));
        $this->ricalcolaTotale($pratica);

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
            'viaggio_id', 'cabina', 'totale_quote', 'totale', 'sconto', 'assicurazione_annullamento', 'supplemento_singola', 'acconto', 'data_acconto', 'saldo', 'data_saldo', 'note', 'clienti', 'gratuiti', 'ridotti',
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

    private function formData(Pratica $pratica, bool $soloViaggiAttivi = false): array
    {
        return [
            'pratica' => $pratica,
            'viaggi' => Viaggio::query()
                ->when($soloViaggiAttivi, fn ($query) => $query->whereDate('data_partenza', '>=', today()))
                ->orderBy('data_partenza')
                ->orderBy('nome')
                ->get(),
        ];
    }

    private function queryElenco(?string $ricerca, bool $mostraPassati, ?int $viaggioId, ?string $pagamento = null, array $ordinamenti = [])
    {
        $query = Pratica::with(['viaggio', 'clienti'])
            ->when($viaggioId, fn ($query) => $query->where('viaggio_id', $viaggioId))
            ->when(! $viaggioId && ! $mostraPassati, fn ($query) => $query->whereHas('viaggio', fn ($viaggi) => $viaggi->whereDate('data_partenza', '>=', today())))
            ->when($ricerca, function ($query, $ricerca) {
                $operatore = $this->likeOperator();
                $query->where(function ($query) use ($ricerca, $operatore) {
                    $query->whereHas('viaggio', function ($viaggi) use ($ricerca, $operatore) {
                        $viaggi->where('nome', $operatore, "%{$ricerca}%")
                            ->orWhere('destinazione', $operatore, "%{$ricerca}%")
                            ->orWhere('tipologia', $operatore, "%{$ricerca}%");
                    })->orWhereHas('clienti', function ($clienti) use ($ricerca, $operatore) {
                        $clienti->where('nome', $operatore, "%{$ricerca}%")
                            ->orWhere('cognome', $operatore, "%{$ricerca}%");
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

        $this->applySort($query, $ordinamenti);

        return $query;
    }

    private function likeOperator(): string
    {
        return DB::connection()->getDriverName() === 'pgsql' ? 'ilike' : 'like';
    }

    private function parseSort(?string $sort): array
    {
        if (! is_string($sort) || trim($sort) === '') {
            return [];
        }

        $allowedFields = ['viaggio', 'clienti', 'totale', 'acconto', 'sconto', 'residuo'];
        $entries = [];

        foreach (explode(',', $sort) as $token) {
            [$field, $direction] = array_pad(explode(':', trim($token), 2), 2, null);
            $field = (string) $field;
            $direction = strtolower((string) $direction);

            if (! in_array($field, $allowedFields, true) || ! in_array($direction, ['asc', 'desc'], true)) {
                continue;
            }

            $entries = array_values(array_filter($entries, fn (array $entry) => $entry['field'] !== $field));
            $entries[] = ['field' => $field, 'direction' => $direction];
        }

        return $entries;
    }

    private function sortToString(array $ordinamenti): string
    {
        return collect($ordinamenti)
            ->map(fn (array $entry) => $entry['field'] . ':' . $entry['direction'])
            ->implode(',');
    }

    private function applySort($query, array $ordinamenti): void
    {
        if (empty($ordinamenti)) {
            $query->latest();

            return;
        }

        if (collect($ordinamenti)->contains(fn (array $entry) => $entry['field'] === 'viaggio')) {
            $query->leftJoin('viaggi as viaggio_sort', 'viaggio_sort.id', '=', 'pratiche.viaggio_id')
                ->select('pratiche.*');
        }

        foreach ($ordinamenti as $entry) {
            $direction = $entry['direction'];

            switch ($entry['field']) {
                case 'viaggio':
                    $query->orderBy('viaggio_sort.nome', $direction);
                    break;

                case 'clienti':
                    $query->orderByRaw("(
                        select min(clienti.cognome || ' ' || clienti.nome)
                        from clienti
                        inner join cliente_pratica on cliente_pratica.cliente_id = clienti.id
                        where cliente_pratica.pratica_id = pratiche.id
                    ) {$direction}");
                    break;

                case 'totale':
                    $query->orderBy('pratiche.totale', $direction);
                    break;

                case 'acconto':
                    $query->orderBy('pratiche.acconto', $direction);
                    break;

                case 'sconto':
                    $query->orderBy('pratiche.sconto', $direction);
                    break;

                case 'residuo':
                    $query->orderByRaw("(pratiche.totale - pratiche.acconto - pratiche.saldo) {$direction}");
                    break;
            }
        }

        $query->orderByDesc('pratiche.id');
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
        $viaggio = Viaggio::find($request->input('viaggio_id'));
        $tipiCabina = collect($viaggio?->prezzi_cabine ?? [])->pluck('tipo')->filter()->values()->all();
        $regoleCabina = $viaggio?->tipologia === 'crociera'
            ? ['required', 'string', Rule::in($tipiCabina)]
            : ['nullable', 'string', Rule::in($tipiCabina)];
        $gratuiti = $request->input('gratuiti', []);
        $gratuiti = is_array($gratuiti) ? $gratuiti : [];

        $rules = [
            'viaggio_id' => ['required', 'exists:viaggi,id'],
            'cabina' => $regoleCabina,
            'totale_quote' => ['nullable', 'numeric', 'min:0'],
            'totale' => ['nullable', 'numeric', 'min:0'],
            'sconto' => ['nullable', 'numeric', 'min:0'],
            'assicurazione_annullamento' => ['nullable', 'numeric', 'min:0'],
            'supplemento_singola' => ['nullable', 'numeric', 'min:0'],
            'acconto' => ['nullable', 'numeric', 'min:0'],
            'data_acconto' => ['nullable', 'date'],
            'saldo' => ['nullable', 'numeric', 'min:0'],
            'data_saldo' => ['nullable', 'date'],
            'note' => ['nullable', 'string'],
            'gratuiti' => ['nullable', 'array'],
            'gratuiti.*' => ['integer', 'distinct', 'exists:clienti,id'],
            'ridotti' => ['nullable', 'array'],
            'ridotti.*' => ['integer', 'distinct', 'exists:clienti,id', Rule::notIn($gratuiti)],
        ];

        if ($richiedeClienti) {
            $rules['clienti'] = ['required', 'array', 'min:1'];
            $rules['clienti.*'] = ['integer', 'distinct', 'exists:clienti,id'];
        }

        return $request->validate($rules, [
            'ridotti.*.not_in' => 'Un cliente non può essere contemporaneamente gratuito e ridotto.',
        ]);
    }

    private function clientiConTariffe(array $validated): array
    {
        $gratuiti = collect($validated['gratuiti'] ?? [])->map(fn ($id) => (int) $id)->all();
        $ridotti = collect($validated['ridotti'] ?? [])->map(fn ($id) => (int) $id)->all();

        return collect($validated['clienti'])
            ->mapWithKeys(fn ($id) => [(int) $id => [
                'gratuito' => in_array((int) $id, $gratuiti, true),
                'ridotto' => in_array((int) $id, $ridotti, true),
            ]])
            ->all();
    }

    private function ricalcolaTotale(Pratica $pratica): void
    {
        $pratica->load(['viaggio', 'clienti']);
        $totaleQuote = $pratica->clienti->sum(fn (Cliente $cliente) => $this->quotaCliente($pratica, $cliente));
        $totale = max(0, $totaleQuote
            + (float) $pratica->assicurazione_annullamento
            + (float) $pratica->supplemento_singola
            - (float) $pratica->sconto);

        $pratica->update([
            'totale_quote' => $totaleQuote,
            'totale' => $totale,
        ]);
    }

    private function quotaCliente(Pratica $pratica, Cliente $cliente): float
    {
        if ($cliente->pivot->gratuito) {
            return 0;
        }

        $viaggio = $pratica->viaggio;
        $quotaBase = $viaggio->tipologia === 'crociera'
            ? (collect($viaggio->prezzi_cabine ?? [])->firstWhere('tipo', $pratica->cabina)['prezzo'] ?? $viaggio->prezzo)
            : $viaggio->prezzo;

        if ($cliente->pivot->ridotto) {
            if ($viaggio->tipologia === 'crociera' && $viaggio->quota_fissa !== null) {
                return (float) $viaggio->quota_fissa;
            }

            if ($viaggio->quota_ridotto !== null) {
                return (float) $viaggio->quota_ridotto;
            }
        }

        return (float) ($quotaBase ?? 0);
    }

    private function praticaData(array $validated): array
    {
        return [
            'viaggio_id' => $validated['viaggio_id'],
            'cabina' => $validated['cabina'] ?? null,
            'totale_quote' => 0,
            'totale' => 0,
            'sconto' => $validated['sconto'] ?? 0,
            'assicurazione_annullamento' => $validated['assicurazione_annullamento'] ?? 0,
            'supplemento_singola' => $validated['supplemento_singola'] ?? 0,
            'acconto' => $validated['acconto'] ?? 0,
            'data_acconto' => $validated['data_acconto'] ?? null,
            'saldo' => $validated['saldo'] ?? 0,
            'data_saldo' => $validated['data_saldo'] ?? null,
            'note' => $validated['note'] ?? null,
        ];
    }
}