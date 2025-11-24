<?php

namespace App\Http\Controllers;

use App\Models\Log;
use App\Models\Funcionario;
use Illuminate\Http\Request;
use Carbon\Carbon;

class LogController extends Controller
{
    public function index(Request $request)
    {
        $query = Log::query();

        // Filtros
        if ($request->filled('usuario_id')) {
            $query->where('usuario_id', $request->usuario_id);
        }

        if ($request->filled('model')) {
            $query->where('model', $request->model);
        }

        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        if ($request->filled('data_de')) {
            $query->whereDate('created_at', '>=', $request->data_de);
        }

        if ($request->filled('data_ate')) {
            $query->whereDate('created_at', '<=', $request->data_ate);
        }

        // Dados para os selects
        $models  = Log::distinct()->pluck('model')->filter();
        $actions = Log::distinct()->pluck('action')->filter();
        
        // Funcionários ativos (com ativo = 1)
        $funcionariosAtivos = Funcionario::where('ativo', 1)
            ->orderBy('nome')
            ->get(['id', 'nome']);

        // Paginação: 9 por página
        $logs = $query
            ->orderBy('created_at', 'desc')
            ->paginate(9);

        return view('logs.index', compact('logs', 'models', 'actions', 'funcionariosAtivos'));
    }

    public function show($id)
    {
        $log = Log::findOrFail($id);

        // Normaliza/decodifica o campo details (pode vir como json ou array)
        $details = $log->details ?? null;
        if (is_string($details)) {
            $details = json_decode($details, true);
        }

        if (is_array($details)) {
            $timezone = config('app.timezone', 'America/Sao_Paulo');

            $formatDate = function ($value) use ($timezone) {
                if (empty($value) || !is_string($value)) {
                    return $value;
                }

                try {
                    // tenta identificar ISO / timestamps
                    // se der erro, devolve como veio
                    return Carbon::parse($value)
                        ->timezone($timezone)
                        ->format('d/m/Y H:i:s');
                } catch (\Throwable $e) {
                    return $value;
                }
            };

            // Se você estiver guardando um "timestamp" raiz nos detalhes
            if (!empty($details['timestamp'])) {
                $details['timestamp'] = $formatDate($details['timestamp']);
            }

            // Formata datas dentro de dados antigos/novos (updated_at, created_at, etc.)
            foreach (['dados_antigos', 'dados_novos'] as $grupo) {
                if (!empty($details[$grupo]) && is_array($details[$grupo])) {
                    foreach ($details[$grupo] as $campo => $valor) {
                        // heurística simples: se parecer uma data ISO, converte
                        if (is_string($valor) && preg_match('/\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}/', $valor)) {
                            $details[$grupo][$campo] = $formatDate($valor);
                        }
                    }
                }
            }

            // coloca de volta no model para a view usar normalmente
            $log->details = $details;
        }

        return view('logs.show', compact('log'));
    }
}
