<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;

class Cliente extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $table = 'clientes';

    protected $fillable = [
        'nome',
        'email',
        'telefone',
        'cpf',
        'data_nascimento',
        'ativo',
        'senha',

        // NOVOS CAMPOS DE ENDEREÇO
        'cep',
        'rua',
        'bairro',
        'numero',
    ];

    protected $hidden = [
        'senha',
        'remember_token'
    ];

    protected $casts  = [
        'data_nascimento' => 'date:Y-m-d',
        'ativo'           => 'boolean',
        'senha'           => 'hashed', // hash automático ao atribuir
    ];

    // campo de login segue sendo email
    public function getAuthIdentifierName()
    { 
        return 'email';
    }

    // campo de senha para o Auth
    public function getAuthPassword()
    { 
        return $this->senha;
    }
}
