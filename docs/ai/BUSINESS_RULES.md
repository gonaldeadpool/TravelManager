# Regole di business

Le regole qui sotto sono quelle riscontrate nel codice corrente, non una specifica separata. Le fonti primarie sono `ViaggioController`, `PraticaController`, `ClienteController`, `DashboardController`, `AmministrazioneController`, `MenuPermission` e le migration. Aggiornare questo documento quando cambia il comportamento.

## Viaggi e tariffe

- Tipologie ammesse: `viaggio`, `soggiorno`, `tour`, `crociera`.
- Nome, tipologia, destinazione e date sono richiesti; il rientro deve essere lo stesso giorno o successivo alla partenza.
- Per tipologie diverse da crociera è richiesta la quota base. Per crociere il prezzo si configura per cabina (`interna`, `vista_mare`, `balcone`); una pratica crociera deve indicare una cabina presente nei prezzi del viaggio.
- Numero minimo partecipanti richiesto e almeno 1; massimo facoltativo ma non inferiore al minimo. L'`importo_minimo_acconto` è memorizzato nel viaggio; non assumere che venga applicato automaticamente ai pagamenti della pratica senza verificare il flusso corrente.
- I viaggi sono considerati futuri/attivi quando `data_partenza >= oggi`. Gli elenchi viaggi e pratiche escludono normalmente i passati; i filtri e l'eventuale viaggio selezionato modificano questo comportamento. Dashboard capacità usa solo viaggi con partenza non passata.
- Trasporti sono dati strutturati JSON; tra i tipi validati: bus, aereo e treno. Un bus può avere posti configurati.

## Partecipanti e disponibilità

- Creando una pratica, il server richiede almeno un cliente; ID duplicati non sono ammessi. Per un partecipante, `gratuito` e `ridotto` non possono essere contemporaneamente veri.
- Le schermate di selezione clienti filtrano clienti già associati ad altre pratiche dello stesso viaggio, mantenendo quelli già selezionati nella pratica corrente. Il vincolo non è una chiave univoca nel database: non assumere che il solo filtro UI sia una garanzia per ogni endpoint/percorso.
- La capacità mostrata in dashboard conta clienti distinti per viaggio (`COUNT(DISTINCT cliente_id)`), non il numero di pratiche. Avviso di minimo in scadenza: entro 30 giorni dalla partenza (inclusi), iscritti sotto il minimo. Quasi pieno: almeno 80% del massimo ma meno del massimo; completo: iscritti almeno al massimo.
- L'assegnazione tappa richiede tappa appartenente al viaggio e cliente partecipante. L'assegnazione sostituisce ogni tappa precedente del cliente per quel viaggio; la pivot consente al massimo una tappa per cliente/viaggio.
- UI «Tappe Bus» (ex «Tappe di raccolta»): desktop con drag & drop dalla sidebar partecipanti; mobile senza sidebar: tap sulla tappa apre un menu a tendina con i clienti disponibili, doppio tap sul nome lo rimuove dalla tappa. Su mobile l'header del viaggio ha titolo su una riga e azioni «Modifica»/«Torna ai viaggi» come sole icone.
- L'assegnazione posto bus richiede un bus configurato e un cliente partecipante al viaggio. Conflitti sullo stesso indice bus/posto fra partecipanti dello stesso viaggio vengono rimossi prima dell'assegnazione. L'assegnazione è replicata sulle pratiche in cui il cliente partecipa.
- Nella piantina bus desktop si può assegnare trascinando oppure selezionando un cliente con un click e un posto libero con il successivo. Su mobile la lista laterale è nascosta: toccando un posto si apre un menu con i clienti senza posto; se il posto è occupato, il menu mostra in cima «Libera posto» e consente di sostituire l'occupante con un cliente disponibile. Il doppio click su un posto occupato lo libera anche su desktop e riporta il cliente tra quelli disponibili.

## Calcolo importi pratica

Il server non usa `totale_quote` e `totale` provenienti dal form come fonte di verità: `praticaData()` li inizializza a zero e `ricalcolaTotale()` li ricostruisce.

1. Quota cliente gratuita = `0`.
2. Altrimenti, quota base = prezzo della cabina della pratica (crociera; se non trovato, fallback a `viaggio.prezzo`) oppure `viaggio.prezzo` per gli altri tipi.
3. Se ridotto: per crociera con `quota_fissa` definita si usa la quota fissa; altrimenti, se `quota_ridotto` è definita si usa la quota ridotta. Se nessuna tariffa ridotta è configurata, resta la quota base.
4. `totale_quote` = somma delle quote individuali.
5. `totale` = `max(0, totale_quote + assicurazione_annullamento + supplemento_singola + supplemento_post_bus_riservato - sconto)`.

Gli importi ammessi sono numerici e non negativi; i valori sono somme monetarie, non percentuali nel calcolo osservato. Per ogni variazione ai partecipanti, aggiornare/ricalcolare il totale: creazione, aggiunta e rimozione cliente sono punti da mantenere coerenti.

## Acconti, saldi e scadenze

- `Pratica` conserva importi e date proprie di acconto/saldo. Tuttavia la classificazione attuale dello stato usa le scadenze del `Viaggio`: `data_acconto` oppure `data_partenza` come fallback; `data_saldo` oppure `data_partenza` come fallback. Non confondere questi campi di pagamento inseriti nella pratica con le date usate per le etichette di stato.
- Soglie di imminenza per acconto e saldo sono configurabili in amministrazione (0–3650 giorni); valore predefinito del codice: 30.
- Stato `saldo_versato`: saldo maggiore di zero e residuo `totale - acconto - saldo` minore o uguale a zero.
- Se acconto è zero o negativo: stato `acconto_non_versato` se mancano più giorni della soglia alla scadenza; altrimenti `acconto_non_versato_scadenza`.
- Se l'acconto è positivo ma il saldo non risulta interamente versato: `acconto_versato` se la scadenza saldo è oltre la soglia; altrimenti `saldo_non_versato_scadenza`.
- La classificazione non introduce uno stato distinto di pagamento scaduto: gli stati con suffisso `scadenza` coprono il periodo entro soglia e anche date oltre la scadenza secondo il confronto corrente.

## Documenti cliente

- Upload accettato: PDF/JPEG/JPG, massimo 10 MiB; metadati documento includono tipo, numero e scadenza facoltativi.
- Tipo consentito: `carta_identita`, `passaporto`, `patente`, `altro`.
- Soglia di scadenza configurabile per tipo; default 30 giorni.
- `in_regola` richiede almeno un documento e nessun documento scaduto o entro la propria soglia.
- `in_scadenza` richiede almeno un documento non scaduto entro la soglia del relativo tipo.
- In elenco e dashboard, assenza di documenti è trattata come `scaduti`; la presenza di almeno un documento già scaduto prevale rispetto ad altri documenti validi/in scadenza.
- I documenti sono conservati in storage privato e scaricati tramite route autenticata che verifica l'appartenenza del documento al cliente.

## Autorizzazioni

- Le route web applicative richiedono autenticazione. `MenuPermission` è aggiunto a tutto il middleware `web`; per un operatore, ogni route nominata in una famiglia menu richiede la corrispondente voce in `menu_permissions`. L'admin bypassa il controllo menu.
- Dashboard richiede inoltre email verificata.
- Le azioni di configurazione SMTP e test SMTP verificano esplicitamente `isAdmin()`, oltre all'autenticazione.
- Permesso menu non equivale a un controllo di ruolo per ogni singolo record: non presumere l'esistenza di autorizzazioni per proprietario/tenant.

## Email, PDF e file

- Gli invii email cliente/pratica accettano indirizzi multipli separati da virgola, punto e virgola o spazi/a capo e deduplicano senza distinzione maiuscole/minuscole; massimo 50 destinatari per invio.
- Nell'email riepilogo pratica, indirizzi selezionati fra quelli dei clienti devono appartenere ai partecipanti; gli indirizzi manuali sono validati separatamente.
- La password SMTP è cifrata in `AppSetting`; non loggare né esporre valori segreti. Errore di configurazione/invio è riportato all'utente e registrato nei log.
- Le locandine usano storage configurabile (fallback predefinito in `storage/app/public/locandine`); documenti cliente e pratica hanno cartelle private configurabili. Eliminare un record con allegati deve rimuovere anche i file fisici.
