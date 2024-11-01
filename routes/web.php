<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProdutoController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\CompraController;
use App\Http\Controllers\ParcelaController;

Route::get('/', function () {
    return redirect()->route('compras.create');
});


Route::resource('produtos', ProdutoController::class);
Route::resource('clientes', ClienteController::class);
Route::resource('compras', CompraController::class);


Route::post('compras/{compra}/parcelas', [ParcelaController::class, 'store'])->name('parcelas.store');
Route::get('/compras/{id}/parcelas', [CompraController::class, 'getParcelas']);


