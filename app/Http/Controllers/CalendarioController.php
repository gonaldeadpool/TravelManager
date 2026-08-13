<?php

namespace App\Http\Controllers;

use App\Models\Viaggio;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CalendarioController extends Controller
{
    public function index(): View
    {
        return view('calendario');
    }

    public function eventi(Request $request)
    {
        $inizio = $request->date('start');
        $fine = $request->date('end');

        $viaggi = Viaggio::query()
            ->when($inizio, fn ($query) => $query->whereDate('data_rientro', '>=', $inizio))
            ->when($fine, fn ($query) => $query->whereDate('data_partenza', '<', $fine))
            ->orderBy('data_partenza')
            ->get();

        return response()->json($viaggi->map(fn (Viaggio $viaggio) => [
            'title' => $viaggio->nome,
            'start' => $viaggio->data_partenza->toDateString(),
            'end' => $viaggio->data_rientro->copy()->addDay()->toDateString(),
            'url' => route('viaggi.show', $viaggio),
            'backgroundColor' => $this->colore($viaggio->tipologia),
            'borderColor' => $this->colore($viaggio->tipologia),
            'extendedProps' => [
                'destinazione' => $viaggio->destinazione,
                'tipologia' => ucfirst($viaggio->tipologia),
            ],
        ]));
    }

    private function colore(string $tipologia): string
    {
        return match ($tipologia) {
            'tour' => '#0f766e',
            'crociera' => '#0369a1',
            default => '#4f46e5',
        };
    }
}