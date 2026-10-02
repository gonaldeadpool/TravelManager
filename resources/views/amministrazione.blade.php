<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Amministrazione</h2>
    </x-slot>

    <div class="p-6">
        @if (session('success'))
            <div class="mb-4 rounded border border-green-400 bg-green-100 px-4 py-3 text-green-700">
                {{ session('success') }}
            </div>
        @endif

        @if (session('mailError'))
            <div class="mb-4 rounded border border-red-400 bg-red-100 px-4 py-3 text-red-700">
                {{ session('mailError') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-4 rounded border border-red-400 bg-red-100 px-4 py-3 text-red-700">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="mx-auto max-w-4xl" x-data="{ tab: @js(session('mailError') || $errors->has('smtp_host') || $errors->has('smtp_port') || $errors->has('smtp_scheme') || $errors->has('smtp_username') || $errors->has('smtp_password') || $errors->has('from_address') || $errors->has('from_name') ? 'posta' : 'configurazione') }">
            <div class="mb-6 border-b border-gray-200">
                <nav class="flex gap-6" aria-label="Sezioni amministrazione">
                    <button type="button" @click="tab = 'configurazione'" :class="tab === 'configurazione' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700'" class="border-b-2 px-1 pb-3 text-sm font-semibold">Setup</button>
                    <button type="button" @click="tab = 'tecnica'" :class="tab === 'tecnica' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700'" class="border-b-2 px-1 pb-3 text-sm font-semibold">Path file esterni</button>
                    @if (Auth::user()->isAdmin())
                        <button type="button" @click="tab = 'posta'" :class="tab === 'posta' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700'" class="border-b-2 px-1 pb-3 text-sm font-semibold">Posta</button>
                    @endif
                </nav>
            </div>

            <form method="POST" action="{{ route('amministrazione.update') }}" class="space-y-6">
                @csrf
                @method('PUT')

                <div x-show="tab === 'tecnica'" x-cloak class="rounded bg-white p-6 shadow">
                    <h4 class="mb-4 font-semibold">Percorsi di archiviazione</h4>

                    <div class="space-y-4">
                        <div>
                            <label for="locandine_path" class="mb-1 block">Cartella locandine</label>
                            <input id="locandine_path" type="text" name="locandine_path" value="{{ old('locandine_path', $locandinePath) }}" required class="w-full rounded border p-2">
                            <!--<p class="mt-1 text-sm text-gray-500">Percorso locale assoluto o relativo alla radice del progetto.</p>-->
                        </div>

                        <div>
                            <label for="documenti_path" class="mb-1 block">Cartella documenti cliente</label>
                            <input id="documenti_path" type="text" name="documenti_path" value="{{ old('documenti_path', $documentiPath) }}" required class="w-full rounded border p-2">
                            <!--<p class="mt-1 text-sm text-gray-500">I documenti saranno accessibili solo agli utenti autenticati.</p>-->
                        </div>

                        <div>
                            <label for="documenti_pratiche_path" class="mb-1 block">Cartella documenti pratiche</label>
                            <input id="documenti_pratiche_path" type="text" name="documenti_pratiche_path" value="{{ old('documenti_pratiche_path', $documentiPratichePath) }}" required class="w-full rounded border p-2">
                            <!--<p class="mt-1 text-sm text-gray-500">Gli allegati delle pratiche saranno accessibili solo agli utenti autenticati.</p>-->
                        </div>
                    </div>
                </div>

                <div x-show="tab === 'configurazione'" x-cloak class="rounded bg-white p-6 shadow">
                    <h4 class="mb-4 font-semibold">Scadenza documenti cliente</h4>
                    <p class="mb-4 text-sm text-gray-500">Indica con quanti giorni di anticipo evidenziare i documenti in scadenza nell'elenco clienti.</p>

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        @foreach ([
                            'carta_identita' => "Carta d'identità",
                            'passaporto' => 'Passaporto',
                            'patente' => 'Patente',
                            'altro' => 'Altri documenti',
                        ] as $tipo => $label)
                            <div>
                                <label for="scadenza_{{ $tipo }}" class="mb-1 block">{{ $label }}</label>
                                <div class="relative">
                                    <input id="scadenza_{{ $tipo }}" type="number" name="scadenza_{{ $tipo }}" min="0" max="3650" required value="{{ old('scadenza_' . $tipo, $scadenze[$tipo]) }}" class="w-full rounded border p-2 pr-16">
                                    <span class="absolute right-3 top-2 text-gray-500">giorni</span>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-8 border-t border-gray-200 pt-6">
                        <h4 class="mb-1 font-semibold">Pagamenti</h4>
                        <p class="mb-4 text-sm text-gray-500">Indica quanti giorni prima di indacare acconto o saldo in scadenza.</p>

                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            @foreach ([
                                'acconto' => 'Giorni scadenza acconto',
                                'saldo' => 'Giorni scadenza saldo',
                            ] as $tipo => $label)
                                <div>
                                    <label for="scadenza_{{ $tipo }}" class="mb-1 block">{{ $label }}</label>
                                    <div class="relative">
                                        <input id="scadenza_{{ $tipo }}" type="number" name="scadenza_{{ $tipo }}" min="0" max="3650" required value="{{ old('scadenza_' . $tipo, $scadenzePagamenti[$tipo]) }}" class="w-full rounded border p-2 pr-16">
                                        <span class="absolute right-3 top-2 text-gray-500">giorni</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <button x-show="tab !== 'posta'" type="submit" class="rounded bg-blue-600 px-4 py-2 text-white">Salva configurazione</button>
            </form>

            @if (Auth::user()->isAdmin())
                <div x-show="tab === 'posta'" x-cloak class="space-y-6">
                    <form method="POST" action="{{ route('amministrazione.mail.update') }}" class="space-y-6 rounded bg-white p-6 shadow">
                        @csrf
                        @method('PUT')
                        <div>
                            <h4 class="font-semibold">Server SMTP</h4>
                            <p class="mt-1 text-sm text-gray-500">La password viene cifrata prima di essere salvata e non viene mai mostrata.</p>
                        </div>

                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div>
                                <label for="smtp_host" class="mb-1 block">Server SMTP</label>
                                <input id="smtp_host" name="smtp_host" value="{{ old('smtp_host', $mailSettings['smtp_host']) }}" required autocomplete="off" class="w-full rounded border p-2">
                            </div>
                            <div>
                                <label for="smtp_port" class="mb-1 block">Porta</label>
                                <input id="smtp_port" type="number" name="smtp_port" min="1" max="65535" value="{{ old('smtp_port', $mailSettings['smtp_port']) }}" required class="w-full rounded border p-2">
                            </div>
                            <div>
                                <label for="smtp_scheme" class="mb-1 block">Sicurezza</label>
                                <select id="smtp_scheme" name="smtp_scheme" class="w-full rounded border p-2">
                                    <option value="smtp" @selected(old('smtp_scheme', $mailSettings['smtp_scheme']) === 'smtp')>STARTTLS</option>
                                    <option value="smtps" @selected(old('smtp_scheme', $mailSettings['smtp_scheme']) === 'smtps')>SSL/TLS</option>
                                </select>
                            </div>
                            <div>
                                <label for="smtp_username" class="mb-1 block">Nome utente SMTP</label>
                                <input id="smtp_username" name="smtp_username" value="{{ old('smtp_username', $mailSettings['smtp_username']) }}" autocomplete="username" class="w-full rounded border p-2">
                            </div>
                            <div>
                                <label for="smtp_password" class="mb-1 block">Password SMTP</label>
                                <input id="smtp_password" type="password" name="smtp_password" autocomplete="new-password" class="w-full rounded border p-2">
                                @if ($mailSettings['smtp_password_configured'])
                                    <p class="mt-1 text-xs text-gray-500">Password già configurata; lascia vuoto per mantenerla.</p>
                                    <label class="mt-2 inline-flex items-center gap-2 text-sm"><input type="checkbox" name="remove_smtp_password" value="1" class="rounded border-gray-300"> Rimuovi password salvata</label>
                                @endif
                            </div>
                        </div>

                        <div class="border-t border-gray-200 pt-5">
                            <h4 class="mb-4 font-semibold">Mittente</h4>
                            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                <div>
                                    <label for="from_address" class="mb-1 block">Indirizzo mittente</label>
                                    <input id="from_address" type="email" name="from_address" value="{{ old('from_address', $mailSettings['from_address']) }}" required autocomplete="email" class="w-full rounded border p-2">
                                </div>
                                <div>
                                    <label for="from_name" class="mb-1 block">Nome mittente</label>
                                    <input id="from_name" name="from_name" value="{{ old('from_name', $mailSettings['from_name']) }}" required class="w-full rounded border p-2">
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="rounded bg-blue-600 px-4 py-2 text-white">Salva posta</button>
                    </form>

                    <form method="POST" action="{{ route('amministrazione.mail.test') }}" class="flex flex-wrap items-center justify-between gap-4 rounded bg-white p-6 shadow">
                        @csrf
                        <p class="text-sm text-gray-700">Invia una mail di prova all'indirizzo del tuo account.</p>
                        <button type="submit" class="rounded border px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Invia test</button>
                    </form>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
