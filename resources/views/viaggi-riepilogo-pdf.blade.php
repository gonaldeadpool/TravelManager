<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <title>Riepilogo viaggio - {{ $viaggio->nome }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #111827;
            margin: 16px;
        }
        h1, h2, h3 {
            margin: 0 0 8px;
        }
        h1 {
            font-size: 20px;
            margin-bottom: 6px;
        }
        h2 {
            font-size: 15px;
            margin-top: 22px;
            border-bottom: 1px solid #d1d5db;
            padding-bottom: 4px;
        }
        h3 {
            font-size: 13px;
            margin-top: 14px;
        }
        p {
            margin: 4px 0;
        }
        .meta {
            color: #4b5563;
            margin-bottom: 10px;
        }
        .grid {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
        }
        .grid th,
        .grid td {
            border: 1px solid #d1d5db;
            padding: 6px 8px;
            vertical-align: top;
        }
        .grid th {
            background: #f3f4f6;
            text-align: left;
            font-weight: 700;
        }
        .muted {
            color: #6b7280;
        }
        .page-break {
            page-break-before: always;
        }
        .bus-shell {
            border: 1px solid #cbd5e1;
            border-radius: 10px;
            background: #f8fafc;
            padding: 8px;
            margin-top: 6px;
            page-break-inside: avoid;
        }
        .bus-head {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
        }
        .bus-head td {
            background: #e2e8f0;
            color: #334155;
            font-size: 9px;
            font-weight: 700;
            text-transform: uppercase;
            border: 1px solid #cbd5e1;
            padding: 4px 6px;
        }
        .bus-head .center {
            text-align: center;
        }
        .bus-map {
            width: 100%;
            border-collapse: separate;
            border-spacing: 2px;
            table-layout: fixed;
        }
        .seat,
        .gap {
            height: 36px;
            vertical-align: top;
            border-radius: 4px;
        }
        .seat {
            border: 1px solid #16a34a;
            background: #ffffff;
            padding: 3px;
        }
        .seat.occupied {
            border-color: #dc2626;
            background: #eff6ff;
        }
        .gap {
            border: 1px dashed #cbd5e1;
            background: #f8fafc;
        }
        .seat-number {
            font-size: 8px;
            font-weight: 700;
            color: #334155;
            line-height: 1;
        }
        .seat-name {
            margin-top: 3px;
            font-size: 7px;
            line-height: 1.1;
            color: #1d4ed8;
        }
        .bus-page {
            page-break-inside: avoid;
            break-inside: avoid;
            margin-bottom: 10px;
        }
        .bus-page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>
    @php
        $partecipanti = $viaggio->pratiche->flatMap->clienti->unique('id')->values();
    @endphp

    <h1>{{ $viaggio->nome }}</h1>
    <p class="meta">Riepilogo completo viaggio - esportazione PDF</p>

    <h2>Riepilogo</h2>
    <table class="grid">
        <tbody>
            <tr><th>Tipologia</th><td>{{ ucfirst($viaggio->tipologia) }}</td><th>Destinazione</th><td>{{ $viaggio->destinazione }}</td></tr>
            <tr><th>Periodo</th><td>{{ $viaggio->data_partenza->format('d/m/Y') }} - {{ $viaggio->data_rientro->format('d/m/Y') }}</td><th>Durata</th><td>{{ $viaggio->data_partenza->diffInDays($viaggio->data_rientro) + 1 }} giorni, {{ $viaggio->data_partenza->diffInDays($viaggio->data_rientro) }} notti</td></tr>
            <tr><th>Prezzo a persona</th><td>{{ $viaggio->prezzo !== null ? number_format($viaggio->prezzo, 2, ',', '.') . ' EUR' : '-' }}</td><th>Partecipanti</th><td>{{ $numeroPartecipanti }}</td></tr>
            <tr><th>Minimo partecipanti</th><td>{{ $viaggio->minimo_partecipanti }}</td><th>Massimo partecipanti</th><td>{{ $viaggio->massimo_partecipanti ?? '-' }}</td></tr>
            <tr><th>Data acconto</th><td>{{ $viaggio->data_acconto?->format('d/m/Y') ?? '-' }}</td><th>Importo acconto</th><td>{{ number_format($importoAcconto, 2, ',', '.') }} EUR</td></tr>
            <tr><th>Data saldo</th><td>{{ $viaggio->data_saldo?->format('d/m/Y') ?? '-' }}</td><th>Importo saldo</th><td>{{ number_format($importoSaldo, 2, ',', '.') }} EUR</td></tr>
            <tr><th>Note</th><td colspan="3">{{ $viaggio->note ?: '-' }}</td></tr>
        </tbody>
    </table>

    <h3>Trasporti</h3>
    <table class="grid">
        <thead>
            <tr><th>Tipo</th><th>Dettagli</th></tr>
        </thead>
        <tbody>
            @forelse ($viaggio->trasporti ?? [] as $trasporto)
                <tr>
                    <td>{{ ucfirst($trasporto['tipo'] ?? '-') }}</td>
                    <td>
                        @if (! empty($trasporto['posti']))
                            {{ $trasporto['posti'] }} posti
                        @else
                            -
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="2" class="muted">Nessun trasporto configurato.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="page-break"></div>
    <h2>Partecipanti</h2>
    <table class="grid">
        <thead>
            <tr><th>Cliente</th><th>Email</th><th>Telefono</th><th>Pratica</th></tr>
        </thead>
        <tbody>
            @forelse ($viaggio->pratiche as $pratica)
                @foreach ($pratica->clienti as $cliente)
                    <tr>
                        <td>{{ $cliente->cognome }} {{ $cliente->nome }}</td>
                        <td>{{ $cliente->email ?: '-' }}</td>
                        <td>{{ $cliente->telefono ?: '-' }}</td>
                        <td>#{{ $pratica->id }}</td>
                    </tr>
                @endforeach
            @empty
                <tr><td colspan="4" class="muted">Nessun partecipante associato a questo viaggio.</td></tr>
            @endforelse
        </tbody>
    </table>

    @if ($busTrasporti->isNotEmpty())
        <div class="page-break"></div>
        <h2>Bus</h2>
        @foreach ($busTrasporti as $indiceBus => $bus)
            @php
                $postiAssegnatiBus = $partecipanti
                    ->filter(fn ($cliente) => (int) ($cliente->pivot->posto_bus ?? -1) === $indiceBus && filled($cliente->pivot->posto ?? null))
                    ->keyBy(fn ($cliente) => (int) $cliente->pivot->posto);

                $numeroSedile = 0;
                $righePosti = [];
                for ($riga = 1; $riga <= 14; $riga++) {
                    $righePosti[$riga] = [];
                    for ($colonna = 1; $colonna <= 5; $colonna++) {
                        $assegnabile = $riga === 14 || ($colonna !== 3 && ! (in_array($riga, [7, 8], true) && in_array($colonna, [4, 5], true)));
                        $righePosti[$riga][$colonna] = $assegnabile ? ++$numeroSedile : null;
                    }
                }

                $totaleAssegnati = $postiAssegnatiBus->count();
                $postiBus = (int) ($bus['posti'] ?? 0);
                $postiTotaliMostrati = $postiBus > 0 ? $postiBus : $numeroSedile;
                $sediliLiberi = max($postiTotaliMostrati - $totaleAssegnati, 0);
            @endphp
            <div class="bus-page{{ $loop->last ? '' : ' bus-page-break' }}">
                <h3>Bus {{ $indiceBus + 1 }}{{ !empty($bus['posti']) ? ' - ' . $bus['posti'] . ' posti' : '' }}</h3>
                <p class="meta">Posti assegnati: {{ $totaleAssegnati }} - Posti liberi: {{ $sediliLiberi }}</p>

                <div class="bus-shell">
                    <table class="bus-head">
                        <tr>
                            <td>Autista</td>
                            <td class="center">Bus {{ $indiceBus + 1 }}</td>
                            <td style="text-align: right;">Hostess</td>
                        </tr>
                    </table>

                    <table class="bus-map">
                        <tbody>
                            @foreach ($righePosti as $riga)
                                <tr>
                                    @for ($colonna = 1; $colonna <= 5; $colonna++)
                                        @php($posto = $riga[$colonna])
                                        @if ($posto !== null)
                                            @php($clienteSeduto = $postiAssegnatiBus->get($posto))
                                            <td class="seat{{ $clienteSeduto ? ' occupied' : '' }}">
                                                <div class="seat-number">{{ $posto }}</div>
                                                @if ($clienteSeduto)
                                                    <div class="seat-name">{{ $clienteSeduto->cognome }} {{ $clienteSeduto->nome }}</div>
                                                @endif
                                            </td>
                                        @else
                                            <td class="gap"></td>
                                        @endif
                                    @endfor
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

        @endforeach

        <div class="page-break"></div>
        <h2>Tappe di raccolta</h2>
        @forelse ($tappeRaccolta as $tappa)
            <h3>{{ $tappa->nome }} - {{ $tappa->orario->format('H:i') }}</h3>
            <table class="grid">
                <thead>
                    <tr><th>Cliente</th><th>Email</th><th>Telefono</th></tr>
                </thead>
                <tbody>
                    @forelse ($tappa->clienti as $cliente)
                        <tr>
                            <td>{{ $cliente->cognome }} {{ $cliente->nome }}</td>
                            <td>{{ $cliente->email ?: '-' }}</td>
                            <td>{{ $cliente->telefono ?: '-' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="muted">Nessun cliente assegnato a questa tappa.</td></tr>
                    @endforelse
                </tbody>
            </table>
        @empty
            <p class="muted">Nessuna tappa creata.</p>
        @endforelse
    @endif
</body>
</html>
