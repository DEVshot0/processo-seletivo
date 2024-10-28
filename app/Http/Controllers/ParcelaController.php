<?php

namespace App\Http\Controllers;

use App\Models\Parcela;
use Illuminate\Http\Request;

class ParcelaController extends Controller
{
    public function store(Request $request, $compra_id)
    {
        $request->validate([
            'parcelas' => 'required|array',
        ]);

        foreach ($request->parcelas as $parcela) {
            Parcela::create([
                'compra_id' => $compra_id,
                'data_vencimento' => $parcela['data_vencimento'],
                'valor_parcela' => $parcela['valor'],
            ]);
        }

        return redirect()->back()->with('success', 'Parcelas criadas com sucesso.');
    }
}
