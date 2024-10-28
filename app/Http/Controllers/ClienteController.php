<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use Illuminate\Http\Request;

class ClienteController extends Controller
{
    public function create()
    {
        $clientes = Cliente::all();
        return view('clientes.create', compact('clientes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nome_completo' => 'required|string|max:255',
            'cpf' => 'required|string|max:14',
            'data_nascimento' => 'required|date',
            'rua' => 'required|string|max:255',
            'bairro' => 'required|string|max:255',
            'cidade' => 'required|string|max:255',
            'estado' => 'required|string|max:2',
            'complemento' => 'nullable|string|max:255'
        ]);

        $cliente = Cliente::create($request->all());

        return response()->json([
            'success' => true,
            'cliente' => $cliente
        ]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nome_completo' => 'required|string|max:255',
            'cpf' => 'required|string|max:14',
            'data_nascimento' => 'required|date',
            'rua' => 'required|string|max:255',
            'bairro' => 'required|string|max:255',
            'cidade' => 'required|string|max:255',
            'estado' => 'required|string|max:2',
            'complemento' => 'nullable|string|max:255'
        ]);

        $cliente = Cliente::findOrFail($id);
        $cliente->update($request->all());

        return response()->json([
            'success' => true,
            'cliente' => $cliente
        ]);
    }

    public function destroy($id)
    {
        $cliente = Cliente::findOrFail($id);
        $cliente->delete();

        return response()->json([
            'success' => true
        ]);
    }
}
