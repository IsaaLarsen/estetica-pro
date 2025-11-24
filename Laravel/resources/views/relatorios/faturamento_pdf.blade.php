<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Relatório de Faturamento</title>

    <style>
        *{ box-sizing:border-box; margin:0; padding:0; }
        body{
            font-family: DejaVu Sans, Arial, Helvetica, sans-serif;
            font-size:11px;
            color:#111827;
            padding:10px 18px;
        }

        /* CABEÇALHO */
        .header{
            padding-bottom:10px;
            border-bottom:2px solid #ec4899;
            margin-bottom:14px;
            position:relative; /* para posicionar a logo */
        }

        /* LOGO */
        .logo {
            width:80px;
            height:auto;
            position:absolute;
            top:-5px;
            right:0;
            display:block;
        }

        .header-info .title{
            font-size:20px;
            font-weight:bold;
            color:#ec4899;
            margin-bottom:2px;
        }
        .header-info .subtitle{
            font-size:12px;
            color:#6b7280;
        }
        .meta{
            margin-top:6px;
            font-size:10px;
            color:#4b5563;
        }
        .meta span{ display:inline-block; margin-right:16px; }

        /* SEÇÃO / BLOCOS */
        .section-title{
            font-size:13px;
            font-weight:bold;
            margin-top:10px;
            margin-bottom:4px;
            color:#111827;
        }
        .section-subtitle{
            font-size:10px;
            color:#6b7280;
            margin-bottom:4px;
        }

        /* TABELA GENÉRICA */
        .table{
            width:100%;
            border-collapse:collapse;
            margin-top:6px;
            margin-bottom:10px;
        }
        .table th, .table td{
            border:1px solid #e5e7eb;
            padding:5px 4px;
            text-align:left;
        }
        .table thead th{
            background:#f9fafb;
            font-weight:bold;
            font-size:10px;
        }
        .table tbody tr:nth-child(even){
            background:#fdf2f8;
        }
        .text-right{text-align:right;}
        .text-center{text-align:center;}

        /* BADGES */
        .badge{
            display:inline-block;
            padding:2px 6px;
            border-radius:999px;
            font-size:9px;
            font-weight:bold;
        }
        .badge-meio{
            background:#eef2ff;
            color:#3730a3;
        }

        /* CARDS TOTAIS */
        .totais-grid{
            width:100%;
            margin-top:4px;
            margin-bottom:8px;
        }
        .totais-grid td{
            vertical-align:top;
            padding-right:10px;
            font-size:11px;
        }
        .totais-card{
            border:1px solid #e5e7eb;
            border-radius:6px;
            padding:6px 8px;
        }
        .totais-label{
            font-size:10px;
            color:#6b7280;
            margin-bottom:2px;
        }
        .totais-value{
            font-size:13px;
            font-weight:bold;
            color:#ec4899;
        }
        .totais-hint{
            font-size:9px;
            color:#9ca3af;
            margin-top:2px;
        }

        /* RODAPÉ */
        .footer{
            margin-top:10px;
            font-size:10px;
            color:#6b7280;
            border-top:1px solid #e5e7eb;
            padding-top:6px;
            text-align:center;
        }
    </style>
</head>
<body>

@php
    use Carbon\Carbon;

    // Normaliza datas do período
    $inicioPeriodo = $periodo['inicio'] ?? null;
    $fimPeriodo    = $periodo['fim']    ?? null;

    if ($inicioPeriodo instanceof Carbon) {
        $inicioPeriodo = $inicioPeriodo->format('d/m/Y');
    }

    if ($fimPeriodo instanceof Carbon) {
        $fimPeriodo = $fimPeriodo->format('d/m/Y');
    }

    $inicioPeriodo = $inicioPeriodo ?: '-';
    $fimPeriodo    = $fimPeriodo    ?: '-';

    // Totais gerais
    $totalAtendimentos = (float)($totaisGerais['total_atendimentos'] ?? 0);
    $totalFaturamento  = (float)($totaisGerais['total_faturamento']  ?? 0);
    $ticketMedio       = (float)($totaisGerais['ticket_medio']       ?? 0);

    // Detecta se temos meio de pagamento nos detalhes
    $primeiroDet = $detalhes->first();
    $temMeioPagamento = $primeiroDet && isset($primeiroDet->meio_pagamento);

    // Texto dos filtros
    $filtroFuncTxt    = $filtroFuncNome    ?? null;
    $filtroServicoTxt = $filtroServicoNome ?? null;
    $filtroMeioTxt    = $meioFiltro        ?? null;
@endphp

<!-- ==========================
          HEADER
     ========================== -->
<div class="header">
    <div class="header-info">
        <div class="title">Relatório de Faturamento</div>
        <div class="subtitle">Estética PRO — Sistema de Gestão</div>

        <div class="meta">
            <span><strong>Período:</strong> {{ $inicioPeriodo }} até {{ $fimPeriodo }}</span>
            <span><strong>Gerado em:</strong> {{ ($geradoEm ?? now())->format('d/m/Y H:i') }}</span>
            @if(!empty($usuario?->nome))
                <span><strong>Usuário:</strong> {{ $usuario->nome }}</span>
            @endif
            @if(!empty($filtroFuncTxt))
                <span><strong>Profissional:</strong> {{ $filtroFuncTxt }}</span>
            @endif
            @if(!empty($filtroServicoTxt))
                <span><strong>Serviço:</strong> {{ $filtroServicoTxt }}</span>
            @endif
            @if(!empty($filtroMeioTxt))
                <span><strong>Meio de pagamento:</strong> {{ $filtroMeioTxt }}</span>
            @endif
        </div>
    </div>

    <!-- LOGO canto superior direito -->
    <img src="{{ public_path('image/logoEP.png') }}" class="logo" alt="Logo Estética PRO">
</div>

<!-- ==========================
          TOTAIS GERAIS
     ========================== -->
<div class="section-title">Resumo geral do período</div>
<div class="section-subtitle">
    Considera apenas agendamentos com status <strong>concluído</strong>.
</div>

<table class="totais-grid">
    <tr>
        <td style="width:33%;">
            <div class="totais-card">
                <div class="totais-label">Total de atendimentos concluídos</div>
                <div class="totais-value">
                    {{ number_format($totalAtendimentos, 0, ',', '.') }}
                </div>
                <div class="totais-hint">
                    Quantidade de atendimentos concluídos no intervalo selecionado.
                </div>
            </div>
        </td>
        <td style="width:33%;">
            <div class="totais-card">
                <div class="totais-label">Faturamento total</div>
                <div class="totais-value">
                    R$ {{ number_format($totalFaturamento, 2, ',', '.') }}
                </div>
                <div class="totais-hint">
                    Soma dos valores de serviço de todos os atendimentos concluídos.
                </div>
            </div>
        </td>
        <td style="width:33%;">
            <div class="totais-card">
                <div class="totais-label">Ticket médio</div>
                <div class="totais-value">
                    R$ {{ number_format($ticketMedio, 2, ',', '.') }}
                </div>
                <div class="totais-hint">
                    Faturamento total dividido pelo número de atendimentos concluídos.
                </div>
            </div>
        </td>
    </tr>
</table>

<!-- ==========================
     FATURAMENTO POR PROFISSIONAL
     ========================== -->
<div class="section-title">Faturamento por profissional</div>
<div class="section-subtitle">
    Visão consolidada por colaborador — apenas atendimentos concluídos no período.
</div>

<table class="table">
    <thead>
        <tr>
            <th style="width:40%;">Profissional</th>
            <th style="width:20%;" class="text-center">Atendimentos</th>
            <th style="width:20%;" class="text-right">Faturamento (R$)</th>
            <th style="width:20%;" class="text-right">Ticket médio (R$)</th>
        </tr>
    </thead>
    <tbody>
        @forelse($resumoPorProfissional as $row)
            <tr>
                <td>{{ $row->funcionario_nome }}</td>
                <td class="text-center">
                    {{ number_format($row->total_atendimentos ?? 0, 0, ',', '.') }}
                </td>
                <td class="text-right">
                    {{ number_format($row->faturamento ?? 0, 2, ',', '.') }}
                </td>
                <td class="text-right">
                    {{ number_format($row->ticket_medio ?? 0, 2, ',', '.') }}
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="4">Nenhum atendimento encontrado para os filtros informados.</td>
            </tr>
        @endforelse
    </tbody>
</table>

<!-- ==========================
     POR MEIO DE PAGAMENTO
     ========================== -->
@if($porMeioPagamento && $porMeioPagamento->count())
    <div class="section-title">Faturamento por meio de pagamento</div>
    <div class="section-subtitle">
        Distribuição do faturamento entre os meios de pagamento utilizados nos atendimentos concluídos.
    </div>

    <table class="table">
        <thead>
            <tr>
                <th style="width:40%;">Meio de pagamento</th>
                <th style="width:20%;" class="text-center">Atendimentos</th>
                <th style="width:20%;" class="text-right">Faturamento (R$)</th>
                <th style="width:20%;" class="text-right">Participação (%)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($porMeioPagamento as $row)
                <tr>
                    <td>
                        <span class="badge badge-meio">
                            {{ $row->meio_pagamento ?? 'Não informado' }}
                        </span>
                    </td>
                    <td class="text-center">
                        {{ number_format($row->total_atendimentos ?? 0, 0, ',', '.') }}
                    </td>
                    <td class="text-right">
                        {{ number_format($row->faturamento ?? 0, 2, ',', '.') }}
                    </td>
                    <td class="text-right">
                        {{ number_format($row->participacao ?? 0, 1, ',', '.') }}%
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4">Nenhuma informação de meio de pagamento disponível para este período.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
@endif

<!-- ==========================
     DETALHAMENTO DOS ATENDIMENTOS
     ========================== -->
<div class="section-title">Detalhamento dos atendimentos</div>
<div class="section-subtitle">
    Lista dos atendimentos concluídos dentro do período filtrado.
</div>

<table class="table">
    <thead>
        <tr>
            <th style="width:16%;">Data/Hora</th>
            <th style="width:24%;">Cliente</th>
            <th style="width:22%;">Serviço</th>
            <th style="width:18%;">Profissional</th>
            <th style="width:10%;" class="text-right">Valor (R$)</th>
            @if($temMeioPagamento)
                <th style="width:10%;">Meio pag.</th>
            @endif
        </tr>
    </thead>
    <tbody>
        @forelse($detalhes as $d)
            @php
                $dataHora = $d->inicio
                    ? Carbon::parse($d->inicio)->format('d/m/Y H:i')
                    : '-';
            @endphp
            <tr>
                <td>{{ $dataHora }}</td>
                <td>{{ $d->cliente_nome ?? '-' }}</td>
                <td>{{ $d->servico_nome ?? '-' }}</td>
                <td>{{ $d->funcionario_nome ?? '-' }}</td>
                <td class="text-right">
                    {{ number_format($d->valor ?? 0, 2, ',', '.') }}
                </td>
                @if($temMeioPagamento)
                    <td>{{ $d->meio_pagamento ?? '—' }}</td>
                @endif
            </tr>
        @empty
            <tr>
                <td colspan="{{ $temMeioPagamento ? 6 : 5 }}">
                    Nenhum atendimento encontrado para os filtros informados.
                </td>
            </tr>
        @endforelse
    </tbody>
</table>

<!-- ==========================
            RODAPÉ
     ========================== -->
<div class="footer">
    Documento gerado automaticamente pelo sistema Estética PRO.
</div>

</body>
</html>
