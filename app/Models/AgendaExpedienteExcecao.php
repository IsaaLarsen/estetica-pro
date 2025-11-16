<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class AgendaExpedienteExcecao extends Model
{
    use HasFactory;

    protected $table = 'agenda_expediente_excecoes';

    protected $fillable = [
        'data',
        'inicio',
        'fim',
        'aplicar_todos',
    ];

    protected $casts = [
        'data'          => 'date',
        'aplicar_todos' => 'boolean',
    ];

    /**
     * Funcionários que terão este expediente especial
     * (quando NÃO for aplicar_todos).
     */
    public function funcionarios(): BelongsToMany
    {
        return $this->belongsToMany(
            Funcionario::class,
            'agenda_expediente_excecao_funcionario', // tabela pivot
            'excecao_id',                            // fk da exceção
            'funcionario_id'                         // fk do funcionário
        );
    }
}
