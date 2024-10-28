<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    use HasFactory;

    protected $fillable = [
        'nome_completo', 'cpf', 'data_nascimento', 'rua', 
        'bairro', 'cidade', 'estado', 'complemento'
    ];

    public function compras()
    {
        return $this->hasMany(Compra::class);
    }
}
