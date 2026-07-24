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

            $clienti = $clienti->get();

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

                ->get();

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
                'cognome' => $request->cognome
            ]);

            return redirect()->route('clienti');
        }
}
