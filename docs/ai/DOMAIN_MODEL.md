# Modello di dominio

Schema derivato da `app/Models/` e dalle migration in `database/migrations/`. In caso di divergenza, verificare lo schema risultante dall'intera sequenza di migration.

## Entità principali

### Viaggio (`viaggi`)

Rappresenta l'offerta/partenza: nome, tipologia, destinazione, data partenza/rientro, prezzi, capacità, scadenze di pagamento, note, locandina e configurazioni logistiche.

- Tipi applicativi: `viaggio`, `soggiorno`, `tour`, `crociera` (`Viaggio::TIPOLOGIE`).
- `prezzo`, `quota_ridotto`, `quota_fissa`, capacità, date e scadenze sono colonne; `trasporti` e `prezzi_cabine` sono JSON castati ad array.
- Ha molte `Pratica` e molte `TappaRaccolta`.

### Cliente (`clienti`)

Anagrafica personale e contatti: nome, cognome, data/luogo di nascita, codice fiscale, telefoni, email, indirizzo e note.

- Ha molti `ClienteDocumento`.
- Partecipa a molte `Pratica` tramite `cliente_pratica`.
- Può essere collegato a una tappa del viaggio tramite `viaggio_tappa_cliente`.

### Pratica (`pratiche`)

Prenotazione riferita a un singolo viaggio. Memorizza cabina, totali, sconto, supplementi, importi/date acconto e saldo, note.

- Appartiene a un `Viaggio`.
- Ha molti `Cliente` tramite `cliente_pratica`.
- Ha molti `PraticaDocumento`.
- Il totale viene derivato server-side dal viaggio e dai partecipanti; vedere [BUSINESS_RULES.md](BUSINESS_RULES.md).

### Pivot `cliente_pratica`

Associazione molti-a-molti pratica/cliente con chiave primaria composta `(pratica_id, cliente_id)`. Campi di dominio:

- `gratuito`, `ridotto`: tariffa del cliente in quella pratica.
- `posto`, `posto_bus`: posto assegnato e indice del bus nella lista trasporti del viaggio.

Il posto è registrato sulla partecipazione; quando lo stesso cliente compare in più pratiche dello stesso viaggio, l'assegnazione viene mantenuta sincronizzata sui relativi record pivot dal flusso di assegnazione posti.

### Documenti

- `ClienteDocumento` → `cliente_documenti`: tipo, numero, scadenza, nome originale, percorso, MIME e dimensione.
- `PraticaDocumento` → `pratica_documenti`: nome originale, percorso, MIME e dimensione; appartiene a una pratica.
- I metadati sono nel database; il contenuto è su filesystem configurato tramite `LocalStoragePaths`. I documenti cliente/pratica usano percorsi privati e download autenticato.

### Tappe di raccolta

- `TappaRaccolta` → `viaggio_tappe_raccolta`: appartiene a un viaggio, con nome e orario. Nome univoco per viaggio.
- Pivot `viaggio_tappa_cliente`: collega viaggio, tappa e cliente. La chiave primaria `(viaggio_id, cliente_id)` rappresenta al massimo una tappa assegnata per cliente e viaggio; ulteriore unicità `(tappa_id, cliente_id)`.
- L'assegnazione è consentita solo a un cliente che partecipa al viaggio e a una tappa dello stesso viaggio.

### Utenti e impostazioni

- `User` (`users`): autenticazione, `role` (`admin`/`operatore`), `menu_permissions` JSON, `dashboard_widgets`, avatar e dati 2FA. Admin supera i controlli menu; l'operatore usa le permission configurate.
- `AppSetting` (`app_settings`): chiave univoca e valore testuale nullable per soglie, percorsi storage e configurazione mail. La password SMTP è cifrata prima di essere salvata.

## Relazioni e vincoli di cancellazione

```text
Viaggio 1 ── * Pratica * ── * Cliente
                    │              │
                    │              └── * ClienteDocumento
                    └── * PraticaDocumento

Viaggio 1 ── * TappaRaccolta
Viaggio * ── * Cliente (via viaggio_tappa_cliente, con la tappa come relazione)
```

- `pratiche.viaggio_id` usa `restrictOnDelete`: non eliminare un viaggio con pratiche associate.
- `cliente_pratica.cliente_id` usa `restrictOnDelete`: non eliminare direttamente un cliente ancora associato a pratiche.
- Eliminando una pratica, il DB elimina a cascata le righe pivot e gli indici `pratica_documenti`; il controller deve rimuovere anche i file fisici.
- Eliminando un cliente, i metadati dei suoi documenti hanno cascade; il controller elimina prima i file fisici. Il controller blocca inoltre la cancellazione se esistono pratiche correlate.
- Eliminando un viaggio, tappe e associazioni logistiche sono eliminate a cascata; la locandina su disco richiede gestione esplicita.

## Schema accessorio

Laravel include anche tabelle di autenticazione/sessione, cache, queue e password reset. Sono infrastruttura e non entità del dominio viaggi.
