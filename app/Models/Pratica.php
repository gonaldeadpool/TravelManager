<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pratica extends Model
{
    protected $table = 'pratiche';

    protected $fillable = [
        'viaggio_id',
        'totale',
        'acconto',
        'data_acconto',
        'saldo',
        'data_saldo',
        'note',
    ];

    protected function casts(): array
    {
        return [
            'totale' => 'decimal:2',
            'acconto' => 'decimal:2',
            'saldo' => 'decimal:2',
            'data_acconto' => 'date',
            'data_saldo' => 'date',
        ];
    }

    public function viaggio()
    {
        return $this->belongsTo(Viaggio::class);
    }

    public function clienti()
    {
        return $this->belongsToMany(Cliente::class, 'cliente_pratica')->withPivot(['gratuito', 'posto', 'posto_bus']);
    }

    public function documenti()
    {
        return $this->hasMany(PraticaDocumento::class);
    }
}