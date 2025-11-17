<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class AgendaBloqueio extends Model
{
    protected $table = 'agenda_bloqueios';

    protected $fillable = [
        'aplicar_todos',
        'inicio',
        'fim',
        'motivo',
    ];

    protected $casts = [
        'inicio'        => 'datetime',
        'fim'           => 'datetime',
        'aplicar_todos' => 'boolean',
    ];

    /**
     * Funcionários vinculados a este bloqueio
     * (quando não for bloqueio geral).
     */
    public function funcionarios(): BelongsToMany
    {
        return $this->belongsToMany(
            Funcionario::class,
            'agenda_bloqueio_funcionario', // tabela pivot
            'bloqueio_id',                 // fk do bloqueio
            'funcionario_id'               // fk do funcionário
        );
    }

    // Se em algum lugar antigo ainda tiver $bloqueio->funcionario,
    // a gente pode voltar com este belongsTo aqui, importando BelongsTo certinho.
    // Por enquanto tirei pra evitar erro e confusão com o novo modelo.
}
