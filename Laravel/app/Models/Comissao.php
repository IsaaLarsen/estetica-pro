<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Carbon\Carbon;

class Comissao extends Model
{
    protected $table = 'comissoes';

    protected $fillable = [
        'agenda_id','funcionario_id','servico_id',
        'valor_servico','percentual','valor_comissao',
        'status','pago_em','obs'
    ];

    protected $casts = [
        'valor_servico'  => 'decimal:2',
        'valor_comissao' => 'decimal:2',
        'percentual'     => 'decimal:2',
    ];

    /**
     * 🚀 Força todas as datas desta model a usar America/Sao_Paulo
     */
    protected function pagoEm(): Attribute
    {
        return Attribute::make(
            get: fn($value) =>
                $value ? Carbon::parse($value)->setTimezone('America/Sao_Paulo')->format('Y-m-d H:i:s') : null
        );
    }

    protected function updatedAt(): Attribute
    {
        return Attribute::make(
            get: fn($value) =>
                $value ? Carbon::parse($value)->setTimezone('America/Sao_Paulo')->format('Y-m-d H:i:s') : null
        );
    }

    protected function createdAt(): Attribute
    {
        return Attribute::make(
            get: fn($value) =>
                $value ? Carbon::parse($value)->setTimezone('America/Sao_Paulo')->format('Y-m-d H:i:s') : null
        );
    }
}
