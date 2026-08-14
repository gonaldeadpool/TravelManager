<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\ClienteDocumento;
use App\Models\AppSetting;
use App\Support\LocalStoragePaths;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ClienteController extends Controller
{
    public function index(Request $request): View
    {
        $ricerca = $request->input('ricerca');
        $documentiStato = $request->input('documenti_stato');
        $sort = $request->input('sort');
        $ordinamenti = $this->parseSort($sort);
        $clienti = Cliente::with('documenti');

        if ($ricerca) {
            $clienti->where(function ($query) use ($ricerca) {
                $query->where('nome', 'like', "%{$ricerca}%")
                    ->orWhere('cognome', 'like', "%{$ricerca}%")
                    ->orWhereRaw("concat(cognome, ' ', nome) like ?", ["%{$ricerca}%"])
                    ->orWhere('email', 'like', "%{$ricerca}%");
            });
        }

        if (in_array($documentiStato, ['in_regola', 'in_scadenza', 'scaduti'], true)) {
            $oggi = today();
            $soglie = $this->scadenzeDocumenti();
            if ($documentiStato === 'scaduti') {
                $clienti->where(fn ($query) => $query->doesntHave('documenti')->orWhereHas('documenti', fn ($documenti) => $documenti->whereDate('scadenza', '<', $oggi)));
            } elseif ($documentiStato === 'in_scadenza') {
                $clienti->whereHas('documenti', function ($query) use ($oggi, $soglie) {
                    $query->whereDate('scadenza', '>=', $oggi)->where(function ($query) use ($soglie, $oggi) {
                        foreach ($soglie as $tipo => $giorni) {
                            $query->orWhere(fn ($documenti) => $documenti->where('tipo', $tipo)->whereDate('scadenza', '<=', $oggi->copy()->addDays($giorni)));
                        }
                    });
                });
            } else {
                $clienti->whereHas('documenti')->whereDoesntHave('documenti', function ($query) use ($oggi, $soglie) {
                    $query->whereDate('scadenza', '<', $oggi)->orWhere(function ($query) use ($soglie, $oggi) {
                        foreach ($soglie as $tipo => $giorni) {
                            $query->orWhere(fn ($documenti) => $documenti->where('tipo', $tipo)->whereDate('scadenza', '<=', $oggi->copy()->addDays($giorni)));
                        }
                    });
                });
            }
        }

        $this->applySort($clienti, $ordinamenti);

        return view('clienti', [
            'clienti' => $clienti->paginate(5)->withQueryString(),
            'ricerca' => $ricerca,
            'documentiStato' => $documentiStato,
            'scadenzeDocumenti' => $this->scadenzeDocumenti(),
            'ordinamenti' => $ordinamenti,
        ]);
    }

    public function search(Request $request): View
    {
        $ricerca = $request->input('q');
        $documentiStato = $request->input('documenti_stato');
        $sort = $request->input('sort');
        $ordinamenti = $this->parseSort($sort);
        $clienti = Cliente::with('documenti')->where(function ($query) use ($ricerca) {
            $query->where('nome', 'like', "%{$ricerca}%")
                ->orWhere('cognome', 'like', "%{$ricerca}%")
                ->orWhereRaw("concat(cognome, ' ', nome) like ?", ["%{$ricerca}%"])
                ->orWhere('email', 'like', "%{$ricerca}%");
        });

        if (in_array($documentiStato, ['in_regola', 'in_scadenza', 'scaduti'], true)) {
            $oggi = today();
            $soglie = $this->scadenzeDocumenti();

            if ($documentiStato === 'scaduti') {
                $clienti->where(fn ($query) => $query->doesntHave('documenti')->orWhereHas('documenti', fn ($documenti) => $documenti->whereDate('scadenza', '<', $oggi)));
            } elseif ($documentiStato === 'in_scadenza') {
                $clienti->whereHas('documenti', function ($query) use ($oggi, $soglie) {
                    $query->whereDate('scadenza', '>=', $oggi)->where(function ($query) use ($soglie, $oggi) {
                        foreach ($soglie as $tipo => $giorni) {
                            $query->orWhere(fn ($documenti) => $documenti->where('tipo', $tipo)->whereDate('scadenza', '<=', $oggi->copy()->addDays($giorni)));
                        }
                    });
                });
            } else {
                $clienti->whereHas('documenti')->whereDoesntHave('documenti', function ($query) use ($oggi, $soglie) {
                    $query->whereDate('scadenza', '<', $oggi)->orWhere(function ($query) use ($soglie, $oggi) {
                        foreach ($soglie as $tipo => $giorni) {
                            $query->orWhere(fn ($documenti) => $documenti->where('tipo', $tipo)->whereDate('scadenza', '<=', $oggi->copy()->addDays($giorni)));
                        }
                    });
                });
            }
        }

        $this->applySort($clienti, $ordinamenti);

        return view('clienti._table', [
            'clienti' => $clienti->paginate(5)->appends(array_filter([
                'q' => $ricerca,
                'documenti_stato' => $documentiStato,
                'sort' => empty($ordinamenti) ? null : $this->sortToString($ordinamenti),
            ])),
            'scadenzeDocumenti' => $this->scadenzeDocumenti(),
            'ordinamenti' => $ordinamenti,
        ]);
    }

    public function create(): View
    {
        return view('clienti-create', ['documenti' => collect()]);
    }

    public function riepilogo(Cliente $cliente): View
    {
        $cliente->load([
            'documenti',
            'pratiche' => fn ($query) => $query->with('viaggio')->orderByDesc('created_at'),
        ]);

        return view('clienti-riepilogo', compact('cliente'));
    }

    public function edit($id): View
    {
        $cliente = Cliente::findOrFail($id);

        return view('clienti-edit', [
            'cliente' => $cliente,
            'documenti' => $cliente->documenti()->latest()->get(),
        ]);
    }

    public function update(Request $request, $id): RedirectResponse
    {
        $cliente = Cliente::findOrFail($id);
        $this->validateCliente($request);
        $cliente->update($this->clienteData($request));
        $this->salvaDocumento($request, $cliente);

        return redirect()->route('clienti');
    }

    public function destroy($id): RedirectResponse
    {
        $cliente = Cliente::findOrFail($id);

        foreach ($cliente->documenti as $documento) {
            LocalStoragePaths::disk(LocalStoragePaths::documenti())->delete($documento->percorso);
        }

        $cliente->delete();

        return redirect()->route('clienti');
    }

    public function store(Request $request): RedirectResponse
    {
        $this->validateCliente($request);
        $cliente = Cliente::create($this->clienteData($request));
        $this->salvaDocumento($request, $cliente);

        return redirect()->route('clienti');
    }

    public function downloadDocument(Cliente $cliente, ClienteDocumento $documento)
    {
        abort_unless($documento->cliente_id === $cliente->id, 404);

        $disk = LocalStoragePaths::disk(LocalStoragePaths::documenti());
        abort_unless($disk->exists($documento->percorso), 404);

        return $disk->response($documento->percorso);
    }

    public function storeDocument(Request $request, Cliente $cliente)
    {
        $request->validate([
            'documento_tipo' => ['nullable', 'in:carta_identita,passaporto,patente,altro'],
            'documento_numero' => ['nullable', 'string', 'max:100'],
            'documento_scadenza' => ['nullable', 'date'],
            'documento_file' => ['required', 'file', 'mimes:pdf,jpeg,jpg', 'max:10240'],
        ]);

        $documento = $this->salvaDocumento($request, $cliente);

        return response()->json([
            'html' => view('clienti._documento', compact('cliente', 'documento'))->render(),
            'count' => $cliente->documenti()->count(),
        ]);
    }

    public function destroyDocument(Cliente $cliente, ClienteDocumento $documento): RedirectResponse
    {
        abort_unless($documento->cliente_id === $cliente->id, 404);

        LocalStoragePaths::disk(LocalStoragePaths::documenti())->delete($documento->percorso);
        $documento->delete();

        return redirect()->route('clienti.edit', $cliente)->with('success', 'Documento eliminato correttamente.');
    }

    private function validateCliente(Request $request): void
    {
        $request->validate([
            'nome' => ['required', 'max:100'],
            'cognome' => ['required', 'max:100'],
            'documento_tipo' => ['nullable', 'in:carta_identita,passaporto,patente,altro'],
            'documento_numero' => ['nullable', 'string', 'max:100'],
            'documento_scadenza' => ['nullable', 'date'],
            'documento_file' => ['nullable', 'file', 'mimes:pdf,jpeg,jpg', 'max:10240'],
        ]);
    }

    private function clienteData(Request $request): array
    {
        return [
            'nome' => $request->nome,
            'cognome' => $request->cognome,
            'data_nascita' => $request->data_nascita,
            'luogo_nascita' => $request->luogo_nascita,
            'codice_fiscale' => $request->codice_fiscale,
            'telefono' => $request->telefono,
            'cellulare' => $request->cellulare,
            'email' => $request->email,
            'indirizzo' => $request->indirizzo,
            'cap' => $request->cap,
            'citta' => $request->citta,
            'provincia' => $request->provincia,
            'nazione' => $request->nazione,
            'note' => $request->note,
        ];
    }

    private function scadenzeDocumenti(): array
    {
        return collect(['carta_identita', 'passaporto', 'patente', 'altro'])
            ->mapWithKeys(fn ($tipo) => [$tipo => (int) (AppSetting::where('key', "documenti.scadenza.{$tipo}")->value('value') ?? 30)])
            ->all();
    }

    private function salvaDocumento(Request $request, Cliente $cliente): ?ClienteDocumento
    {
        if (! $request->hasFile('documento_file')) {
            return null;
        }

        LocalStoragePaths::ensureDirectories();
        $file = $request->file('documento_file');
        $nome = Str::uuid()->toString() . ($file->getClientOriginalExtension() ? '.' . $file->getClientOriginalExtension() : '');
        $disk = LocalStoragePaths::disk(LocalStoragePaths::documenti());
        $disk->putFileAs('', $file, $nome);

        return $cliente->documenti()->create([
            'tipo' => $request->input('documento_tipo'),
            'numero' => $request->input('documento_numero'),
            'scadenza' => $request->input('documento_scadenza'),
            'nome_originale' => $file->getClientOriginalName(),
            'percorso' => $nome,
            'mime_type' => $file->getMimeType(),
            'dimensione' => $file->getSize(),
        ]);
    }

    private function parseSort(?string $sort): array
    {
        if (! is_string($sort) || trim($sort) === '') {
            return [];
        }

        $allowedFields = ['cliente', 'email', 'telefono', 'documenti'];
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
            $query->orderBy('clienti.cognome')->orderBy('clienti.nome');

            return;
        }

        foreach ($ordinamenti as $entry) {
            $direction = $entry['direction'];

            switch ($entry['field']) {
                case 'cliente':
                    $query->orderBy('clienti.cognome', $direction);
                    $query->orderBy('clienti.nome', $direction);
                    break;

                case 'email':
                    $query->orderBy('clienti.email', $direction);
                    break;

                case 'telefono':
                    $query->orderBy('clienti.telefono', $direction);
                    break;

                case 'documenti':
                    $query->orderByRaw("(
                        select count(*) from cliente_documenti
                        where cliente_documenti.cliente_id = clienti.id
                    ) {$direction}");
                    break;
            }
        }

        $query->orderByDesc('clienti.id');
    }
}
