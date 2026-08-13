<?php

namespace App\Http\Controllers;

use App\Models\AppSetting;
use App\Models\Cliente;
use App\Models\Pratica;
use App\Models\Viaggio;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $oggi = today();
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
        $pratiche = Pratica::with('viaggio')
            ->whereHas('viaggio', fn ($query) => $query->whereDate('data_partenza', '>=', $oggi))
            ->get();
        $sogliePagamenti = [
            'acconto' => (int) (AppSetting::where('key', 'pratiche.scadenza.acconto')->value('value') ?? 30),
            'saldo' => (int) (AppSetting::where('key', 'pratiche.scadenza.saldo')->value('value') ?? 30),
        ];
        $statiPratiche = $pratiche->countBy(fn (Pratica $pratica) => $this->statoPagamento($pratica, $oggi, $sogliePagamenti));

        return view('dashboard', [
            'totaleClienti' => Cliente::count(),
            'statiClienti' => $statiClienti,
            'totaleViaggi' => $viaggi->count(),
            'tipologieViaggi' => $viaggi->countBy('tipologia'),
            'totalePratiche' => $pratiche->count(),
            'statiPratiche' => $statiPratiche,
        ]);
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
