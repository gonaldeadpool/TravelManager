# Mappa del progetto

## Flusso architetturale

Richiesta browser → route nominata in `routes/web.php` → middleware `auth` e middleware globale `MenuPermission` → controller → modelli Eloquent/query → vista Blade o risposta JSON/PDF/email. JavaScript interattivo condiviso vive in `resources/js/app.js`; non è un frontend SPA.

## Dove intervenire

| Area | File e directory principali | Responsabilità |
|---|---|---|
| Rotte e protezione | `routes/web.php`, `routes/auth.php`, `bootstrap/app.php`, `app/Http/Middleware/MenuPermission.php` | Rotte web nominate, autenticazione, verifica email per dashboard, controllo permessi menu |
| Viaggi | `app/Http/Controllers/ViaggioController.php`, `app/Models/Viaggio.php`, `resources/views/viaggi/`, `resources/views/viaggi*.blade.php` | CRUD, ricerca/filtri, tariffe/cabine, trasporti, locandine, posti bus, tappe, riepilogo e PDF |
| Clienti | `app/Http/Controllers/ClienteController.php`, `app/Models/Cliente.php`, `app/Models/ClienteDocumento.php`, `resources/views/clienti/`, `resources/views/clienti*.blade.php` | Anagrafica, documenti/scadenze, ricerca, riepilogo ed email |
| Pratiche | `app/Http/Controllers/PraticaController.php`, `app/Models/Pratica.php`, `app/Models/PraticaDocumento.php`, `resources/views/pratiche/`, `resources/views/pratiche.blade.php` | Creazione a più passaggi, partecipanti e tariffe, calcolo importi, pagamenti, allegati, PDF/email |
| Dashboard | `app/Http/Controllers/DashboardController.php`, `resources/views/dashboard.blade.php` | Widget configurabili, conteggi e avvisi su viaggi, clienti, pratiche e capacità |
| Calendario | `app/Http/Controllers/CalendarioController.php`, `resources/views/calendario.blade.php`, parte calendario di `resources/js/app.js` | Eventi viaggio FullCalendar, intervalli data e collegamento a dettaglio/creazione viaggio |
| Utenti e profilo | `app/Http/Controllers/UserManagementController.php`, `app/Http/Controllers/ProfileController.php`, `app/Models/User.php`, `resources/views/utenti/`, `resources/views/profile/` | Gestione utenti/ruoli, preferenze menu, profilo, avatar, password e 2FA |
| Amministrazione | `app/Http/Controllers/AmministrazioneController.php`, `app/Support/MailSettings.php`, `app/Support/LocalStoragePaths.php`, `app/Models/AppSetting.php` | Percorsi storage, soglie scadenze e impostazioni SMTP |
| Altre aree | `app/Http/Controllers/LogController.php`, `app/Logging/ContestoUtenteLog.php`, `app/Mail/`, `resources/views/log/`, `resources/views/emails/` | Consultazione log, contesto utente, messaggi email |
| Schema/dati di test | `database/migrations/`, `database/factories/`, `database/seeders/` | Schema evolutivo, factory utente e seeder |
| Asset e configurazione | `resources/css/app.css`, `resources/js/app.js`, `vite.config.js`, `tailwind.config.js`, `postcss.config.js` | Bundle UI e librerie frontend |
| Test | `tests/Feature/`, `tests/Unit/`, `phpunit.xml` | Test HTTP/integrati e unitari; SQLite in-memory con `RefreshDatabase` |

## Rotte per area

`routes/web.php` contiene route esplicite per clienti, ricerca, documenti e email; resource route per viaggi; route dedicate per tappe/posti; resource route pratica con selezione partecipanti, allegati, riepiloghi e email; e route per dashboard, calendario, amministrazione, utenti e log. Per nomi e parametri aggiornati consultare direttamente il file, invece di duplicare qui l'elenco.

La route `/` reindirizza al login. Tutte le route applicative definite nel gruppo web sono sottoposte a `MenuPermission`; dashboard richiede inoltre `verified`. Le route di autenticazione sono in `routes/auth.php`.

## Struttura dati, a colpo d'occhio

- `viaggi`: prodotto/partenza e opzioni logistiche e tariffarie.
- `clienti`: anagrafica; `cliente_documenti`: documenti personali.
- `pratiche`: prenotazione per un viaggio; `cliente_pratica`: partecipanti e campi propri della partecipazione.
- `pratica_documenti`: allegati della pratica.
- `viaggio_tappe_raccolta` e `viaggio_tappa_cliente`: tappe e associazione partecipante-tappa.
- `users`: account, ruolo, permessi e preferenze dashboard; `app_settings`: configurazioni chiave/valore.

Per cardinalità, chiavi e significato delle pivot vedere [DOMAIN_MODEL.md](DOMAIN_MODEL.md). Per i vincoli operativi vedere [BUSINESS_RULES.md](BUSINESS_RULES.md).

## Attenzioni durante l'esplorazione

- Le migration sono la fonte dello schema; alcune campi sono stati introdotti da migration successive e alcuni sono stati rimossi. Non basarsi esclusivamente sui file `create_*`.
- Le viste principali non seguono tutte una struttura annidata: per esempio `viaggi` usa anche `resources/views/viaggi.blade.php`, mentre i partial sono nella cartella `resources/views/viaggi/`.
- Una parte significativa delle regole è nei controller, non nei model accessor/policy. Ricerca e dashboard possono calcolare o aggregare dati separatamente: controllare le superfici coinvolte quando si cambia una regola.
- La configurazione dinamica è in `app_settings`; password SMTP cifrata con `Crypt` in `MailSettings`.
- I file sotto `storage/`, `vendor/` e `node_modules/` sono dati/runtime o dipendenze, non sorgenti applicative da esaminare per una modifica ordinaria.
