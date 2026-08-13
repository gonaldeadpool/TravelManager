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
        'massimo_partecipanti',
        'data_acconto',
        'importo_minimo_acconto',
        'data_saldo',
        'note',
        'locandina',
        'trasporti',
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
            'massimo_partecipanti' => 'integer',
            'data_acconto' => 'date',
            'importo_minimo_acconto' => 'decimal:2',
            'data_saldo' => 'date',
            'trasporti' => 'array',
            'prezzi_cabine' => 'array',
            'eta_gratuita' => 'integer',
        ];
    }

    public function pratiche()
    {
        return $this->hasMany(Pratica::class);
    }

    public function tappeRaccolta()
    {
        return $this->hasMany(TappaRaccolta::class)->orderBy('orario')->orderBy('nome');
    }
}
