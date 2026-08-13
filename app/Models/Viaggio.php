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
        'prezzi_cabine',
        'eta_gratuita',
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
            'prezzi_cabine' => 'array',
            'eta_gratuita' => 'integer',
        ];
    }

    public function pratiche()
    {
        return $this->hasMany(Pratica::class);
    }
}
