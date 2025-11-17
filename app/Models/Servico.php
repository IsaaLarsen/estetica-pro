<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Servico extends Model
{
    protected $table = 'servicos';

    protected $fillable = [
        'nome',
        'valor',             // <- coluna de preço
        'comissao_percent',
        'duracao_minutos',
        'descricao',
        'ativo',
    ];

    protected $casts = [
        'valor'            => 'decimal:2',
        'comissao_percent' => 'decimal:2',
        'duracao_minutos'  => 'integer',
        'ativo'            => 'boolean',
    ];

    // Pivot com quem pode executar o serviço
    public function funcionarios()
    {
        return $this->belongsToMany(
            Funcionario::class,
            'funcionario_servico',
            'servico_id',
            'funcionario_id'
        )->withTimestamps();
    }
}
