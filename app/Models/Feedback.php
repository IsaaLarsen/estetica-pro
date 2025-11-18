<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Feedback extends Model
{
    use HasFactory;

    protected $table = 'feedbacks';

    protected $fillable = [
        'cliente_id',
        'servico_id',
        'nota',
        'comentario',
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function servico()
    {
        return $this->belongsTo(Servico::class);
    }
}
