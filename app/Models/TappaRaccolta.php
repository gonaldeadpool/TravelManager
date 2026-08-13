<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TappaRaccolta extends Model
{
    protected $table = 'viaggio_tappe_raccolta';

    protected $fillable = ['viaggio_id', 'nome', 'orario'];

    protected function casts(): array
    {
        return ['orario' => 'datetime:H:i'];
    }

    public function viaggio()
    {
        return $this->belongsTo(Viaggio::class);
    }

    public function clienti()
    {
        return $this->belongsToMany(Cliente::class, 'viaggio_tappa_cliente', 'tappa_id', 'cliente_id')
            ->withPivot('viaggio_id');
    }
}
