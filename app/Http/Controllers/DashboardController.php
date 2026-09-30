<?php

namespace App\Http\Controllers;

use App\Models\AppSetting;
use App\Models\Cliente;
use App\Models\Pratica;
use App\Models\Viaggio;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public const WIDGETS = [
        'clienti' => 'Clienti',
        'viaggi' => 'Viaggi',
        'pratiche' => 'Pratiche',
        'top_viaggi' => 'Viaggi più venduti',
        'capacita_viaggi' => 'Stato conferma viaggi',
    ];

    public function index(): View
    {
        $oggi = today();
        $widgetsAttivi = auth()->user()->dashboard_widgets ?? array_keys(self::WIDGETS);
        $soglie = collect(['carta_identita', 'passaporto', 'patente', 'altro'])
            ->mapWithKeys(fn ($tipo) => [$tipo => (int) (AppSetting::where('key', "documenti.scadenza.{$tipo}")->value('value') ?? 30)]);

        $statiClienti = Cliente::with('documenti')->get()->countBy(function (Cliente $cliente) use ($oggi, $soglie) {
            if ($cliente->documenti->isEmpty() || $cliente->documenti->contains(fn ($documento) => $documento->scadenza?->lt($oggi))) {
                return 'scaduti';
            }

            return $cliente->documenti->contains(function ($documento) use ($oggi, $soglie) {
                $soglia = $soglie[$documento->tipo] ?? $soglie['altro'];

                return $documento->scadenza && $documento->scadenza->lte($oggi->copy()->addDays($soglia));
            }) ? 'in_scadenza' : 'in_regola';
        });

        $viaggi = Viaggio::whereDate('data_partenza', '>=', $oggi)->get();
        $viaggiCapacita = collect();

        if (in_array('capacita_viaggi', $widgetsAttivi, true) && $viaggi->isNotEmpty()) {
            $iscrittiPerViaggio = DB::table('cliente_pratica')
                ->join('pratiche', 'pratiche.id', '=', 'cliente_pratica.pratica_id')
                ->whereIn('pratiche.viaggio_id', $viaggi->modelKeys())
                ->selectRaw('pratiche.viaggio_id as viaggio_id, COUNT(DISTINCT cliente_pratica.cliente_id) as iscritti')
                ->groupBy('pratiche.viaggio_id')
                ->pluck('iscritti', 'viaggio_id');

            $viaggiCapacita = $viaggi->map(function (Viaggio $viaggio) use ($oggi, $iscrittiPerViaggio) {
                $iscritti = (int) ($iscrittiPerViaggio[$viaggio->id] ?? 0);
                $minimo = (int) $viaggio->minimo_partecipanti;
                $massimo = $viaggio->massimo_partecipanti !== null ? (int) $viaggio->massimo_partecipanti : null;
                $giorniAllaPartenza = (int) $oggi->diffInDays($viaggio->data_partenza, false);
                $inScadenza = $giorniAllaPartenza >= 0 && $giorniAllaPartenza <= 30 && $iscritti < $minimo;
                $quasiPieno = $massimo !== null
                    && $massimo > 0
                    && $iscritti >= ceil($massimo * 0.8)
                    && $iscritti < $massimo;
                $completo = $massimo !== null && $massimo > 0 && $iscritti >= $massimo;

                return [
                    'id' => $viaggio->id,
                    'titolo' => $viaggio->nome,
                    'dataPartenza' => $viaggio->data_partenza->format('d/m/Y'),
                    'ordinamentoData' => $viaggio->data_partenza->getTimestamp(),
                    'giorniAllaPartenza' => $giorniAllaPartenza,
                    'numeroMinimo' => $minimo,
                    'numeroMassimo' => $massimo,
                    'iscritti' => $iscritti,
                    'inScadenza' => $inScadenza,
                    'priorita' => $inScadenza ? 0 : ($quasiPieno ? 1 : ($completo ? 2 : 3)),
                ];
            })->sortBy([
                ['priorita', 'asc'],
                ['ordinamentoData', 'asc'],
            ])->values();
        }

        $pratiche = Pratica::with('viaggio')
            ->whereHas('viaggio', fn ($query) => $query->whereDate('data_partenza', '>=', $oggi))
            ->get();
        $sogliePagamenti = [
            'acconto' => (int) (AppSetting::where('key', 'pratiche.scadenza.acconto')->value('value') ?? 30),
            'saldo' => (int) (AppSetting::where('key', 'pratiche.scadenza.saldo')->value('value') ?? 30),
        ];
        $statiPratiche = $pratiche->countBy(fn (Pratica $pratica) => $this->statoPagamento($pratica, $oggi, $sogliePagamenti));

        $topViaggi = Viaggio::withCount('pratiche')
            ->has('pratiche')
            ->orderByDesc('pratiche_count')
            ->take(5)
            ->get();

        return view('dashboard', [
            'widgetsDisponibili' => self::WIDGETS,
            'widgetsAttivi' => $widgetsAttivi,
            'totaleClienti' => Cliente::count(),
            'statiClienti' => $statiClienti,
            'totaleViaggi' => $viaggi->count(),
            'tipologieViaggi' => $viaggi->countBy('tipologia'),
            'totalePratiche' => $pratiche->count(),
            'statiPratiche' => $statiPratiche,
            'topViaggi' => $topViaggi,
            'travels' => $viaggiCapacita,
        ]);
    }

    public function updateWidgets(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'widgets' => ['nullable', 'array'],
            'widgets.*' => ['string', 'in:' . implode(',', array_keys(self::WIDGETS))],
        ]);

        $selezionati = collect($validated['widgets'] ?? [])->unique()->values();
        $ordineAttuale = collect($request->user()->dashboard_widgets ?? array_keys(self::WIDGETS));
        $ordineAggiornato = $ordineAttuale
            ->intersect($selezionati)
            ->concat($selezionati->diff($ordineAttuale))
            ->unique()
            ->values();

        $request->user()->update(['dashboard_widgets' => $ordineAggiornato->all()]);

        return redirect()->route('dashboard');
    }

    public function updateWidgetOrder(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'order' => ['required', 'array'],
            'order.*' => ['required', 'string', 'distinct', 'in:' . implode(',', array_keys(self::WIDGETS))],
        ]);

        $widgetsAttivi = collect($request->user()->dashboard_widgets ?? array_keys(self::WIDGETS));
        $ordineRichiesto = collect($validated['order'])->intersect($widgetsAttivi)->unique();
        $ordineAggiornato = $ordineRichiesto
            ->concat($widgetsAttivi->diff($ordineRichiesto))
            ->values();

        $request->user()->update(['dashboard_widgets' => $ordineAggiornato->all()]);

        return response()->json(['ok' => true]);
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
            $giorni = $oggi->diffInDays($dataAcconto, false);

            return $giorni > $soglie['acconto'] ? 'acconto_non_versato' : 'acconto_non_versato_scadenza';
        }

        $dataSaldo = $pratica->viaggio->data_saldo ?? $pratica->viaggio->data_partenza;
        $giorni = $oggi->diffInDays($dataSaldo, false);

        return $giorni > $soglie['saldo'] ? 'acconto_versato' : 'saldo_non_versato_scadenza';
    }
}

