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
        $base = Comissao::query()
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
            $dataDe = Carbon::parse($request->de)->toDateString();
            $base->whereDate('agendas.inicio', '>=', $dataDe);
        }

        if ($request->filled('ate')) {
            $dataAte = Carbon::parse($request->ate)->toDateString();
            $base->whereDate('agendas.inicio', '<=', $dataAte);
        }

        // LISTA (colunas normais)
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

        // TOTAIS (somente agregações)
        $totais = (clone $base)
            ->selectRaw("
                SUM(
                    CASE WHEN comissoes.status = 'pendente'
                         THEN comissoes.valor_comissao
                         ELSE 0
                    END
                ) AS pendente
            ")
            ->selectRaw("
                SUM(
                    CASE WHEN comissoes.status = 'pago'
                         THEN comissoes.valor_comissao
                         ELSE 0
                    END
                ) AS pago
            ")
            ->selectRaw("SUM(comissoes.valor_comissao) AS total")
            ->first();

        $funcionarios = DB::table('funcionarios')
            ->where('ativo', 1)
            ->orderBy('nome')
            ->get();

        return view('comissoes.index', [
            'usuario'      => Session::get('usuario'),
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
            'status'  => 'pago',
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
            'status'  => 'estornado',
            'pago_em' => null,
        ]);

        return back()->with('success', 'Comissão estornada.');
    }

    /**
     * Recalcula TODAS as comissões com base nos dados atuais dos serviços.
     * - Usa servicos.valor e servicos.comissao_percent
     * - Não altera status (pendente/pago/estornado)
     */
    public function recalcularTodas(Request $request)
    {
        DB::transaction(function () {
            $itens = DB::table('comissoes')
                ->join('servicos', 'servicos.id', '=', 'comissoes.servico_id')
                ->select(
                    'comissoes.id',
                    'servicos.valor as servico_valor',
                    'servicos.comissao_percent as servico_percent'
                )
                // se quiser evitar mexer nas estornadas, descomenta a linha abaixo:
                //->where('comissoes.status', '!=', 'estornado')
                ->get();

            foreach ($itens as $row) {
                // Normaliza valor do serviço
                $valorBruto = $row->servico_valor ?? 0;
                if (is_string($valorBruto)) {
                    // "1.234,56" -> "1234.56"
                    $valorBruto = str_replace('.', '', $valorBruto);
                    $valorBruto = str_replace(',', '.', $valorBruto);
                }
                $valorServico = (float) $valorBruto;

                // Normaliza % de comissão
                $percentualBruto = $row->servico_percent ?? 0;
                if (is_string($percentualBruto)) {
                    // "40,00" ou "40%" -> "40.00"
                    $percentualBruto = str_replace(['%', '.'], ['', ''], $percentualBruto);
                    $percentualBruto = str_replace(',', '.', $percentualBruto);
                }
                $percentual = (float) $percentualBruto;

                if ($percentual <= 0) {
                    // fallback de segurança, mas idealmente sempre vem certo
                    $percentual = 40.0;
                }

                $valorComissao = round($valorServico * ($percentual / 100), 2);

                Comissao::where('id', $row->id)->update([
                    'valor_servico'  => $valorServico,
                    'percentual'     => $percentual,
                    'valor_comissao' => $valorComissao,
                ]);
            }
        });

        return back()->with('success', 'Todas as comissões foram recalculadas com sucesso.');
    }
}
