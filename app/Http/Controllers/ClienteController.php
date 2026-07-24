<?php

namespace App\Http\Controllers;

use App\Models\Cliente;

use Illuminate\Http\Request;

class ClienteController extends Controller
{
        public function index()
        {
//            Cliente::create([
//                'nome' => 'Mario',
//                'cognome' => 'Rossi',
//                'telefono' => '3331234567',
//                'email' => 'mario.rossi@test.it'
//            ]);

            $clienti = Cliente::all();

            return view('clienti', [
                'clienti' => $clienti
            ]);
        }

        public function create()
        {
            return view('clienti-create');
        }

        public function store(Request $request)
        {
            $request->validate(
            [
            'nome' => 'required|max:100',
            'cognome' => 'required|max:100'
            ]
            //,
            //[
            //'nome.required' => 'Il nome è obbligatorio.',
            //'cognome.required' => 'Il cognome è obbligatorio.'
            //]
            );

            Cliente::create([
                'nome' => $request->nome,
                'cognome' => $request->cognome
            ]);

            return redirect()->route('clienti');
        }
}
