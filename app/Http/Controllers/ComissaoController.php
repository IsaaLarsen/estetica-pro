<?php

namespace App\Http\Controllers;

use App\Models\Comissao;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;

class ComissaoController extends Controller
{
   public function index(Request $request)
{
    // Base: joins + filtros (NÃO coloca select aqui)
    $base = \App\Models\Comissao::query()
        ->join('agendas', 'agendas.id', '=', 'comissoes.agenda_id')
        ->join('funcionarios', 'funcionarios.id', '=', 'comissoes.funcionario_id')
        ->join('servicos', 'servicos.id', '=', 'comissoes.servico_id')
        ->join('clientes', 'clientes.id', '=', 'agendas.cliente_id');

    // Filtros
    if ($request->filled('funcionario_id')) {
        $base->where('comissoes.funcionario_id', $request->funcionario_id);
    }
    if ($request->filled('status')) {
        $base->where('comissoes.status', $request->status);
    }
    if ($request->filled('de')) {
        $base->whereDate('agendas.inicio', '>=', \Carbon\Carbon::parse($request->de)->toDateString());
    }
    if ($request->filled('ate')) {
        $base->whereDate('agendas.inicio', '<=', \Carbon\Carbon::parse($request->ate)->toDateString());
    }

    // LISTA (aqui sim seleciona as colunas "normais")
    $comissoes = (clone $base)
        ->select(
            'comissoes.*',
            'funcionarios.nome as funcionario_nome',
            'servicos.nome as servico_nome',
            'clientes.nome as cliente_nome',
            'agendas.inicio as data_atendimento'
        )
        ->orderByDesc('agendas.inicio')
        ->paginate(20)
        ->withQueryString();

    // TOTAIS (somente agregações; NADA de comissoes.* aqui)
    $totais = (clone $base)
        ->selectRaw("SUM(CASE WHEN comissoes.status = 'pendente' THEN comissoes.valor_comissao ELSE 0 END) AS pendente")
        ->selectRaw("SUM(CASE WHEN comissoes.status = 'pago' THEN comissoes.valor_comissao ELSE 0 END) AS pago")
        ->selectRaw("SUM(comissoes.valor_comissao) AS total")
        ->first();

    $funcionarios = \DB::table('funcionarios')->where('ativo', 1)->orderBy('nome')->get();

    return view('comissoes.index', [
        'usuario'      => \Illuminate\Support\Facades\Session::get('usuario'),
        'comissoes'    => $comissoes,
        'totais'       => $totais,
        'funcionarios' => $funcionarios,
    ]);
}
    public function marcarPago($id)
    {
        $c = Comissao::findOrFail($id);

        // Evita reprocessar
        if ($c->status === 'pago') {
            return back()->with('success', 'Comissão já estava paga.');
        }

        $c->update([
            'status' => 'pago',
            'pago_em' => now(),
        ]);

        return back()->with('success', 'Comissão marcada como paga.');
    }

    public function estornar($id)
    {
        $c = Comissao::findOrFail($id);

        // Evita reprocessar
        if ($c->status === 'estornado') {
            return back()->with('success', 'Comissão já estava estornada.');
        }

        $c->update([
            'status' => 'estornado',
            'pago_em' => null,
        ]);

        return back()->with('success', 'Comissão estornada.');
    }
}
