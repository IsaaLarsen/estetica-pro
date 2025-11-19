<?php

namespace App\Http\Controllers;

use App\Models\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class LogController extends Controller
{
    public function index(Request $request)
    {
        if (!Session::has('usuario')) {
            return redirect()->route('login');
        }

        $usuario = Session::get('usuario');

        if (strtolower($usuario->role ?? '') !== 'admin') {
            abort(403, 'Apenas administradores podem acessar os logs.');
        }

        $query = Log::query()->orderByDesc('created_at');

        // 🔍 Filtros
        if ($request->filled('usuario_nome')) {
            $query->where('usuario_nome', 'like', '%'.$request->get('usuario_nome').'%');
        }

        if ($request->filled('data_de')) {
            $query->whereDate('created_at', '>=', $request->get('data_de'));
        }

        if ($request->filled('data_ate')) {
            $query->whereDate('created_at', '<=', $request->get('data_ate'));
        }

        $logs = $query->paginate(20);

        return view('logs.index', compact('logs'));
    }

    public function show($id)
    {
        if (!Session::has('usuario')) {
            return redirect()->route('login');
        }

        $usuario = Session::get('usuario');

        if (strtolower($usuario->role ?? '') !== 'admin') {
            abort(403, 'Apenas administradores podem acessar os logs.');
        }

        $log = Log::findOrFail($id);

        return view('logs.show', compact('log'));
    }
}
