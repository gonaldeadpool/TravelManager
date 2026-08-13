<?php

namespace App\Http\Controllers;

use App\Models\AppSetting;
use App\Support\LocalStoragePaths;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AmministrazioneController extends Controller
{
    public function edit(): View
    {
        return view('amministrazione', [
            'locandinePath' => LocalStoragePaths::locandine(),
            'documentiPath' => LocalStoragePaths::documenti(),
            'documentiPratichePath' => LocalStoragePaths::documentiPratiche(),
            'scadenze' => $this->scadenze(),
            'scadenzePagamenti' => $this->scadenzePagamenti(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'locandine_path' => ['required', 'string', 'max:500'],
            'documenti_path' => ['required', 'string', 'max:500'],
            'documenti_pratiche_path' => ['required', 'string', 'max:500'],
            'scadenza_carta_identita' => ['required', 'integer', 'min:0', 'max:3650'],
            'scadenza_passaporto' => ['required', 'integer', 'min:0', 'max:3650'],
            'scadenza_patente' => ['required', 'integer', 'min:0', 'max:3650'],
            'scadenza_altro' => ['required', 'integer', 'min:0', 'max:3650'],
            'scadenza_acconto' => ['required', 'integer', 'min:0', 'max:3650'],
            'scadenza_saldo' => ['required', 'integer', 'min:0', 'max:3650'],
        ]);

        AppSetting::updateOrCreate(['key' => 'storage.locandine'], ['value' => trim($validated['locandine_path'])]);
        AppSetting::updateOrCreate(['key' => 'storage.documenti'], ['value' => trim($validated['documenti_path'])]);
        AppSetting::updateOrCreate(['key' => 'storage.documenti_pratiche'], ['value' => trim($validated['documenti_pratiche_path'])]);
        foreach (['carta_identita', 'passaporto', 'patente', 'altro'] as $tipo) {
            AppSetting::updateOrCreate(
                ['key' => "documenti.scadenza.{$tipo}"],
                ['value' => (string) $validated["scadenza_{$tipo}"]]
            );
        }
        AppSetting::updateOrCreate(
            ['key' => 'pratiche.scadenza.acconto'],
            ['value' => (string) $validated['scadenza_acconto']]
        );
        AppSetting::updateOrCreate(
            ['key' => 'pratiche.scadenza.saldo'],
            ['value' => (string) $validated['scadenza_saldo']]
        );
        LocalStoragePaths::ensureDirectories();

        return redirect()->route('amministrazione')->with('success', 'Configurazione salvata correttamente.');
    }

    private function scadenze(): array
    {
        return collect(['carta_identita', 'passaporto', 'patente', 'altro'])
            ->mapWithKeys(fn ($tipo) => [$tipo => (int) (AppSetting::where('key', "documenti.scadenza.{$tipo}")->value('value') ?? 30)])
            ->all();
    }

    private function scadenzePagamenti(): array
    {
        return [
            'acconto' => (int) (AppSetting::where('key', 'pratiche.scadenza.acconto')->value('value') ?? 30),
            'saldo' => (int) (AppSetting::where('key', 'pratiche.scadenza.saldo')->value('value') ?? 30),
        ];
    }
}
