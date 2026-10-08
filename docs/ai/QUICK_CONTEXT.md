# TravelManager — contesto rapido

## In una frase

Applicazione Laravel per un'agenzia viaggi: gestisce catalogo viaggi, anagrafiche e documenti clienti, pratiche con partecipanti e tariffe, pagamenti, posti/tappe di raccolta, riepiloghi PDF/email, calendario e dashboard.

## Stack e comandi

- PHP `^8.3`, Laravel `^13.8`; frontend Blade, Alpine.js, Tailwind CSS e Vite.
- FullCalendar per la pianificazione; `barryvdh/laravel-dompdf` per i PDF.
- Avvio locale: `composer run dev` (server Laravel, worker queue, log e Vite).
- Test: `composer test` oppure `php artisan test`; PHPUnit usa SQLite in-memory.
- Build asset: `npm run build`.
- Database predefinito configurato in `config/database.php`: SQLite. Sono configurati anche MySQL/MariaDB e PostgreSQL.

## Percorso più breve per orientarsi

1. Rotte e middleware: `routes/web.php`, `bootstrap/app.php`, `app/Http/Middleware/MenuPermission.php`.
2. Casi d'uso e logica: `app/Http/Controllers/ViaggioController.php`, `ClienteController.php`, `PraticaController.php`; dashboard/calendario in controller dedicati.
3. Modello dati: `app/Models/` e tutte le migration in `database/migrations/`. Le migration successive modificano schema preesistente: leggerle in ordine, non inferire lo schema finale da una sola migration iniziale.
4. UI: viste di modulo in `resources/views/viaggi/`, `clienti/`, `pratiche/`; alcune pagine principali sono file alla radice di `resources/views/`.
5. Regole più sensibili: [BUSINESS_RULES.md](BUSINESS_RULES.md); relazioni: [DOMAIN_MODEL.md](DOMAIN_MODEL.md).

## Concetti essenziali

- Una `Pratica` appartiene a un `Viaggio` e collega uno o più `Clienti` tramite `cliente_pratica`; la pivot conserva gratuito/ridotto e dati posto.
- I prezzi della pratica sono calcolati lato server. Il totale è basato sulle quote dei partecipanti, supplementi e sconto.
- Admin e operatore sono i ruoli applicativi. I permessi menu per operatore sono controllati dal middleware globale del gruppo `web`.
- Le locandine e i documenti hanno percorsi configurabili in `AppSetting`; documenti di clienti e pratiche sono in storage privato.
- Il progetto e le etichette UI sono in italiano.

## Fonti autorevoli (evitare scansioni ampie)

- Rotte: [routes/web.php](../../routes/web.php)
- Regole prezzi/pratiche: [PraticaController.php](../../app/Http/Controllers/PraticaController.php), [Pratica.php](../../app/Models/Pratica.php)
- Modulo/form viaggio: [ViaggioController.php](../../app/Http/Controllers/ViaggioController.php), [viaggi/_form.blade.php](../../resources/views/viaggi/_form.blade.php)
- Ruoli: [User.php](../../app/Models/User.php), [MenuPermission.php](../../app/Http/Middleware/MenuPermission.php)
- Schema: [database/migrations/](../../database/migrations/)
