<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Viaggio extends Model
{
    protected $table = 'viaggi';

    protected $fillable = [
        'nome',
        'tipologia',
        'destinazione',
        'data_partenza',
        'data_rientro',
        'prezzo',
        'minimo_partecipanti',
        'note',
        'locandina',
        'trasporti',
        'sistemazioni',
    ];

    protected function casts(): array
    {
        return [
            'data_partenza' => 'date',
            'data_rientro' => 'date',
            'prezzo' => 'decimal:2',
            'minimo_partecipanti' => 'integer',
            'trasporti' => 'array',
            'sistemazioni' => 'array',
        ];
    }
}
