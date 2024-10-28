<?php

namespace App\Http\Controllers;

use App\Models\Produto;
use Illuminate\Http\Request;

class ProdutoController extends Controller
{
    public function create()
    {
        $produtos = Produto::all();

        return view('produtos.create', compact('produtos'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nome' => 'required|string|max:255',
            'valor_unitario' => 'required|numeric|min:0'
        ]);
        $produto = Produto::create([
            'nome' => $request->nome,
            'valor_unitario' => $request->valor_unitario,
        ]);

        return response()->json([
            'success' => true,
            'produto' => $produto
        ]);
    }

    public function index()
    {
        $produtos = Produto::all();
        return view('produtos.index', compact('produtos'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nome' => 'required|string|max:255',
            'valor_unitario' => 'required|numeric|min:0'
        ]);

        $produto = Produto::findOrFail($id);
        $produto->update([
            'nome' => $request->nome,
            'valor_unitario' => $request->valor_unitario,
        ]);

        return response()->json([
            'success' => true,
            'produto' => $produto
        ]);
    }

    public function destroy($id)
    {

        $produto = Produto::findOrFail($id);
        $produto->delete();

        return response()->json([
            'success' => true
        ]);
    }
}
