<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
class Cliente extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $table = 'clientes';

    protected $fillable = [
        'nome','email','telefone','cpf','endereco','data_nascimento','ativo','senha',
    ];

    protected $hidden = ['senha','remember_token'];   // não expor

    protected $casts  = [
        'data_nascimento' => 'date:Y-m-d',
        'ativo'           => 'boolean',
        'senha'           => 'hashed', // hash automático ao atribuir
    ];

    // campo de login segue sendo email
    public function getAuthIdentifierName() { return 'email'; }
    // informa ao Auth/Sanctum qual é o campo de senha
    public function getAuthPassword() { return $this->senha; }
}

