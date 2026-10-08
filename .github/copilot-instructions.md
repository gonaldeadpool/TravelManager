# Istruzioni per GitHub Copilot

## Scopo del progetto

TravelManager è un'applicazione web Laravel in italiano per gestire viaggi, clienti, pratiche di prenotazione, documenti, pagamenti e attività dell'agenzia. Prima di esplorare il repository, consultare [docs/ai/QUICK_CONTEXT.md](../docs/ai/QUICK_CONTEXT.md); aprire poi soltanto le sezioni pertinenti di [docs/ai/PROJECT_MAP.md](../docs/ai/PROJECT_MAP.md), [docs/ai/DOMAIN_MODEL.md](../docs/ai/DOMAIN_MODEL.md) e [docs/ai/BUSINESS_RULES.md](../docs/ai/BUSINESS_RULES.md).

## Convenzioni e architettura

- Stack: PHP 8.3+, Laravel 13, Eloquent, Blade, Alpine.js, Tailwind CSS e Vite. Il calendario usa FullCalendar; i riepiloghi PDF sono generati con DomPDF.
- L'interfaccia e i nomi di dominio sono in italiano. Conservare lingua, terminologia e stile esistenti.
- Rotte web in `routes/web.php`; registrazione middleware web in `bootstrap/app.php`; logica applicativa prevalentemente nei controller `app/Http/Controllers/`; modelli in `app/Models/`; schema evolutivo solo tramite migration in `database/migrations/`; UI in Blade `resources/views/`; JavaScript condiviso in `resources/js/app.js`.
- Le viste aggregate per i viaggi e i clienti includono template nella radice `resources/views/`; i template condivisi dei moduli sono in sottocartelle come `resources/views/viaggi/` e `resources/views/pratiche/`.
- Prima di modificare una regola, individuare tutti i suoi punti d'uso: controller, modello, query/ordinamenti, form Blade, riepiloghi/PDF, dashboard, endpoint AJAX, migrazioni e test pertinenti. Non spostare regole fra livelli senza verificare i flussi create, edit e update.
- Il database predefinito è SQLite, ma la configurazione Laravel comprende anche MySQL/MariaDB e PostgreSQL. Evitare SQL specifico di un driver se non necessario; notare che alcuni ordinamenti usano espressioni SQL dedicate.
- Non trattare i campi JSON come strutture tipizzate dal database: per esempio `viaggi.trasporti` e `viaggi.prezzi_cabine` sono array JSON castati dal modello.

## Regole di dominio da preservare

- I tipi di viaggio ammessi sono `viaggio`, `soggiorno`, `tour`, `crociera`; la tariffa crociera dipende dalla cabina.
- Le quote della pratica sono calcolate dal server in `PraticaController`: partecipanti gratuiti/ridotti, prezzo applicabile della cabina, supplementi e sconto determinano i totali. Non fidarsi dei totali inviati dal browser.
- Le relazioni di partecipazione, le tariffe per partecipante, il posto bus e la tappa di raccolta hanno semantiche distinte. Consultare [docs/ai/BUSINESS_RULES.md](../docs/ai/BUSINESS_RULES.md) prima di cambiarle.
- Le route web sono autenticate; il middleware `MenuPermission` è aggiunto all'intero gruppo web. Gli operatori necessitano della permission del menu della route; l'amministrazione SMTP richiede inoltre il ruolo admin.
- I percorsi dei file sono configurabili via `AppSetting`; i documenti cliente/pratica sono privati e serviti tramite route autenticate. Non esporli come URL pubblici.
- La cancellazione di viaggi/clienti/pratiche interagisce con chiavi esterne e file su disco: verificare la pulizia di storage e i vincoli `restrictOnDelete` / `cascadeOnDelete`.

## Modifiche e validazione

- Fare modifiche mirate e coerenti con le convenzioni Laravel già presenti. Aggiornare le migration, non lo schema manualmente; non introdurre codice applicativo quando la richiesta è documentale.
- Aggiungere o aggiornare test Feature/Unit per cambiamenti di comportamento. I test usano PHPUnit, SQLite in-memory e `RefreshDatabase`.
- Validazione standard: `composer test` (o `php artisan test`) e, se si toccano asset frontend, `npm run build`. Per modifiche di sola documentazione non serve eseguire la suite.
- Non modificare file in `vendor/`, `node_modules/`, `storage/` o altri output generati per implementare una funzionalità.
- Le fonti definitive sono le rotte, il codice eseguito e le migration correnti. Se la documentazione AI diverge dal codice, verificare il comportamento nel codice e aggiornare i documenti pertinenti nella stessa modifica.

## Mappa della knowledge base

- [Contesto rapido](../docs/ai/QUICK_CONTEXT.md): orientamento minimo e comandi.
- [Mappa del progetto](../docs/ai/PROJECT_MAP.md): dove cercare ogni funzionalità.
- [Modello di dominio](../docs/ai/DOMAIN_MODEL.md): entità e relazioni.
- [Regole di business](../docs/ai/BUSINESS_RULES.md): invarianti e calcoli.
