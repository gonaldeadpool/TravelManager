<?php

namespace App\Logging;

use DateTimeZone;
use Illuminate\Log\Logger;
use Monolog\LogRecord;

class ContestoUtenteLog
{
    public function __invoke(Logger $logger): void
    {
        $logger->getLogger()->pushProcessor(function (LogRecord $record): LogRecord {
            $utente = auth()->user();

            return $record->with(
                datetime: $record->datetime->setTimezone(new DateTimeZone('Europe/Rome')),
                extra: $record->extra + [
                    'utente' => $utente ? $utente->name . ' (#' . $utente->id . ')' : 'ospite',
                ],
            );
        });
    }
}
