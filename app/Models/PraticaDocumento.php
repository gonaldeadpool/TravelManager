<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PraticaDocumento extends Model
{
    protected $table = 'pratica_documenti';

    protected $fillable = [
        'nome_originale',
        'percorso',
        'mime_type',
        'dimensione',
    ];

    public function pratica()
    {
        return $this->belongsTo(Pratica::class);
    }
}