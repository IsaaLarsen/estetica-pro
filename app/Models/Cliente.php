<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    use HasFactory;

    protected $fillable = [
        'nome',
        'telefone',
        'data_nascimento',
        'status',   // ativo (true) ou inativo (false)
        'cpf',
        'email',
        'endereco',
    ];

    protected $casts = [
        'status' => 'boolean',
        'data_nascimento' => 'date',
    ];
}
