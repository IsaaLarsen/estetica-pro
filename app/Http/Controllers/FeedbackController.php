<?php

namespace App\Http\Controllers;

use App\Models\Feedback;
use Illuminate\Http\Request;

class FeedbackController extends Controller
{
    /**
     * Lista todos os feedbacks (mais recentes primeiro)
     */
    public function index(Request $request)
    {
        $feedbacks = Feedback::with(['cliente', 'servico'])
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('feedbacks.index', [
            'usuario'   => session('usuario'),
            'feedbacks' => $feedbacks,
        ]);
    }

    /**
     * Detalhe de um feedback específico (opcional)
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
