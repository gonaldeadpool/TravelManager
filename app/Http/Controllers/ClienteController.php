<?php

namespace App\Http\Controllers;

use App\Models\Cliente;

use Illuminate\Http\Request;

class ClienteController extends Controller
{
        public function index(Request $request)
        {
            $ricerca = $request->input('ricerca');

            $clienti = Cliente::query();

            if ($ricerca) {

                $clienti->where('nome', 'like', "%{$ricerca}%")
                        ->orWhere('cognome', 'like', "%{$ricerca}%")
                        ->orWhere('email', 'like', "%{$ricerca}%");

            }

            $clienti = $clienti->paginate(5)->withQueryString();

            return view('clienti', [
                'clienti' => $clienti,
                'ricerca' => $ricerca
            ]);
        }

        public function search(Request $request)
        {
            $ricerca = $request->input('q');

            $clienti = Cliente::query()

                ->where('nome', 'like', "%{$ricerca}%")
                ->orWhere('cognome', 'like', "%{$ricerca}%")
                ->orWhere('email', 'like', "%{$ricerca}%")

                ->paginate(5)->withQueryString();

            return view('clienti._table', ['clienti' => $clienti]);
        }        

        public function create()
        {
            return view('clienti-create');
        }

        public function edit($id)
        {
            $cliente = Cliente::findOrFail($id);

            return view('clienti-edit', [
                'cliente' => $cliente
            ]);
        }

        public function update(Request $request, $id)
        {
            $request->validate([
                'nome' => 'required|max:100',
                'cognome' => 'required|max:100',
            ]);

            $cliente = Cliente::findOrFail($id);

            $cliente->update([
                'nome' => $request->nome,
                'cognome' => $request->cognome,
                
                'data_nascita' => $request->data_nascita,
                'luogo_nascita' => $request->luogo_nascita,
                'codice_fiscale' => $request->codice_fiscale,
                
                'telefono' => $request->telefono,
                'cellulare' => $request->cellulare,
                'email' => $request->email,
                
                'indirizzo' => $request->indirizzo,
                'cap' => $request->cap,
                'citta' => $request->citta,
                'provincia' => $request->provincia,
                'nazione' => $request->nazione,
                
                'note' => $request->note,
            ]);

            return redirect()->route('clienti');
        }

        public function destroy($id)
        {
            $cliente = Cliente::findOrFail($id);

            $cliente->delete();

            return redirect()->route('clienti');
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
                'cognome' => $request->cognome,
                
                'data_nascita' => $request->data_nascita,
                'luogo_nascita' => $request->luogo_nascita,
                'codice_fiscale' => $request->codice_fiscale,
                
                'telefono' => $request->telefono,
                'cellulare' => $request->cellulare,
                'email' => $request->email,
                
                'indirizzo' => $request->indirizzo,
                'cap' => $request->cap,
                'citta' => $request->citta,
                'provincia' => $request->provincia,
                'nazione' => $request->nazione,
                
                'note' => $request->note,
            ]);

            return redirect()->route('clienti');
        }
}
