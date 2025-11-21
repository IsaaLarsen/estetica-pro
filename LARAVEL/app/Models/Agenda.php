<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Agenda extends Model
{
    protected $table = 'agendas';
    protected $fillable = [
        'funcionario_id','cliente_id','servico_id','inicio','fim','status','observacoes'
    ];
    protected $casts = ['inicio'=>'datetime','fim'=>'datetime'];

    public function funcionario(): BelongsTo { return $this->belongsTo(Funcionario::class); }
    public function cliente(): BelongsTo { return $this->belongsTo(Cliente::class); }
    public function servico(): BelongsTo { return $this->belongsTo(Servico::class); }
}
