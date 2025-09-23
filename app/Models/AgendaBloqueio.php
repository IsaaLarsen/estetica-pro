<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AgendaBloqueio extends Model
{
    protected $table = 'agenda_bloqueios';
    protected $fillable = ['funcionario_id','inicio','fim','motivo'];
    protected $casts = ['inicio'=>'datetime','fim'=>'datetime'];

    public function funcionario(): BelongsTo { return $this->belongsTo(Funcionario::class); }
}
