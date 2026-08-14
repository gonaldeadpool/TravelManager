<?php

namespace App\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Viaggio;
use App\Models\TappaRaccolta;
use App\Models\Cliente;
use App\Support\LocalStoragePaths;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ViaggioController extends Controller
{
    public function index(Request $request): View
    {
        $ricerca = $request->input('ricerca');
        $mostraPassati = $request->boolean('mostra_passati');
        $tipologia = $request->input('tipologia');

        $viaggi = $this->queryRicerca($ricerca, $mostraPassati, $tipologia)
            ->with('pratiche.clienti')
            ->orderBy('data_partenza')
            ->paginate(5)
            ->withQueryString();

        return view('viaggi', [
            'viaggi' => $viaggi,
            'ricerca' => $ricerca,
            'mostraPassati' => $mostraPassati,
            'tipologia' => $tipologia,
        ]);
    }

    public function search(Request $request): View
    {
        $ricerca = $request->input('q');
        $mostraPassati = $request->boolean('mostra_passati');
        $tipologia = $request->input('tipologia');
        $viaggi = $this->queryRicerca($request->input('q'), $request->boolean('mostra_passati'), $request->input('tipologia'))
            ->with('pratiche.clienti')
            ->orderBy('data_partenza')
            ->paginate(5)
            ->withPath(route('viaggi.index'))
            ->appends(array_filter([
                'ricerca' => $ricerca,
                'mostra_passati' => $mostraPassati ? 1 : null,
                'tipologia' => $tipologia,
            ]));

        return view('viaggi._table', compact('viaggi'));
    }

    public function create(Request $request): View
    {
        $data = $request->date('data_partenza');

        return view('viaggi-create', [
            'viaggio' => new Viaggio([
                'trasporti' => [],
                'data_partenza' => $data,
                'data_rientro' => $data,
            ]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateViaggio($request);
        $validated['trasporti'] = $this->normalizzaOpzioni($request->input('trasporti', []));
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
        return view('viaggi-show', $this->buildRiepilogoViewData($viaggio));
    }

    public function riepilogoPdf(Viaggio $viaggio)
    {
        return $this->renderRiepilogoPdf($viaggio, false);
    }

    public function riepilogoPdfDownload(Viaggio $viaggio)
    {
        return $this->renderRiepilogoPdf($viaggio, true);
    }

    private function renderRiepilogoPdf(Viaggio $viaggio, bool $download)
    {
        $data = $this->buildRiepilogoViewData($viaggio);
        $nomeFile = 'riepilogo-viaggio-' . Str::slug($viaggio->nome ?: 'viaggio') . '.pdf';
        $pdf = Pdf::loadView('viaggi-riepilogo-pdf', $data)
            ->setPaper('a4');

        return $download
            ? $pdf->download($nomeFile)
            : $pdf->stream($nomeFile);
    }

    public function creaTappaRaccolta(Request $request, Viaggio $viaggio): JsonResponse
    {
        $validated = $request->validate([
            'nome' => ['required', 'string', 'max:150'],
            'orario' => ['required', 'date_format:H:i'],
        ]);

        $tappa = $viaggio->tappeRaccolta()->create($validated);

        return response()->json([
            'id' => $tappa->id,
            'nome' => $tappa->nome,
            'orario' => $tappa->orario->format('H:i'),
        ]);
    }

    public function assegnaClienteTappa(Request $request, Viaggio $viaggio, TappaRaccolta $tappa): JsonResponse
    {
        abort_unless($tappa->viaggio_id === $viaggio->id, 404);
        $validated = $request->validate(['cliente_id' => ['required', 'integer', 'exists:clienti,id']]);
        $partecipa = $viaggio->pratiche()->whereHas('clienti', fn ($query) => $query->whereKey($validated['cliente_id']))->exists();
        abort_unless($partecipa, 422, 'Il cliente non partecipa a questo viaggio.');

        $tappaIds = $viaggio->tappeRaccolta()->pluck('id');
        DB::table('viaggio_tappa_cliente')->whereIn('tappa_id', $tappaIds)->where('cliente_id', $validated['cliente_id'])->delete();
        DB::table('viaggio_tappa_cliente')->insert([
            'viaggio_id' => $viaggio->id,
            'tappa_id' => $tappa->id,
            'cliente_id' => $validated['cliente_id'],
        ]);

        return response()->json(['ok' => true]);
    }

    public function rimuoviClienteTappa(Request $request, Viaggio $viaggio, TappaRaccolta $tappa, Cliente $cliente): JsonResponse
    {
        abort_unless($tappa->viaggio_id === $viaggio->id, 404);
        DB::table('viaggio_tappa_cliente')->where('tappa_id', $tappa->id)->where('cliente_id', $cliente->id)->delete();

        return response()->json(['ok' => true]);
    }

    public function assegnaPosto(Request $request, Viaggio $viaggio): JsonResponse
    {
        $busIndex = $request->integer('bus');
        $bus = collect($viaggio->trasporti ?? [])
            ->filter(fn ($trasporto) => ($trasporto['tipo'] ?? null) === 'bus')
            ->values()
            ->get($busIndex);
        $posti = (int) ($bus['posti'] ?? 0);
        $validated = $request->validate([
            'cliente_id' => ['required', 'integer', 'exists:clienti,id'],
            'posto' => ['nullable', 'integer', 'min:1', 'max:' . max(55, $posti)],
            'bus' => ['required', 'integer', 'min:0'],
        ]);
        abort_if(! $bus, 422, 'Bus non configurato per questo viaggio.');
        $pratiche = $viaggio->pratiche()->whereHas('clienti', fn ($query) => $query->whereKey($validated['cliente_id']))->get();
        abort_if($pratiche->isEmpty(), 422, 'Il cliente non partecipa a questo viaggio.');

        $praticaIds = $viaggio->pratiche()->pluck('id');
        DB::table('cliente_pratica')
            ->whereIn('pratica_id', $praticaIds)
            ->where('cliente_id', $validated['cliente_id'])
            ->update(['posto' => null, 'posto_bus' => null]);
        if ($validated['posto'] !== null) {
            DB::table('cliente_pratica')
                ->whereIn('pratica_id', $praticaIds)
                ->where('posto_bus', $validated['bus'])
                ->where('posto', $validated['posto'])
                ->update(['posto' => null, 'posto_bus' => null]);
        }
        foreach ($pratiche as $pratica) {
            $pratica->clienti()->updateExistingPivot($validated['cliente_id'], [
                'posto' => $validated['posto'],
                'posto_bus' => $validated['posto'] === null ? null : $validated['bus'],
            ]);
        }

        return response()->json(['ok' => true]);
    }

    public function update(Request $request, Viaggio $viaggio): RedirectResponse
    {
        $validated = $this->validateViaggio($request);
        $validated['trasporti'] = $this->normalizzaOpzioni($request->input('trasporti', []));
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
            'prezzi_cabine.*.tipo' => ['required_if:tipologia,crociera', 'in:interna,vista_mare,balcone'],
            'prezzi_cabine.*.prezzo' => ['required_if:tipologia,crociera', 'nullable', 'numeric', 'min:0'],
            'eta_gratuita' => ['nullable', 'integer', 'min:0', 'max:17'],
            'note' => ['nullable', 'string'],
            'locandina' => ['nullable', 'file', 'image', 'max:5120'],
            'trasporti' => ['nullable', 'array'],
            'trasporti.*.tipo' => ['required', 'in:bus,aereo,treno'],
            'trasporti.*.posti' => ['nullable', 'integer', 'min:1'],
        ]);
    }

    private function queryRicerca(?string $ricerca, bool $mostraPassati = false, ?string $tipologia = null)
    {
        return Viaggio::query()
            ->when(! $mostraPassati, fn ($query) => $query->whereDate('data_partenza', '>=', today()))
            ->when(in_array($tipologia, ['viaggio', 'tour', 'crociera'], true), fn ($query) => $query->where('tipologia', $tipologia))
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

    private function buildRiepilogoViewData(Viaggio $viaggio): array
    {
        $viaggio->load(['pratiche.clienti', 'tappeRaccolta.clienti']);

        $busTrasporti = collect($viaggio->trasporti ?? [])
            ->filter(fn ($trasporto) => ($trasporto['tipo'] ?? null) === 'bus')
            ->values();

        return [
            'viaggio' => $viaggio,
            'numeroPartecipanti' => $viaggio->pratiche->flatMap->clienti->unique('id')->count(),
            'importoAcconto' => $viaggio->pratiche->sum('acconto'),
            'importoSaldo' => $viaggio->pratiche->sum('saldo'),
            'busTrasporti' => $busTrasporti,
            'tappeRaccolta' => $viaggio->tappeRaccolta,
        ];
    }

    private function eliminaLocandina(string $percorso): void
    {
        LocalStoragePaths::disk(LocalStoragePaths::locandine())->delete($percorso);
        Storage::disk('public')->delete($percorso);
    }
}
