<?php
// app/Models/Comissao.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Comissao extends Model
{
    protected $table = 'comissoes';

    protected $fillable = [
        'agenda_id','funcionario_id','servico_id',
        'valor_servico','percentual','valor_comissao',
        'status','pago_em','obs'
    ];

    protected $casts = [
        'pago_em' => 'datetime',
        'valor_servico' => 'decimal:2',
        'valor_comissao' => 'decimal:2',
        'percentual' => 'decimal:2',
    ];

    public function agenda(){ return $this->belongsTo(Agenda::class); }
}
