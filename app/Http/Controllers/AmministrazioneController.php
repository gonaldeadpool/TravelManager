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
            'scadenze' => $this->scadenze(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'locandine_path' => ['required', 'string', 'max:500'],
            'documenti_path' => ['required', 'string', 'max:500'],
            'scadenza_carta_identita' => ['required', 'integer', 'min:0', 'max:3650'],
            'scadenza_passaporto' => ['required', 'integer', 'min:0', 'max:3650'],
            'scadenza_patente' => ['required', 'integer', 'min:0', 'max:3650'],
            'scadenza_altro' => ['required', 'integer', 'min:0', 'max:3650'],
        ]);

        AppSetting::updateOrCreate(['key' => 'storage.locandine'], ['value' => trim($validated['locandine_path'])]);
        AppSetting::updateOrCreate(['key' => 'storage.documenti'], ['value' => trim($validated['documenti_path'])]);
        foreach (['carta_identita', 'passaporto', 'patente', 'altro'] as $tipo) {
            AppSetting::updateOrCreate(
                ['key' => "documenti.scadenza.{$tipo}"],
                ['value' => (string) $validated["scadenza_{$tipo}"]]
            );
        }
        LocalStoragePaths::ensureDirectories();

        return redirect()->route('amministrazione')->with('success', 'Percorsi salvati correttamente.');
    }

    private function scadenze(): array
    {
        return collect(['carta_identita', 'passaporto', 'patente', 'altro'])
            ->mapWithKeys(fn ($tipo) => [$tipo => (int) (AppSetting::where('key', "documenti.scadenza.{$tipo}")->value('value') ?? 30)])
            ->all();
    }
}
