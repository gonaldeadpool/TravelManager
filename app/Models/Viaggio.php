<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Viaggio extends Model
{
    protected $table = 'viaggi';

    protected $fillable = [
        'nome',
        'gestione',
        'destinazione',
        'data_partenza',
        'data_rientro',
        'locandina',
        'trasporti',
        'sistemazioni',
    ];

    protected function casts(): array
    {
        return [
            'data_partenza' => 'date',
            'data_rientro' => 'date',
            'trasporti' => 'array',
            'sistemazioni' => 'array',
        ];
    }
}
