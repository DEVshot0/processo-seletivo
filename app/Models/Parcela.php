<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Parcela extends Model
{
    use HasFactory;

    protected $fillable = ['compra_id', 'data_vencimento', 'valor_parcela'];

    public function compra()
    {
        return $this->belongsTo(Compra::class);
    }
}
