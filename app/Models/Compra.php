<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Compra extends Model
{
    use HasFactory;

    protected $fillable = ['cliente_id', 'valor_total', 'forma_pagamento'];

    
    public function produtos()
    {
        return $this->belongsToMany(Produto::class, 'compra_produto')
                    ->withPivot('quantidade')
                    ->withTimestamps();
    }

    
    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    
    public function parcelas()
    {
        return $this->hasMany(Parcela::class);
    }
}
