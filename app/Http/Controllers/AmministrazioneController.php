<?php

namespace App\Http\Controllers;

use App\Models\AppSetting;
use App\Support\LocalStoragePaths;
use App\Support\MailSettings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Mail\Message;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;
use Throwable;

class AmministrazioneController extends Controller
{
    public function edit(MailSettings $mailSettings): View
    {
        return view('amministrazione', [
            'locandinePath' => LocalStoragePaths::locandine(),
            'documentiPath' => LocalStoragePaths::documenti(),
            'documentiPratichePath' => LocalStoragePaths::documentiPratiche(),
            'scadenze' => $this->scadenze(),
            'scadenzePagamenti' => $this->scadenzePagamenti(),
            'mailSettings' => $mailSettings->forForm(),
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

    public function updateMail(Request $request, MailSettings $mailSettings): RedirectResponse
    {
        abort_unless($request->user()->isAdmin(), 403);

        $validated = $request->validate([
            'smtp_host' => ['required', 'string', 'max:255'],
            'smtp_port' => ['required', 'integer', 'min:1', 'max:65535'],
            'smtp_scheme' => ['required', 'in:smtp,smtps'],
            'smtp_username' => ['nullable', 'string', 'max:255'],
            'smtp_password' => ['nullable', 'string', 'max:1000'],
            'remove_smtp_password' => ['nullable', 'boolean'],
            'from_address' => ['required', 'email', 'max:254'],
            'from_name' => ['required', 'string', 'max:255'],
        ]);

        $mailSettings->save($validated);

        return redirect()->route('amministrazione')->with('success', 'Configurazione posta salvata correttamente.');
    }

    public function testMail(Request $request, MailSettings $mailSettings): RedirectResponse
    {
        abort_unless($request->user()->isAdmin(), 403);

        try {
            $mailSettings->configure();
            Mail::mailer('smtp')->raw('Questa è una mail di prova della configurazione SMTP.', function (Message $message) use ($request): void {
                $message->to($request->user()->email)
                    ->subject('Verifica configurazione posta');
            });
        } catch (Throwable $exception) {
            Log::error('Invio mail di prova SMTP non riuscito.', ['exception' => $exception]);

            return redirect()->route('amministrazione')->with('mailError', 'Impossibile inviare la mail di prova. Verifica i parametri SMTP e riprova.');
        }

        return redirect()->route('amministrazione')->with('success', 'Mail di prova inviata a ' . $request->user()->email . '.');
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
