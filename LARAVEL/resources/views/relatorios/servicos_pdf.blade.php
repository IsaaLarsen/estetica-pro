<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Relatório de Serviços</title>

    <style>
        *{ box-sizing:border-box; margin:0; padding:0; }
        body{
            font-family: DejaVu Sans, Arial, Helvetica, sans-serif;
            font-size:11px;
            color:#111827;
            padding:10px 18px;
        }

        .header{
            padding-bottom:10px;
            border-bottom:2px solid #ec4899;
            margin-bottom:14px;
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

        .table{
            width:100%;
            border-collapse:collapse;
            margin-top:10px;
        }
        .table th, .table td{
            border:1px solid #e5e7eb;
            padding:6px 5px;
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

        .footer{
            margin-top:14px;
            font-size:10px;
            color:#6b7280;
            border-top:1px solid #e5e7eb;
            padding-top:6px;
            text-align:center;
        }

        .totais{
            margin-top:10px;
            font-size:11px;
        }
        .totais-list{
            margin-top:4px;
            font-size:10px;
        }

        .badge-servico{
            display:inline-block;
            padding:2px 6px;
            border-radius:999px;
            font-size:9px;
            font-weight:bold;
            background:#eef2ff;
            color:#3730a3;
        }

        .section-title{
            font-weight:bold;
            margin-top:10px;
            font-size:12px;
            color:#111827;
        }
    </style>
</head>
<body>

@php
    use Carbon\Carbon;

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

    $totalAtend = (int)($totaisGerais['total_atendimentos'] ?? 0);
    $totalFat   = (float)($totaisGerais['total_faturamento'] ?? 0);
    $ticketMed  = (float)($totaisGerais['ticket_medio'] ?? 0);
@endphp

<div class="header">
    <div class="header-info">
        <div class="title">Relatório de Serviços</div>
        <div class="subtitle">Estética PRO — Sistema de Gestão</div>

        <div class="meta">
            <span><strong>Período:</strong> {{ $inicioPeriodo }} até {{ $fimPeriodo }}</span>
            <span><strong>Gerado em:</strong> {{ $geradoEm->format('d/m/Y H:i') }}</span>
            @if(!empty($usuario?->nome))
                <span><strong>Usuário:</strong> {{ $usuario->nome }}</span>
            @endif
        </div>
    </div>
</div>

{{-- RANKING DE SERVIÇOS --}}
<div class="section-title">1. Ranking de serviços (por faturamento)</div>

<table class="table">
    <thead>
        <tr>
            <th style="width:34%;">Serviço</th>
            <th style="width:16%;" class="text-right">Atendimentos</th>
            <th style="width:20%;" class="text-right">Faturamento (R$)</th>
            <th style="width:15%;" class="text-right">Ticket médio (R$)</th>
            <th style="width:15%;" class="text-right">Participação (%)</th>
        </tr>
    </thead>
    <tbody>
        @forelse($servicosResumo as $s)
            <tr>
                <td>
                    <span class="badge-servico">{{ $s->servico_nome }}</span>
                </td>
                <td class="text-right">
                    {{ number_format($s->total_atendimentos ?? 0, 0, ',', '.') }}
                </td>
                <td class="text-right">
                    {{ number_format($s->faturamento ?? 0, 2, ',', '.') }}
                </td>
                <td class="text-right">
                    {{ number_format($s->ticket_medio ?? 0, 2, ',', '.') }}
                </td>
                <td class="text-right">
                    {{ number_format($s->participacao ?? 0, 1, ',', '.') }}%
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="5">Nenhum serviço encontrado para o período informado.</td>
            </tr>
        @endforelse
    </tbody>
</table>

{{-- TOTAIS GERAIS --}}
<div class="totais">
    <strong>Totais gerais do período:</strong><br>
    • Atendimentos concluídos: {{ number_format($totalAtend, 0, ',', '.') }}<br>
    • Faturamento total: R$ {{ number_format($totalFat, 2, ',', '.') }}<br>
    • Ticket médio por atendimento: R$ {{ number_format($ticketMed, 2, ',', '.') }}
</div>

{{-- DETALHES (opcional) --}}
@if($detalhes->count())
    <div class="section-title">2. Detalhamento dos atendimentos</div>

    <table class="table">
        <thead>
            <tr>
                <th style="width:18%;">Data/Hora</th>
                <th style="width:26%;">Cliente</th>
                <th style="width:26%;">Serviço</th>
                <th style="width:20%;">Profissional</th>
                <th style="width:10%;" class="text-right">Valor (R$)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($detalhes as $d)
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
                </tr>
            @endforeach
        </tbody>
    </table>
@endif

<div class="footer">
    Documento gerado automaticamente pelo sistema Estética PRO.
</div>

</body>
</html>
