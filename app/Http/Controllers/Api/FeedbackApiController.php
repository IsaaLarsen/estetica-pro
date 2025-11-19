<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Feedback;
use App\Models\Servico;
use Illuminate\Http\Request;

class FeedbackApiController extends Controller
{
    /**
     * POST /api/feedbacks
     * Body: { servico_id, nota?, comentario }
     * auth:sanctum (cliente logado)
     */
    public function store(Request $r)
    {
        $r->validate([
            'servico_id' => 'required|exists:servicos,id',
            'comentario' => 'required|string|max:2000',
            'nota'       => 'nullable|integer|min:1|max:5',
        ]);

        $cliente = $r->user(); // cliente autenticado por Sanctum

        $feedback = Feedback::create([
            'cliente_id' => $cliente->id,
            'servico_id' => $r->servico_id,
            'nota'       => $r->nota,
            'comentario' => $r->comentario,
        ]);

        return response()->json([
            'ok'       => true,
            'feedback' => [
                'id'         => $feedback->id,
                'servico'    => $feedback->servico->nome ?? null,
                'nota'       => $feedback->nota,
                'comentario' => $feedback->comentario,
                'created_at' => $feedback->created_at->toDateTimeString(),
            ],
        ], 201);
    }
}
