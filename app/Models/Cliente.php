<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    protected $table = 'clienti';

    protected $fillable = [
        'nome',
        'cognome',
        'telefono',
        'email',
        'codice_fiscale',
        'data_nascita'
    ];
}
