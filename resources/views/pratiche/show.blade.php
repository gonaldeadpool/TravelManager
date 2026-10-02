<x-app-layout>
    <style>
        .pratica-print-only { display: none; }

        @page {
            size: A4;
            margin: 14mm 15mm 16mm;
        }

        @media print {
            html, body, .min-h-screen, main { background: #ffffff !important; }
            body { color: #17202a !important; font-family: Arial, sans-serif; font-size: 10pt; }
            nav, .min-h-screen > header, .pratica-screen-only { display: none !important; }
            .pratica-print-document { max-width: none !important; gap: 8px !important; padding: 0 !important; }
            .pratica-print-header { display: flex !important; }
            .pratica-print-only { display: block !important; }
            .pratica-print-section { break-inside: avoid; border: 1px solid #aeb8c2 !important; border-radius: 0 !important; margin: 0; padding: 10px 12px !important; box-shadow: none !important; }
            .pratica-print-section h3 { margin-bottom: 8px !important; border-bottom: 1px solid #d5dbe1; padding-bottom: 5px; color: #17365d; font-size: 10pt; text-transform: uppercase; }
            .pratica-trip-data { display: grid !important; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 8px 16px !important; }
            .pratica-trip-data > div { break-inside: avoid; }
            .pratica-screen-table { display: none !important; }
            .pratica-print-participants { display: grid !important; gap: 0; }
            .pratica-print-participant { display: grid; grid-template-columns: minmax(0, 1.7fr) minmax(0, 0.9fr) minmax(0, 1fr) minmax(0, 1fr); gap: 8px; border-bottom: 1px solid #c8d0d8; padding: 7px 4px; font-size: 9pt; }
            .pratica-financial-list { display: grid; grid-template-columns: minmax(0, 1fr); }
            .pratica-financial-row { display: grid; grid-template-columns: 1fr auto; gap: 16px; border-bottom: 1px solid #d5dbe1; padding: 6px 2px; }
            .pratica-financial-row:last-child { border-bottom: 0; }
            .pratica-financial-total { border-top: 1px solid #17365d; margin-top: 3px; padding-top: 9px; font-weight: 700; }
            .pratica-signatures { break-inside: avoid; margin-top: 12mm; }
            .pratica-signatures.pratica-print-only { display: grid !important; grid-template-columns: 1fr 1fr; }
            .pratica-signature-line { min-height: 25mm; border-bottom: 1px solid #374151; }
            .pratica-stamp-area { min-height: 34mm; border: 1px solid #7b8794; }
            a { color: inherit !important; text-decoration: none !important; }
        }
    </style>

    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div class="pratica-screen-only">
                <p class="text-sm text-gray-500">Riepilogo pratica</p>
                <h2 class="text-xl font-semibold leading-tight text-gray-800">Pratica #{{ $pratica->id }}</h2>
            </div>
            <div class="pratica-screen-only flex items-center gap-2">
                <a href="{{ route('pratiche.riepilogo.pdf', $pratica) }}" target="_blank" rel="noopener" title="Apri PDF per stampa" aria-label="Apri PDF per stampa" class="inline-flex h-9 w-9 items-center justify-center rounded border text-gray-700 hover:bg-gray-100">
                    <svg aria-hidden="true" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
                </a>
                <a href="{{ route('pratiche.riepilogo.pdf.download', $pratica) }}" title="Scarica PDF riepilogo" aria-label="Scarica PDF riepilogo" class="inline-flex h-9 w-9 items-center justify-center rounded border text-gray-700 hover:bg-gray-100">
                    <svg aria-hidden="true" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                </a>
                <button type="button" onclick="document.getElementById('email-pratica-dialog-{{ $pratica->id }}').showModal()" title="Invia via email" aria-label="Invia via email" class="inline-flex h-9 w-9 items-center justify-center rounded border text-gray-700 hover:bg-gray-100">
                    <svg aria-hidden="true" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/></svg>
                </button>
                <a href="{{ route('pratiche.edit', $pratica) }}" class="rounded border px-4 py-2 text-sm text-gray-700">Modifica pratica</a>
                <a href="{{ route('pratiche.index') }}" class="rounded border px-4 py-2 text-sm text-gray-700">Torna alle pratiche</a>
            </div>
        </div>
    </x-slot>

    @if (session('emailSuccess'))
        <div class="pratica-screen-only mx-auto mt-6 max-w-6xl rounded border border-green-300 bg-green-50 px-4 py-3 text-green-800">{{ session('emailSuccess') }}</div>
    @endif
    @if (session('emailError'))
        <div class="pratica-screen-only mx-auto mt-6 max-w-6xl rounded border border-red-300 bg-red-50 px-4 py-3 text-red-800">{{ session('emailError') }}</div>
    @endif

    @include('pratiche._email-dialog', ['pratica' => $pratica])

    <div class="pratica-print-document mx-auto max-w-6xl space-y-6 p-6">
        <div class="pratica-print-header hidden items-center justify-between border-b-2 border-gray-700 pb-4">
            <img src="{{ asset('logo-europolo.png') }}" alt="Logo Europolo" class="h-16 w-auto object-contain">
            <div class="text-right">
                <p class="text-sm font-semibold">Pratica n. {{ $pratica->id }}</p>
                <p class="mt-1 text-sm">Data emissione: {{ now()->format('d/m/Y') }}</p>
            </div>
        </div>

        <p class="hidden text-sm leading-relaxed print:block">Il presente documento riepiloga i dati del viaggio, i partecipanti e gli importi registrati nella pratica indicata.</p>

        <section class="pratica-print-section rounded bg-white p-6 shadow">
            <h3 class="mb-4 text-lg font-semibold">Destinazione viaggio</h3>
            <dl class="pratica-trip-data grid grid-cols-1 gap-4 text-sm md:grid-cols-2 lg:grid-cols-3">
                <div><dt class="text-gray-500">Nome viaggio</dt><dd class="mt-1 font-medium">{{ $pratica->viaggio->nome }}</dd></div>
                <div><dt class="text-gray-500">Tipologia</dt><dd class="mt-1 font-medium">{{ ucfirst($pratica->viaggio->tipologia) }}</dd></div>
                <div><dt class="text-gray-500">Destinazione</dt><dd class="mt-1 font-medium">{{ $pratica->viaggio->destinazione }}</dd></div>
                <div><dt class="text-gray-500">Partenza</dt><dd class="mt-1 font-medium">{{ $pratica->viaggio->data_partenza?->format('d/m/Y') ?? '-' }}</dd></div>
                <div><dt class="text-gray-500">Rientro</dt><dd class="mt-1 font-medium">{{ $pratica->viaggio->data_rientro?->format('d/m/Y') ?? '-' }}</dd></div>
                @if ($pratica->viaggio->tipologia === 'crociera')
                    <div><dt class="text-gray-500">Cabina</dt><dd class="mt-1 font-medium">{{ $pratica->cabina ? ucfirst(str_replace('_', ' ', $pratica->cabina)) : '-' }}</dd></div>
                @endif
                <div><dt class="text-gray-500">Data acconto</dt><dd class="mt-1 font-medium">{{ $pratica->viaggio->data_acconto?->format('d/m/Y') ?? '-' }}</dd></div>
                <div><dt class="text-gray-500">Data saldo</dt><dd class="mt-1 font-medium">{{ $pratica->viaggio->data_saldo?->format('d/m/Y') ?? '-' }}</dd></div>
            </dl>
        </section>

        <section class="pratica-print-section rounded bg-white p-6 shadow">
            <h3 class="mb-4 text-lg font-semibold">Partecipanti</h3>
            <div class="pratica-print-only pratica-print-participants">
                @forelse ($pratica->clienti as $cliente)
                    <div class="pratica-print-participant">
                        <span class="font-medium">{{ $cliente->cognome }} {{ $cliente->nome }}</span>
                        <span>{{ $cliente->data_nascita?->format('d/m/Y') ?? '-' }}</span>
                        <span>{{ $cliente->pivot->gratuito ? 'Gratuito' : ($cliente->pivot->ridotto ? 'Ridotto' : 'Intero') }}</span>
                        <span class="text-right whitespace-nowrap">{{ number_format((float) $cliente->quota_pratica, 2, ',', '.') }} EUR</span>
                    </div>
                @empty
                    <p class="py-2 text-sm">Nessun partecipante associato.</p>
                @endforelse
            </div>
            <div class="pratica-screen-table overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50 text-left text-gray-600">
                        <tr><th class="px-3 py-2">Cliente</th><th class="px-3 py-2">Codice fiscale</th><th class="px-3 py-2">Email</th><th class="px-3 py-2">Telefono</th><th class="px-3 py-2">Quota</th><th class="px-3 py-2">Posto</th></tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse ($pratica->clienti as $cliente)
                            <tr>
                                <td class="px-3 py-3 font-medium">{{ $cliente->cognome }} {{ $cliente->nome }}<div class="mt-1 text-xs text-gray-500">{{ $cliente->pivot->gratuito ? 'Gratuito' : ($cliente->pivot->ridotto ? 'Ridotto' : 'Intero') }}</div></td>
                                <td class="px-3 py-3">{{ $cliente->codice_fiscale ?: '-' }}</td>
                                <td class="px-3 py-3">{{ $cliente->email ?: '-' }}</td>
                                <td class="px-3 py-3">{{ $cliente->cellulare ?: ($cliente->telefono ?: '-') }}</td>
                                <td class="px-3 py-3 whitespace-nowrap">{{ number_format((float) $cliente->quota_pratica, 2, ',', '.') }} EUR</td>
                                <td class="px-3 py-3">{{ $cliente->pivot->posto ? (($cliente->pivot->posto_bus !== null ? 'Bus ' . ($cliente->pivot->posto_bus + 1) . ' - ' : '') . 'Posto ' . $cliente->pivot->posto) : '-' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-3 py-4 text-center text-gray-500">Nessun cliente associato.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section class="pratica-print-section rounded bg-white p-6 shadow">
            <h3 class="mb-4 text-lg font-semibold">Riepilogo</h3>
            <dl class="pratica-screen-only divide-y">
                <div class="flex justify-between gap-4 border-b py-2"><dt class="text-gray-600">Totale quote</dt><dd class="text-right font-medium">{{ number_format($pratica->totale_quote, 2, ',', '.') }} EUR</dd></div>
                <div class="flex justify-between gap-4 border-b py-2"><dt class="text-gray-600">Assicurazione annullamento</dt><dd class="text-right font-medium">{{ number_format($pratica->assicurazione_annullamento, 2, ',', '.') }} EUR</dd></div>
                <div class="flex justify-between gap-4 border-b py-2"><dt class="text-gray-600">Supplemento singola</dt><dd class="text-right font-medium">{{ number_format($pratica->supplemento_singola, 2, ',', '.') }} EUR</dd></div>
                <div class="flex justify-between gap-4 border-b py-2"><dt class="text-gray-600">Sconto</dt><dd class="text-right font-medium">{{ number_format($pratica->sconto, 2, ',', '.') }} EUR</dd></div>
                <div class="flex justify-between gap-4 border-b py-2"><dt class="text-gray-600">Acconto versato</dt><dd class="text-right font-medium">{{ number_format($pratica->acconto, 2, ',', '.') }} EUR</dd></div>
                <div class="flex justify-between gap-4 border-b py-2"><dt class="text-gray-600">Saldo versato</dt><dd class="text-right font-medium">{{ number_format($pratica->saldo, 2, ',', '.') }} EUR</dd></div>
                <div class="flex justify-between gap-4 border-b py-2 font-semibold"><dt>Totale</dt><dd class="text-right">{{ number_format($pratica->totale, 2, ',', '.') }} EUR</dd></div>
                <div class="flex justify-between gap-4 border-b py-2 font-semibold"><dt>Da pagare</dt><dd class="text-right">{{ number_format($pratica->totale - $pratica->acconto - $pratica->saldo, 2, ',', '.') }} EUR</dd></div>
            </dl>
            <div class="pratica-print-only pratica-financial-list">
                <div class="pratica-financial-row"><span>Totale quote</span><span>{{ number_format($pratica->totale_quote, 2, ',', '.') }} EUR</span></div>
                <div class="pratica-financial-row"><span>Assicurazione annullamento</span><span>{{ number_format($pratica->assicurazione_annullamento, 2, ',', '.') }} EUR</span></div>
                <div class="pratica-financial-row"><span>Supplemento singola</span><span>{{ number_format($pratica->supplemento_singola, 2, ',', '.') }} EUR</span></div>
                @if ((float) $pratica->sconto > 0)
                    <div class="pratica-financial-row"><span>Sconto</span><span>{{ number_format($pratica->sconto, 2, ',', '.') }} EUR</span></div>
                @endif
                <div class="pratica-financial-row"><span>Acconto versato</span><span>{{ number_format($pratica->acconto, 2, ',', '.') }} EUR</span></div>
                <div class="pratica-financial-row"><span>Saldo versato</span><span>{{ number_format($pratica->saldo, 2, ',', '.') }} EUR</span></div>
                <div class="pratica-financial-row pratica-financial-total"><span>Totale</span><span>{{ number_format($pratica->totale, 2, ',', '.') }} EUR</span></div>
                <div class="pratica-financial-row pratica-financial-total"><span>Da pagare</span><span>{{ number_format($pratica->totale - $pratica->acconto - $pratica->saldo, 2, ',', '.') }} EUR</span></div>
            </div>
        </section>

        <section class="pratica-print-section rounded bg-white p-6 shadow">
            <h3 class="mb-3 text-lg font-semibold">Note e richieste</h3>
            <p class="whitespace-pre-line text-sm">{{ $pratica->note ?: 'Nessuna nota.' }}</p>
        </section>

        <section class="pratica-screen-only pratica-print-section rounded bg-white p-6 shadow">
            <h3 class="mb-3 text-lg font-semibold">Documenti allegati</h3>
            @forelse ($pratica->documenti as $documento)
                <div class="flex items-center justify-between gap-4 border-b py-2 text-sm last:border-0">
                    <span>{{ $documento->nome_originale }}</span>
                    <a href="{{ route('pratiche.documenti.download', [$pratica, $documento]) }}" class="pratica-screen-only text-blue-700 hover:underline">Apri documento</a>
                </div>
            @empty
                <p class="text-sm text-gray-500">Nessun documento allegato.</p>
            @endforelse
        </section>

        <section class="pratica-print-only pratica-signatures grid grid-cols-1 gap-8 pt-4 sm:grid-cols-2">
            <div>
                <p class="mb-2 text-sm font-medium">Luogo e data</p>
                <div class="pratica-signature-line"></div>
            </div>
            <div>
                <p class="mb-2 text-sm font-medium">Firma del cliente</p>
                <div class="pratica-signature-line"></div>
            </div>
            <div class="sm:col-span-2">
                <p class="mb-2 text-sm font-medium">Timbro e firma dell'agenzia</p>
                <div class="pratica-stamp-area"></div>
            </div>
        </section>

        <p class="pratica-screen-only text-right text-xs text-gray-500">Creata il {{ $pratica->created_at?->format('d/m/Y H:i') ?? '-' }} · Aggiornata il {{ $pratica->updated_at?->format('d/m/Y H:i') ?? '-' }}</p>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('dialog[data-open-on-load]').forEach((dialog) => dialog.showModal());
            @if (request()->boolean('stampa'))
                window.print();
            @endif
        });
    </script>
</x-app-layout>
