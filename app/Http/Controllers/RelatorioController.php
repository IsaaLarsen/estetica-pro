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
        return view('relatorios.index');
    }

    /** Relatório de agendamentos com filtros (TELA HTML) */
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

        // ================= KPIs / RESUMOS =================

        // Total de agendamentos
        $totalAgendamentos = $registros->count();

        // Quantidade por status
        $porStatus = $registros
            ->groupBy('status')
            ->map(function ($itens) {
                return $itens->count();
            })
            ->toArray();

        // Agendamentos por dia (d/m/Y)
        $porDia = $registros
            ->groupBy(function ($item) {
                return Carbon::parse($item->inicio)->format('d/m/Y');
            })
            ->map(function ($itens) {
                return $itens->count();
            })
            ->sortKeys()
            ->toArray();

        // Top serviços
        $porServico = $registros
            ->groupBy('servico_nome')
            ->map(function ($itens) {
                return $itens->count();
            })
            ->sortDesc()
            ->toArray();

        // Top profissionais
        $porFuncionario = $registros
            ->groupBy('funcionario_nome')
            ->map(function ($itens) {
                return $itens->count();
            })
            ->sortDesc()
            ->toArray();

        // combos
        $funcionarios = DB::table('funcionarios')->select('id', 'nome')->orderBy('nome')->get();
        $servicos     = DB::table('servicos')->select('id', 'nome')->orderBy('nome')->get();

        return view('relatorios.agendamentos', [
            'registros'          => $registros,
            'inicio'             => $inicio,
            'fim'                => $fim,
            'funcionarios'       => $funcionarios,
            'servicos'           => $servicos,

            // filtros (nomes novos, usados no Blade novo)
            'filtroFuncionarioId'=> $funcionario,
            'filtroServicoId'    => $servico,
            'filtroStatus'       => $status,

            // compatibilidade com algum Blade antigo que use ainda esses nomes
            'filtroFunc'         => $funcionario,
            'filtroServico'      => $servico,

            // datas brutas pra manter o value dos inputs
            'dataInicio'         => $dataInicio,
            'dataFim'            => $dataFim,

            // KPIs / resumos
            'totalAgendamentos'  => $totalAgendamentos,
            'porStatus'          => $porStatus,
            'porDia'             => $porDia,
            'porServico'         => $porServico,
            'porFuncionario'     => $porFuncionario,
        ]);
    }

    /** PDF do relatório de agendamentos */
    public function agendamentosPdf(Request $request)
    {
        $usuario = Session::get('usuario');

        $dataInicio  = $request->input('data_inicio');
        $dataFim     = $request->input('data_fim');
        $funcionario = $request->input('funcionario_id');
        $servico     = $request->input('servico_id');
        $status      = $request->input('status');

        $inicio = $dataInicio
            ? Carbon::parse($dataInicio)->startOfDay()
            : Carbon::now()->startOfMonth();

        $fim = $dataFim
            ? Carbon::parse($dataFim)->endOfDay()
            : Carbon::now()->endOfMonth();

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

        // nomes dos filtros para o cabeçalho do PDF
        $filtroFuncNome    = null;
        $filtroServicoNome = null;

        if (!empty($funcionario)) {
            $filtroFuncNome = DB::table('funcionarios')
                ->where('id', $funcionario)
                ->value('nome');
        }

        if (!empty($servico)) {
            $filtroServicoNome = DB::table('servicos')
                ->where('id', $servico)
                ->value('nome');
        }

        $periodo = [
            'inicio' => $inicio,
            'fim'    => $fim,
        ];

        $dados = [
            'usuario'          => $usuario,
            'registros'        => $registros,
            'periodo'          => $periodo,
            'filtroFuncNome'   => $filtroFuncNome,
            'filtroServicoNome'=> $filtroServicoNome,
            'filtroStatus'     => $status,
            'geradoEm'         => Carbon::now(),
        ];

        $pdf = Pdf::loadView('relatorios.agendamentos_pdf', $dados)
            ->setPaper('a4', 'portrait');

        $nomeArquivo = 'relatorio-agendamentos-' . $inicio->format('Ymd') . '-' . $fim->format('Ymd') . '.pdf';

        return $pdf->download($nomeArquivo);
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
            'statusTotais'   => $statusTotais,
        ];

        $pdf = Pdf::loadView('relatorios.comissoes_pdf', $dados)
            ->setPaper('a4', 'portrait');

        $nomeArquivo = 'relatorio-comissoes-' . $inicio->format('Ymd') . '-' . $fim->format('Ymd') . '.pdf';

        return $pdf->download($nomeArquivo);
    }

    /**
     * Relatório de FATURAMENTO (tela HTML)
     * - base em agendas concluídas
     * - filtros: período, funcionário, serviço, meio de pagamento (se existir)
     * - usa o valor do serviço (servicos.valor)
     */
    public function faturamento(Request $request)
    {
        $dataInicio    = $request->input('data_inicio');
        $dataFim       = $request->input('data_fim');
        $funcionarioId = $request->input('funcionario_id');
        $servicoId     = $request->input('servico_id');
        $meioFiltro    = $request->input('meio_pagamento');

        // Período padrão = mês atual
        $inicio = $dataInicio
            ? Carbon::parse($dataInicio)->startOfDay()
            : Carbon::now()->startOfMonth();

        $fim = $dataFim
            ? Carbon::parse($dataFim)->endOfDay()
            : Carbon::now()->endOfMonth();

        // Coluna de meio de pagamento na tabela agendas (se existir)
        $meioPagCol = $this->getAgendamentoMeioPagamentoColumn();

        // Query base: apenas agendamentos CONCLUÍDOS
        $base = DB::table('agendas as a')
            ->join('servicos as s', 's.id', '=', 'a.servico_id')
            ->join('funcionarios as f', 'f.id', '=', 'a.funcionario_id')
            ->leftJoin('clientes as c', 'c.id', '=', 'a.cliente_id')
            ->whereBetween('a.inicio', [$inicio, $fim])
            ->where('a.status', 'concluido');

        if (!empty($funcionarioId)) {
            $base->where('a.funcionario_id', $funcionarioId);
        }

        if (!empty($servicoId)) {
            $base->where('a.servico_id', $servicoId);
        }

        if ($meioPagCol && !empty($meioFiltro)) {
            $base->where("a.$meioPagCol", $meioFiltro);
        }

        // ========= DETALHES =========
        $detalhesSelect = [
            'a.id',
            'a.inicio',
            'a.fim',
            'a.status',
            'f.nome as funcionario_nome',
            's.nome as servico_nome',
            'c.nome as cliente_nome',
            DB::raw('s.valor as valor'),
        ];

        if ($meioPagCol) {
            $detalhesSelect[] = DB::raw("a.$meioPagCol as meio_pagamento");
        }

        $detalhes = (clone $base)
            ->orderBy('a.inicio', 'asc')
            ->get($detalhesSelect);

        // ========= TOTAIS GERAIS =========
        $totalAtendimentos = $detalhes->count();
        $totalFaturamento  = $detalhes->sum('valor');
        $ticketMedioGeral  = $totalAtendimentos > 0
            ? $totalFaturamento / $totalAtendimentos
            : 0;

        $totaisGerais = [
            'total_atendimentos' => $totalAtendimentos,
            'total_faturamento'  => $totalFaturamento,
            'ticket_medio'       => $ticketMedioGeral,
        ];

        // ========= RESUMO POR PROFISSIONAL =========
        $resumoPorProfissional = (clone $base)
            ->select([
                'f.id',
                'f.nome as funcionario_nome',
                DB::raw('COUNT(*) as total_atendimentos'),
                DB::raw('SUM(s.valor) as faturamento'),
                DB::raw('AVG(s.valor) as ticket_medio'),
            ])
            ->groupBy('f.id', 'f.nome')
            ->orderBy('f.nome')
            ->get();

        // ========= FATURAMENTO POR MEIO DE PAGAMENTO =========
        $porMeioPagamento = collect();
        if ($meioPagCol) {
            $porMeioPagamento = (clone $base)
                ->select([
                    DB::raw("COALESCE(a.$meioPagCol, 'Não informado') as meio_pagamento"),
                    DB::raw('COUNT(*) as total_atendimentos'),
                    DB::raw('SUM(s.valor) as faturamento'),
                ])
                ->groupBy(DB::raw("COALESCE(a.$meioPagCol, 'Não informado')"))
                ->orderBy('faturamento', 'desc')
                ->get();

            $totalFatGeral = $porMeioPagamento->sum('faturamento');

            $porMeioPagamento = $porMeioPagamento->map(function ($row) use ($totalFatGeral) {
                $row->participacao = $totalFatGeral > 0
                    ? ($row->faturamento / $totalFatGeral) * 100
                    : 0;
                return $row;
            });
        }

        // Combos para filtros
        $funcionarios = DB::table('funcionarios')->select('id', 'nome')->orderBy('nome')->get();
        $servicos     = DB::table('servicos')->select('id', 'nome')->orderBy('nome')->get();

        $meiosPagamento = collect();
        if ($meioPagCol) {
            $meiosPagamento = DB::table('agendas')
                ->whereNotNull($meioPagCol)
                ->distinct()
                ->orderBy($meioPagCol)
                ->pluck($meioPagCol);
        }

        // valorCol aqui é simbólico: diz apenas que existe um campo "valor" nas coleções
        $valorCol = 'valor';

        return view('relatorios.faturamento', [
            'inicio'               => $inicio,
            'fim'                  => $fim,
            'funcionarios'         => $funcionarios,
            'servicos'             => $servicos,
            'meiosPagamento'       => $meiosPagamento,
            'filtroFunc'           => $funcionarioId,
            'filtroServico'        => $servicoId,
            'filtroMeio'           => $meioFiltro,
            'resumoPorProfissional'=> $resumoPorProfissional,
            'totaisGerais'         => $totaisGerais,
            'porMeioPagamento'     => $porMeioPagamento,
            'detalhes'             => $detalhes,
            'valorCol'             => $valorCol,
            'meioPagCol'           => $meioPagCol,
        ]);
    }

    /**
     * PDF do relatório de FATURAMENTO
     * (usa mesma base de dados da tela, com servicos.valor)
     */
    public function faturamentoPdf(Request $request)
    {
        $usuario      = Session::get('usuario');

        $dataInicio    = $request->input('data_inicio');
        $dataFim       = $request->input('data_fim');
        $funcionarioId = $request->input('funcionario_id');
        $servicoId     = $request->input('servico_id');
        $meioFiltro    = $request->input('meio_pagamento');

        $inicio = $dataInicio
            ? Carbon::parse($dataInicio)->startOfDay()
            : Carbon::now()->startOfMonth();

        $fim = $dataFim
            ? Carbon::parse($dataFim)->endOfDay()
            : Carbon::now()->endOfMonth();

        $periodo = [
            'inicio' => $inicio,
            'fim'    => $fim,
        ];

        $meioPagCol = $this->getAgendamentoMeioPagamentoColumn();

        $base = DB::table('agendas as a')
            ->join('servicos as s', 's.id', '=', 'a.servico_id')
            ->join('funcionarios as f', 'f.id', '=', 'a.funcionario_id')
            ->leftJoin('clientes as c', 'c.id', '=', 'a.cliente_id')
            ->whereBetween('a.inicio', [$inicio, $fim])
            ->where('a.status', 'concluido');

        if (!empty($funcionarioId)) {
            $base->where('a.funcionario_id', $funcionarioId);
        }

        if (!empty($servicoId)) {
            $base->where('a.servico_id', $servicoId);
        }

        if ($meioPagCol && !empty($meioFiltro)) {
            $base->where("a.$meioPagCol", $meioFiltro);
        }

        // Detalhes
        $selectDetalhes = [
            'a.inicio',
            'a.fim',
            'a.status',
            'f.nome as funcionario_nome',
            's.nome as servico_nome',
            'c.nome as cliente_nome',
            DB::raw('s.valor as valor'),
        ];

        if ($meioPagCol) {
            $selectDetalhes[] = DB::raw("a.$meioPagCol as meio_pagamento");
        }

        $detalhes = (clone $base)
            ->orderBy('a.inicio', 'asc')
            ->get($selectDetalhes);

        $totalAtendimentos = $detalhes->count();
        $totalFaturamento  = $detalhes->sum('valor');
        $ticketMedioGeral  = $totalAtendimentos > 0
            ? $totalFaturamento / $totalAtendimentos
            : 0;

        $totaisGerais = [
            'total_atendimentos' => $totalAtendimentos,
            'total_faturamento'  => $totalFaturamento,
            'ticket_medio'       => $ticketMedioGeral,
        ];

        // Resumo por profissional
        $resumoPorProfissional = (clone $base)
            ->select([
                'f.id',
                'f.nome as funcionario_nome',
                DB::raw('COUNT(*) as total_atendimentos'),
                DB::raw('SUM(s.valor) as faturamento'),
                DB::raw('AVG(s.valor) as ticket_medio'),
            ])
            ->groupBy('f.id', 'f.nome')
            ->orderBy('f.nome')
            ->get();

        // Por meio de pagamento
        $porMeioPagamento = collect();
        if ($meioPagCol) {
            $porMeioPagamento = (clone $base)
                ->select([
                    DB::raw("COALESCE(a.$meioPagCol, 'Não informado') as meio_pagamento"),
                    DB::raw('COUNT(*) as total_atendimentos'),
                    DB::raw('SUM(s.valor) as faturamento'),
                ])
                ->groupBy(DB::raw("COALESCE(a.$meioPagCol, 'Não informado')"))
                ->orderBy('faturamento', 'desc')
                ->get();

            $totalFatGeral = $porMeioPagamento->sum('faturamento');

            $porMeioPagamento = $porMeioPagamento->map(function ($row) use ($totalFatGeral) {
                $row->participacao = $totalFatGeral > 0
                    ? ($row->faturamento / $totalFatGeral) * 100
                    : 0;
                return $row;
            });
        }

        // Nome do funcionário/serviço filtrado (pra cabeçalho)
        $filtroFuncNome    = null;
        $filtroServicoNome = null;

        if (!empty($funcionarioId)) {
            $filtroFuncNome = DB::table('funcionarios')
                ->where('id', $funcionarioId)
                ->value('nome');
        }

        if (!empty($servicoId)) {
            $filtroServicoNome = DB::table('servicos')
                ->where('id', $servicoId)
                ->value('nome');
        }

        // valorCol simbólico para a view do PDF, igual à HTML
        $valorCol = 'valor';

        $dados = [
            'usuario'              => $usuario,
            'periodo'              => $periodo,
            'inicio'               => $inicio,
            'fim'                  => $fim,
            'resumoPorProfissional'=> $resumoPorProfissional,
            'totaisGerais'         => $totaisGerais,
            'porMeioPagamento'     => $porMeioPagamento,
            'detalhes'             => $detalhes,
            'valorCol'             => $valorCol,
            'meioPagCol'           => $meioPagCol,
            'geradoEm'             => Carbon::now(),
            'filtroFuncNome'       => $filtroFuncNome,
            'filtroServicoNome'    => $filtroServicoNome,
            'meioFiltro'           => $meioFiltro,
        ];

        $pdf = Pdf::loadView('relatorios.faturamento_pdf', $dados)
            ->setPaper('a4', 'portrait');

        $nomeArquivo = 'relatorio-faturamento-' . $inicio->format('Ymd') . '-' . $fim->format('Ymd') . '.pdf';

        return $pdf->download($nomeArquivo);
    }

    /**
     * Relatório de SERVIÇOS (tela HTML)
     * - mostra quais serviços trazem mais dinheiro
     */
    public function servicos(Request $request)
    {
        $dataInicio = $request->input('data_inicio');
        $dataFim    = $request->input('data_fim');

        // Período padrão = mês atual
        $inicio = $dataInicio
            ? Carbon::parse($dataInicio)->startOfDay()
            : Carbon::now()->startOfMonth();

        $fim = $dataFim
            ? Carbon::parse($dataFim)->endOfDay()
            : Carbon::now()->endOfMonth();

        // Base: apenas atendimentos concluídos
        $base = DB::table('agendas as a')
            ->join('servicos as s', 's.id', '=', 'a.servico_id')
            ->join('funcionarios as f', 'f.id', '=', 'a.funcionario_id')
            ->leftJoin('clientes as c', 'c.id', '=', 'a.cliente_id')
            ->whereBetween('a.inicio', [$inicio, $fim])
            ->where('a.status', 'concluido');

        // ========= RANKING POR SERVIÇO =========
        $servicosResumo = (clone $base)
            ->select([
                's.id',
                's.nome as servico_nome',
                DB::raw('COUNT(*) as total_atendimentos'),
                DB::raw('SUM(s.valor) as faturamento'),
                DB::raw('AVG(s.valor) as ticket_medio'),
            ])
            ->groupBy('s.id', 's.nome')
            ->orderBy('faturamento', 'desc')
            ->get();

        $totalFaturamentoGeral = $servicosResumo->sum('faturamento');

        // adiciona % de participação
        $servicosResumo = $servicosResumo->map(function ($row) use ($totalFaturamentoGeral) {
            $row->participacao = $totalFaturamentoGeral > 0
                ? ($row->faturamento / $totalFaturamentoGeral) * 100
                : 0;
            return $row;
        });

        // ========= DETALHES DOS ATENDIMENTOS =========
        $detalhes = (clone $base)
            ->select([
                'a.inicio',
                'a.fim',
                'c.nome as cliente_nome',
                's.nome as servico_nome',
                'f.nome as funcionario_nome',
                DB::raw('s.valor as valor'),
            ])
            ->orderBy('a.inicio', 'asc')
            ->get();

        // ========= TOTAIS GERAIS =========
        $totalAtendimentos = $detalhes->count();
        $totalFaturamento  = $detalhes->sum('valor');
        $ticketMedioGeral  = $totalAtendimentos > 0
            ? $totalFaturamento / $totalAtendimentos
            : 0;

        $totaisGerais = [
            'total_atendimentos' => $totalAtendimentos,
            'total_faturamento'  => $totalFaturamento,
            'ticket_medio'       => $ticketMedioGeral,
        ];

        // valorCol só indica que existe campo "valor" nas coleções
        $valorCol = 'valor';

        return view('relatorios.servicos', [
            'inicio'         => $inicio,
            'fim'            => $fim,
            'servicosResumo' => $servicosResumo,
            'detalhes'       => $detalhes,
            'totaisGerais'   => $totaisGerais,
            'valorCol'       => $valorCol,
        ]);
    }

    /**
     * PDF do Relatório de SERVIÇOS
     */
    public function servicosPdf(Request $request)
    {
        $usuario = Session::get('usuario');

        $dataInicio = $request->input('data_inicio');
        $dataFim    = $request->input('data_fim');

        $inicio = $dataInicio
            ? Carbon::parse($dataInicio)->startOfDay()
            : Carbon::now()->startOfMonth();

        $fim = $dataFim
            ? Carbon::parse($dataFim)->endOfDay()
            : Carbon::now()->endOfMonth();

        $periodo = [
            'inicio' => $inicio,
            'fim'    => $fim,
        ];

        $base = DB::table('agendas as a')
            ->join('servicos as s', 's.id', '=', 'a.servico_id')
            ->join('funcionarios as f', 'f.id', '=', 'a.funcionario_id')
            ->leftJoin('clientes as c', 'c.id', '=', 'a.cliente_id')
            ->whereBetween('a.inicio', [$inicio, $fim])
            ->where('a.status', 'concluido');

        // Ranking por serviço
        $servicosResumo = (clone $base)
            ->select([
                's.id',
                's.nome as servico_nome',
                DB::raw('COUNT(*) as total_atendimentos'),
                DB::raw('SUM(s.valor) as faturamento'),
                DB::raw('AVG(s.valor) as ticket_medio'),
            ])
            ->groupBy('s.id', 's.nome')
            ->orderBy('faturamento', 'desc')
            ->get();

        $totalFaturamentoGeral = $servicosResumo->sum('faturamento');

        $servicosResumo = $servicosResumo->map(function ($row) use ($totalFaturamentoGeral) {
            $row->participacao = $totalFaturamentoGeral > 0
                ? ($row->faturamento / $totalFaturamentoGeral) * 100
                : 0;
            return $row;
        });

        // Detalhes para o PDF
        $detalhes = (clone $base)
            ->select([
                'a.inicio',
                'c.nome as cliente_nome',
                's.nome as servico_nome',
                'f.nome as funcionario_nome',
                DB::raw('s.valor as valor'),
            ])
            ->orderBy('a.inicio', 'asc')
            ->get();

        $totalAtendimentos = $detalhes->count();
        $totalFaturamento  = $detalhes->sum('valor');
        $ticketMedioGeral  = $totalAtendimentos > 0
            ? $totalFaturamento / $totalAtendimentos
            : 0;

        $totaisGerais = [
            'total_atendimentos' => $totalAtendimentos,
            'total_faturamento'  => $totalFaturamento,
            'ticket_medio'       => $ticketMedioGeral,
        ];

        $dados = [
            'usuario'        => $usuario,
            'periodo'        => $periodo,
            'servicosResumo' => $servicosResumo,
            'detalhes'       => $detalhes,
            'totaisGerais'   => $totaisGerais,
            'geradoEm'       => Carbon::now(),
        ];

        $pdf = Pdf::loadView('relatorios.servicos_pdf', $dados)
            ->setPaper('a4', 'portrait');

        $nomeArquivo = 'relatorio-servicos-' . $inicio->format('Ymd') . '-' . $fim->format('Ymd') . '.pdf';

        return $pdf->download($nomeArquivo);
    }

    /**
     * Descobre qual coluna numérica de valor existe na tabela comissoes
     * Ex: valor, valor_comissao, valor_total, valor_bruto...
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

    /**
     * Tenta descobrir a coluna de valor na tabela agendas
     * (mantida caso você use em outra coisa depois)
     */
    private function getAgendamentoValorColumn(): ?string
    {
        if (!Schema::hasTable('agendas')) {
            return null;
        }

        $possiveis = ['valor_total', 'valor', 'preco', 'valor_cobrado'];

        foreach ($possiveis as $col) {
            if (Schema::hasColumn('agendas', $col)) {
                return $col;
            }
        }

        return null;
    }

    /**
     * Tenta descobrir a coluna de meio de pagamento na tabela agendas
     */
    private function getAgendamentoMeioPagamentoColumn(): ?string
    {
        if (!Schema::hasTable('agendas')) {
            return null;
        }

        $possiveis = ['meio_pagamento', 'forma_pagamento'];

        foreach ($possiveis as $col) {
            if (Schema::hasColumn('agendas', $col)) {
                return $col;
            }
        }

        return null;
    }
}
