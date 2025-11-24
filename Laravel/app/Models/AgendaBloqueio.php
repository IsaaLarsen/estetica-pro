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
     * Funcionários vinculados ao bloqueio.
     */
    public function funcionarios(): BelongsToMany
    {
        return $this->belongsToMany(
            Funcionario::class,
            'agenda_bloqueio_funcionario',
            'bloqueio_id',
            'funcionario_id'
        );
    }

    /**
     * Enriquecimento do toArray para LOGS.
     */
    public function toArray()
    {
        $arr = parent::toArray();

        // Adiciona nomes dos funcionários para exibir no LOG
        $arr['funcionarios_nomes'] = $this->funcionarios
            ? $this->funcionarios->pluck('nome')->toArray()
            : [];

        return $arr;
    }
}
