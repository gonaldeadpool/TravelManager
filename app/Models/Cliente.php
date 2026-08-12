<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    protected $table = 'clienti';

    protected $fillable = [

        'nome',
        'cognome',

        'data_nascita',
        'luogo_nascita',
        'codice_fiscale',

        'telefono',
        'cellulare',
        'email',

        'indirizzo',
        'cap',
        'citta',
        'provincia',
        'nazione',

        'note'
    ];

    public function documenti()
    {
        return $this->hasMany(ClienteDocumento::class);
    }

}
