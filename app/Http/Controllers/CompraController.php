<?php

namespace App\Http\Controllers;

use App\Models\Compra;
use App\Models\Produto;
use App\Models\Cliente;
use App\Models\Parcela;
use Illuminate\Http\Request;

class CompraController extends Controller
{
    public function create()
    {
        $clientes = Cliente::all();
        $produtos = Produto::all();
        $compras = Compra::with('cliente', 'produtos')->get(); 
        return view('compras.create', compact('clientes', 'produtos', 'compras'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'cliente_id' => 'required|exists:clientes,id',
            'produtos' => 'required|array',
            'produtos.*.produto_id' => 'required|exists:produtos,id',
            'produtos.*.quantidade' => 'required|integer|min:1', 
            'pagamento' => 'required',
            'num_parcelas' => 'nullable|integer|min:1',
            'parcelas' => 'nullable|array',
        ]);

        $valor_total = 0;
        foreach ($request->produtos as $produto) {
            $produtoData = Produto::find($produto['produto_id']);
            $valor_total += $produtoData->valor_unitario * $produto['quantidade'];
        }

        if ($request->pagamento == 'parcelado') {
            $somaParcelas = array_sum(array_column($request->parcelas, 'valor'));
            if ($somaParcelas != $valor_total) {
                return redirect()->back()->withErrors(['parcelas' => 'A soma das parcelas deve ser igual ao valor total da compra.']);
            }
        }

        $compra = Compra::create([
            'cliente_id' => $request->cliente_id,
            'valor_total' => $valor_total,
            'forma_pagamento' => $request->pagamento,
        ]);

        foreach ($request->produtos as $produto) {
            $compra->produtos()->attach($produto['produto_id'], ['quantidade' => $produto['quantidade']]);
        }

        if ($request->pagamento == 'parcelado' && isset($request->parcelas)) {
            foreach ($request->parcelas as $parcela) {
                Parcela::create([
                    'compra_id' => $compra->id,
                    'data_vencimento' => $parcela['data_vencimento'],
                    'valor_parcela' => $parcela['valor'],
                ]);
            }
        }

        return redirect()->route('compras.create')->with('success', 'Compra realizada com sucesso!');
    }

public function edit($id)
{
    $compra = Compra::with('produtos', 'cliente')->findOrFail($id);
    $clientes = Cliente::all();
    $produtos = Produto::all();
    $compras = Compra::with('cliente')->get();

    return view('compras.edit', compact('compra', 'clientes', 'produtos', 'compras'));
}


    public function update(Request $request, $id)
    {
        $request->validate([
            'cliente_id' => 'required|exists:clientes,id',
            'produtos' => 'required|array',
            'produtos.*.produto_id' => 'required|exists:produtos,id',
            'produtos.*.quantidade' => 'required|integer|min:1',
            'pagamento' => 'required',
            'num_parcelas' => 'nullable|integer|min:1', 
            'parcelas' => 'nullable|array',
        ]);

        $compra = Compra::findOrFail($id);

        $valor_total = 0;
        foreach ($request->produtos as $produto) {
            $produtoData = Produto::find($produto['produto_id']);
            $valor_total += $produtoData->valor_unitario * $produto['quantidade'];
        }
        if ($request->pagamento == 'parcelado') {
            $somaParcelas = array_sum(array_column($request->parcelas, 'valor'));
            if ($somaParcelas != $valor_total) {
                return redirect()->back()->withErrors(['parcelas' => 'A soma das parcelas deve ser igual ao valor total da compra.']);
            }
        }

        $compra->update([
            'cliente_id' => $request->cliente_id,
            'valor_total' => $valor_total,
            'forma_pagamento' => $request->pagamento,
        ]);

        $compra->produtos()->detach(); 
        foreach ($request->produtos as $produto) {
            $compra->produtos()->attach($produto['produto_id'], ['quantidade' => $produto['quantidade']]);
        }

        $compra->parcelas()->delete();

        if ($request->pagamento == 'parcelado' && isset($request->parcelas)) {
            foreach ($request->parcelas as $parcela) {
                Parcela::create([
                    'compra_id' => $compra->id,
                    'data_vencimento' => $parcela['data_vencimento'],
                    'valor_parcela' => $parcela['valor'],
                ]);
            }
        }

        return redirect()->route('compras.create')->with('success', 'Compra atualizada com sucesso!');
    }

    public function destroy($id)
    {
        $compra = Compra::findOrFail($id);

        if ($compra->produtos()->exists()) {
            $compra->produtos()->detach(); 
        }

        if ($compra->parcelas()->exists()) {
            $compra->parcelas()->delete();
        }


        $compra->delete();

        return redirect()->route('compras.create')->with('success', 'Compra excluída com sucesso.');
    }
}

