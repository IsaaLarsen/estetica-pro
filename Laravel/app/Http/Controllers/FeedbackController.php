<?php

namespace App\Http\Controllers;

use App\Models\Feedback;
use App\Models\Cliente;
use Illuminate\Http\Request;

class FeedbackController extends Controller
{
    /**
     * Lista todos os feedbacks (mais recentes primeiro)
     */
    public function index(Request $request)
    {
        $query = Feedback::with(['cliente', 'servico'])
            ->orderByDesc('created_at');

        // ======== FILTROS =========

        // Cliente ativo selecionado
        if ($request->filled('cliente_id')) {
            $query->where('cliente_id', $request->cliente_id);
        }

        // Data inicial
        if ($request->filled('data_inicio')) {
            $query->whereDate('created_at', '>=', $request->data_inicio);
        }

        // Data final
        if ($request->filled('data_fim')) {
            $query->whereDate('created_at', '<=', $request->data_fim);
        }

        // ==========================

        // ⬇⬇ Paginação oficial: 9 registros por página + mantém filtros na URL
        $feedbacks = $query->paginate(9)->withQueryString();

        // Clientes ATIVOS para o filtro
        $clientes = Cliente::where('ativo', 1)
            ->orderBy('nome')
            ->get();

        return view('feedbacks.index', [
            'usuario'   => session('usuario'),
            'feedbacks' => $feedbacks,
            'clientes'  => $clientes,
        ]);
    }

    /**
     * Detalhe de um feedback específico
     */
    public function show(Feedback $feedback)
    {
        $feedback->load(['cliente', 'servico']);

        return view('feedbacks.show', [
            'usuario'  => session('usuario'),
            'feedback' => $feedback,
        ]);
    }
}
