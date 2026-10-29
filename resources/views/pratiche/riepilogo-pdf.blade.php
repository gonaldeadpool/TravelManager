<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <title>Riepilogo pratica {{ $pratica->id }}</title>
    <style>
        @page { margin: 9mm 10mm; }
        body { color: #17202a; font-family: DejaVu Sans, sans-serif; font-size: 9.5px; line-height: 1.35; }
        .header { border-bottom: 2px solid #17365d; margin-bottom: 12px; padding-bottom: 9px; }
        .header-table, .grid, .participants, .summary { border-collapse: collapse; width: 100%; }
        .header-table td { vertical-align: middle; }
        .logo { max-height: 50px; max-width: 155px; }
        .document-meta { color: #334155; text-align: right; }
        h1 { color: #17365d; font-size: 16px; margin: 0 0 3px; text-transform: uppercase; }
        h2 { border-bottom: 1px solid #9aa8b6; color: #17365d; font-size: 10px; margin: 12px 0 6px; padding-bottom: 3px; text-transform: uppercase; }
        .grid td, .grid th { border: 1px solid #c8d0d8; padding: 5px 6px; text-align: left; vertical-align: top; }
        .grid th { background: #edf1f5; color: #334155; font-weight: bold; width: 17%; }
        .participants td { border-bottom: 1px solid #c8d0d8; padding: 5px 5px; vertical-align: top; }
        .participant-name { font-weight: bold; width: 34%; }
        .participant-date { width: 19%; }
        .participant-type { width: 21%; }
        .participant-amount { text-align: right; white-space: nowrap; width: 26%; }
        .summary td { border-bottom: 1px solid #d5dbe1; padding: 5px 5px; }
        .summary td:last-child { text-align: right; white-space: nowrap; width: 30%; }
        .summary .total td { border-top: 1px solid #17365d; font-size: 10px; font-weight: bold; padding-top: 6px; }
        .notes { border: 1px solid #c8d0d8; min-height: 30px; padding: 6px; white-space: pre-wrap; }
        .signatures { margin-top: 18px; page-break-inside: avoid; width: 100%; }
        .signatures td { padding: 0 7px; vertical-align: top; width: 50%; }
        .signature-line { border-bottom: 1px solid #374151; height: 42px; }
        .stamp { border: 1px solid #7b8794; height: 70px; margin-top: 6px; }
        .payment-details { border: 1px solid #c8d0d8; height: 70px; margin-top: 6px; padding: 6px; }
        .payment-details div { margin-bottom: 4px; }
        .payment-details div:last-child { margin-bottom: 0; }
        .payment-iban { word-break: break-all; }
        .label { color: #475569; font-size: 8px; }
    </style>
</head>
<body>
    @php
        $logoPath = public_path('logo-europolo.png');
        $hasLogo = is_file($logoPath);
        $dataDaPagare = $pratica->totale - $pratica->acconto - $pratica->saldo;
    @endphp

    <div class="header">
        <table class="header-table">
            <tr>
                <td>@if ($hasLogo)<img src="{{ $logoPath }}" alt="Europolo" class="logo">@endif</td>
                <td class="document-meta"><strong>Pratica n. {{ $pratica->id }}</strong><br>Data emissione: {{ now()->format('d/m/Y') }}</td>
            </tr>
        </table>
    </div>

    <p>Il presente documento riepiloga i dati del viaggio, i partecipanti e gli importi registrati nella pratica.</p>

    <h2>Destinazione viaggio</h2>
    <table class="grid">
        <tr><th>Nome viaggio</th><td>{{ $pratica->viaggio->nome }}</td><th>Tipologia</th><td>{{ $pratica->viaggio->tipologia_label }}</td></tr>
        <tr><th>Destinazione</th><td colspan="{{ $pratica->viaggio->tipologia === 'crociera' ? 1 : 3 }}">{{ $pratica->viaggio->destinazione }}</td>@if ($pratica->viaggio->tipologia === 'crociera')<th>Cabina</th><td>{{ $pratica->cabina ? ucfirst(str_replace('_', ' ', $pratica->cabina)) : '-' }}</td>@endif</tr>
        <tr><th>Partenza</th><td>{{ $pratica->viaggio->data_partenza?->format('d/m/Y') ?? '-' }}</td><th>Rientro</th><td>{{ $pratica->viaggio->data_rientro?->format('d/m/Y') ?? '-' }}</td></tr>
        <tr><th>Data acconto</th><td>{{ $pratica->viaggio->data_acconto?->format('d/m/Y') ?? '-' }}</td><th>Data saldo</th><td>{{ $pratica->viaggio->data_saldo?->format('d/m/Y') ?? '-' }}</td></tr>
    </table>

    <h2>Partecipanti</h2>
    <table class="participants">
        <tbody>
            @forelse ($pratica->clienti as $cliente)
                <tr>
                    <td class="participant-name">{{ $cliente->cognome }} {{ $cliente->nome }}</td>
                    <td class="participant-date">{{ $cliente->data_nascita?->format('d/m/Y') ?? '-' }}</td>
                    <td class="participant-type">{{ $cliente->pivot->gratuito ? 'Gratuito' : ($cliente->pivot->ridotto ? 'Ridotto' : 'Intero') }}</td>
                    <td class="participant-amount">{{ number_format((float) $cliente->quota_pratica, 2, ',', '.') }} EUR</td>
                </tr>
            @empty
                <tr><td colspan="4">Nessun partecipante associato.</td></tr>
            @endforelse
        </tbody>
    </table>

    <h2>Riepilogo</h2>
    <table class="summary">
        <tbody>
            <tr><td>Totale quote</td><td>{{ number_format($pratica->totale_quote, 2, ',', '.') }} EUR</td></tr>
            <tr><td>Assicurazione annullamento</td><td>{{ number_format($pratica->assicurazione_annullamento, 2, ',', '.') }} EUR</td></tr>
            <tr><td>Supplemento singola</td><td>{{ number_format($pratica->supplemento_singola, 2, ',', '.') }} EUR</td></tr>
            <tr><td>Supplemento post bus riservato</td><td>{{ number_format($pratica->supplemento_post_bus_riservato, 2, ',', '.') }} EUR</td></tr>
            @if ((float) $pratica->sconto > 0)
                <tr><td>Sconto</td><td>{{ number_format($pratica->sconto, 2, ',', '.') }} EUR</td></tr>
            @endif
            <tr><td>Acconto versato</td><td>{{ number_format($pratica->acconto, 2, ',', '.') }} EUR</td></tr>
            <tr><td>Saldo versato</td><td>{{ number_format($pratica->saldo, 2, ',', '.') }} EUR</td></tr>
            <tr class="total"><td>Totale</td><td>{{ number_format($pratica->totale, 2, ',', '.') }} EUR</td></tr>
            <tr class="total"><td>Da pagare</td><td>{{ number_format($dataDaPagare, 2, ',', '.') }} EUR</td></tr>
        </tbody>
    </table>

    <h2>Note e richieste</h2>
    <div class="notes">{{ $pratica->note ?: 'Nessuna nota.' }}</div>

    <table class="signatures">
        <tr>
            <td><div class="label">Luogo e data</div><div class="signature-line"></div></td>
            <td><div class="label">Firma del cliente</div><div class="signature-line"></div></td>
        </tr>
        <tr>
            <td>
                <div class="label">Timbro e firma dell'agenzia</div>
                <div class="stamp"></div>
            </td>
            <td>
                <div class="label">Dati pagamento</div>
                <div class="payment-details">
                    <div><strong>Intestatario:</strong> {{ $datiPagamento['intestatario'] ?: '-' }}</div>
                    <div><strong>Banca:</strong> {{ $datiPagamento['banca'] ?: '-' }}</div>
                    <div class="payment-iban"><strong>IBAN:</strong> {{ $datiPagamento['iban'] ?: '-' }}</div>
                </div>
            </td>
        </tr>
    </table>
</body>
</html>
