<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cargo extends Model
{
    use HasFactory;

    /**
     * Nome da tabela (opcional, Laravel já deduz "cargos")
     */
    protected $table = 'cargos';

    /**
     * Campos permitidos para inserção/atualização em massa
     */
    protected $fillable = [
        'nome',
        'descricao',
        'ativo',
    ];

    /**
     * Casts automáticos de tipos
     */
    protected $casts = [
        'ativo' => 'boolean',
    ];

    /**
     * Relacionamento: um cargo pode ter vários funcionários
     */
    public function funcionarios()
    {
        return $this->hasMany(Funcionario::class, 'cargo_id');
    }
}
