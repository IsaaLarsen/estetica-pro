<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Funcionario extends Model
{
    protected $table = 'funcionarios'; // opcional, mas explícito
    protected $fillable = [
        'nome',
        'cpf',
        'email',
        'cargo',      // usamos essa coluna no filtro por cargo
        'telefone',
        'endereco',
        'ativo',
    ];
    protected $casts = [
        'ativo' => 'boolean',
    ];
}
