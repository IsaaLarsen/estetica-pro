<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class RelatorioController extends Controller
{
    /** Tela inicial de relatórios (menu) */
    public function index()
    {
        // Aqui pode ter cards com links para cada relatório
        return view('relatorios.index');
    }

    /** Relatório de agendamentos com filtros */
    public function agendamentos(Request $request)
    {
        $dataInicio  = $request->input('data_inicio');
        $dataFim     = $request->input('data_fim');
        $funcionario = $request->input('funcionario_id');
        $servico     = $request->input('servico_id');
        $status      = $request->input('status');

        // Defaults: mês atual
        $inicio = $dataInicio
            ? Carbon::parse($dataInicio)->startOfDay()
            : Carbon::now()->startOfMonth();

        $fim = $dataFim
            ? Carbon::parse($dataFim)->endOfDay()
            : Carbon::now()->endOfMonth();

        // monta query base
        $q = DB::table('agendas as a')
            ->leftJoin('clientes as c', 'c.id', '=', 'a.cliente_id')
            ->leftJoin('servicos as s', 's.id', '=', 'a.servico_id')
            ->leftJoin('funcionarios as f', 'f.id', '=', 'a.funcionario_id')
            ->whereBetween('a.inicio', [$inicio, $fim]);

        if (!empty($funcionario)) {
            $q->where('a.funcionario_id', $funcionario);
        }

        if (!empty($servico)) {
            $q->where('a.servico_id', $servico);
        }

        if (!empty($status)) {
            $q->where('a.status', $status);
        }

        $registros = $q->orderBy('a.inicio', 'asc')
            ->get([
                'a.inicio',
                'a.fim',
                'a.status',
                'c.nome as cliente_nome',
                's.nome as servico_nome',
                'f.nome as funcionario_nome',
            ]);

        // combos
        $funcionarios = DB::table('funcionarios')->select('id', 'nome')->orderBy('nome')->get();
        $servicos     = DB::table('servicos')->select('id', 'nome')->orderBy('nome')->get();

        return view('relatorios.agendamentos', [
            'registros'      => $registros,
            'inicio'         => $inicio,
            'fim'            => $fim,
            'funcionarios'   => $funcionarios,
            'servicos'       => $servicos,
            'filtroFunc'     => $funcionario,
            'filtroServico'  => $servico,
            'filtroStatus'   => $status,
        ]);
    }

    /** Relatório de comissões (tela HTML) */
    public function comissoes(Request $request)
    {
        // Filtros de data (default = mês atual)
        $dataInicio    = $request->input('data_inicio');
        $dataFim       = $request->input('data_fim');
        $funcionarioId = $request->input('funcionario_id');

        $inicio = $dataInicio
            ? Carbon::parse($dataInicio)->startOfDay()
            : Carbon::now()->startOfMonth();

        $fim = $dataFim
            ? Carbon::parse($dataFim)->endOfDay()
            : Carbon::now()->endOfMonth();

        // descobre qual coluna de valor existe na tabela comissoes
        $valorCol = $this->getComissaoValorColumn();

        $query = DB::table('comissoes as c')
            ->leftJoin('funcionarios as f', 'f.id', '=', 'c.funcionario_id')
            ->leftJoin('agendas as a', 'a.id', '=', 'c.agenda_id')
            ->leftJoin('servicos as s', 's.id', '=', 'a.servico_id')
            ->whereBetween('c.created_at', [$inicio, $fim]);

        if (!empty($funcionarioId)) {
            $query->where('c.funcionario_id', $funcionarioId);
        }

        $selects = [
            DB::raw('c.created_at as data'),
            'c.status',
            'f.nome as funcionario_nome',
            's.nome as servico_nome',
        ];

        if ($valorCol) {
            $selects[] = DB::raw("c.$valorCol as valor");
        }

        $registros = $query
            ->orderBy('c.created_at', 'asc')
            ->get($selects);

        // total geral
        $total = 0;
        if ($valorCol) {
            $total = $registros->sum('valor');
        }

        // combo de funcionários
        $funcionarios = DB::table('funcionarios')
            ->select('id', 'nome')
            ->orderBy('nome')
            ->get();

        return view('relatorios.comissoes', [
            'registros'      => $registros,
            'total'          => $total,
            'inicio'         => $inicio,
            'fim'            => $fim,
            'funcionarios'   => $funcionarios,
            'filtroFunc'     => $funcionarioId,
            'valorCol'       => $valorCol,
        ]);
    }

    /** PDF do relatório de comissões */
    public function comissoesPdf(Request $request)
    {
        // usuário logado (para mostrar no cabeçalho do PDF)
        $usuario = Session::get('usuario');

        // Filtros vindos da tela
        $dataInicio    = $request->input('data_inicio');
        $dataFim       = $request->input('data_fim');
        $funcionarioId = $request->input('funcionario_id');

        // Mesma lógica de período usada na tela HTML
        $inicio = $dataInicio
            ? Carbon::parse($dataInicio)->startOfDay()
            : Carbon::now()->startOfMonth();

        $fim = $dataFim
            ? Carbon::parse($dataFim)->endOfDay()
            : Carbon::now()->endOfMonth();

        // Array de período (usado no Blade do PDF)
        $periodo = [
            'inicio' => $inicio,
            'fim'    => $fim,
        ];

        // Coluna de valor
        $valorCol = $this->getComissaoValorColumn();

        $query = DB::table('comissoes as c')
            ->leftJoin('funcionarios as f', 'f.id', '=', 'c.funcionario_id')
            ->leftJoin('agendas as a', 'a.id', '=', 'c.agenda_id')
            ->leftJoin('servicos as s', 's.id', '=', 'a.servico_id')
            ->whereBetween('c.created_at', [$inicio, $fim]);

        if (!empty($funcionarioId)) {
            $query->where('c.funcionario_id', $funcionarioId);
        }

        $selects = [
            DB::raw('c.created_at as data'),
            'c.status',
            'f.nome as funcionario_nome',
            's.nome as servico_nome',
        ];

        if ($valorCol) {
            $selects[] = DB::raw("c.$valorCol as valor");
        }

        $registros = $query
            ->orderBy('c.created_at', 'asc')
            ->get($selects);

        // total geral
        $total = 0;
        if ($valorCol) {
            $total = $registros->sum('valor');
        }

        // 👉 Totais por status (valor em R$ por status)
        $statusTotais = [
            'pago'     => 0.0,
            'pendente' => 0.0,
        ];

        foreach ($registros as $linha) {
            $status = strtolower($linha->status ?? 'pendente');
            $valor  = (float)($linha->valor ?? 0);

            if (isset($statusTotais[$status])) {
                $statusTotais[$status] += $valor;
            }
        }

        // Nome do funcionário filtrado (se tiver)
        $filtroFuncNome = null;
        if (!empty($funcionarioId)) {
            $filtroFuncNome = DB::table('funcionarios')
                ->where('id', $funcionarioId)
                ->value('nome');
        }

        $dados = [
            'usuario'        => $usuario,
            'registros'      => $registros,
            'total'          => $total,
            'periodo'        => $periodo,
            'filtroFuncNome' => $filtroFuncNome,
            'geradoEm'       => Carbon::now(),
            'statusTotais'   => $statusTotais, // 👈 enviado pro Blade
        ];

        $pdf = Pdf::loadView('relatorios.comissoes_pdf', $dados)
            ->setPaper('a4', 'portrait');

        $nomeArquivo = 'relatorio-comissoes-' . $inicio->format('Ymd') . '-' . $fim->format('Ymd') . '.pdf';

        return $pdf->download($nomeArquivo);
    }

    /**
     * Descobre qual coluna numérica de valor existe na tabela comissoes
     * Ex: valor, valor_comissao, valor_total...
     */
    private function getComissaoValorColumn(): ?string
    {
        if (!Schema::hasTable('comissoes')) {
            return null;
        }

        $possiveis = ['valor', 'valor_comissao', 'valor_total', 'valor_bruto'];

        foreach ($possiveis as $col) {
            if (Schema::hasColumn('comissoes', $col)) {
                return $col;
            }
        }

        return null; // sem coluna de valor
    }
}
