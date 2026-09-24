<?php

namespace App\Http\Controllers;

use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class LogController extends Controller
{
    private const TIPI = ['laravel', 'nginx_access', 'nginx_error'];

    public function index(): View
    {
        abort_unless(auth()->user()->isAdmin(), 403);

        $log = collect(self::TIPI)->map(fn (string $tipo) => [
            'tipo' => $tipo,
            'etichetta' => $this->etichetta($tipo),
            'percorso' => $this->percorso($tipo),
            'esiste' => is_file($this->percorso($tipo)) && is_readable($this->percorso($tipo)),
            'dimensione' => is_file($this->percorso($tipo)) ? filesize($this->percorso($tipo)) : 0,
        ]);

        return view('log.index', ['log' => $log]);
    }

    public function download(string $tipo): BinaryFileResponse
    {
        abort_unless(auth()->user()->isAdmin(), 403);
        abort_unless(in_array($tipo, self::TIPI, true), 404);

        $percorso = $this->percorso($tipo);
        abort_unless(is_file($percorso) && is_readable($percorso), 404);

        $nomeFile = $this->etichetta($tipo) . '-' . now()->format('Y-m-d_His') . '.log';

        return response()->download($percorso, $nomeFile);
    }

    private function etichetta(string $tipo): string
    {
        return match ($tipo) {
            'laravel' => 'laravel',
            'nginx_access' => 'nginx-access',
            'nginx_error' => 'nginx-error',
        };
    }

    private function percorso(string $tipo): string
    {
        return match ($tipo) {
            'laravel' => $this->ultimoLogLaravel(),
            'nginx_access' => env('LOG_NGINX_ACCESS_PATH', '/var/log/nginx/access.log'),
            'nginx_error' => env('LOG_NGINX_ERROR_PATH', '/var/log/nginx/error.log'),
        };
    }

    private function ultimoLogLaravel(): string
    {
        // con LOG_STACK=daily il nome cambia ogni giorno (laravel-YYYY-MM-DD.log): prendiamo il più recente
        $piuRecente = collect(glob(storage_path('logs/laravel*.log')) ?: [])
            ->sortByDesc(fn (string $percorso) => filemtime($percorso))
            ->first();

        return $piuRecente ?? storage_path('logs/laravel.log');
    }
}
