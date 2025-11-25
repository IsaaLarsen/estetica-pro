<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Funcionario extends Model
{
    protected $table = 'funcionarios';

    protected $fillable = [
        'nome',
        'cpf',
        'email',
        'cargo',
        'telefone',
        'data_nascimento',
        'ativo',

        // NOVOS CAMPOS DE ENDEREÇO
        'cep',
        'rua',
        'bairro',
        'numero',
    ];

    protected $casts = [
        'ativo'           => 'boolean',
        'data_nascimento' => 'date',
    ];
}
