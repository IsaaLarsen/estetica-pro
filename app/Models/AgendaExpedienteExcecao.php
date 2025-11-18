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
        // aplicar_todos não existe aqui, não deve estar no fillable
    ];

    protected $casts = [
        'data'   => 'date',
        'inicio' => 'string',
        'fim'    => 'string',
    ];

    /**
     * Funcionários vinculados a esta exceção de expediente.
     */
    public function funcionarios(): BelongsToMany
    {
        return $this->belongsToMany(
            Funcionario::class,
            'agenda_expediente_excecao_funcionario',
            'excecao_id',
            'funcionario_id'
        );
    }

    /**
     * Enriquecimento do toArray para LOGS
     */
    public function toArray()
    {
        $arr = parent::toArray();

        // Adiciona nomes dos funcionários
        $arr['funcionarios_nomes'] = $this->funcionarios
            ? $this->funcionarios->pluck('nome')->toArray()
            : [];

        return $arr;
    }
}
