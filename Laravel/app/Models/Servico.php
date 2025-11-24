<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Servico extends Model
{
    use HasFactory;

    protected $table = 'servicos';

    protected $fillable = [
        'nome',
        'descricao',
        'valor',
        'duracao_minutos',
        'ativo',
        // coloque aqui os campos que existem na sua tabela
    ];

    public function feedbacks()
    {
        return $this->hasMany(Feedback::class);
    }
}
