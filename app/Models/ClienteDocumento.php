<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClienteDocumento extends Model
{
    protected $table = 'cliente_documenti';

    protected $fillable = [
        'cliente_id',
        'tipo',
        'numero',
        'scadenza',
        'nome_originale',
        'percorso',
        'mime_type',
        'dimensione',
    ];

    protected function casts(): array
    {
        return ['scadenza' => 'date'];
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }
}
