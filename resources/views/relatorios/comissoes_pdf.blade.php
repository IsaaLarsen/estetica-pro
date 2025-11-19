<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Relatório de Comissões</title>

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

        /* TABELA */
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

        /* BADGES */
        .badge{
            display:inline-block;
            padding:2px 6px;
            border-radius:999px;
            font-size:9px;
            font-weight:bold;
        }
        .badge-pago{
            background:#dcfce7;
            color:#166534;
        }
        .badge-pendente{
            background:#fef3c7;
            color:#92400e;
        }

        /* RODAPÉ / TOTAIS */
        .footer{
            margin-top:14px;
            font-size:10px;
            color:#6b7280;
            border-top:1px solid #e5e7eb;
            padding-top:6px;
            text-align:center;
        }
        .totais{
            margin-top:8px;
            font-size:11px;
        }
        .totais-list{
            margin-top:4px;
            font-size:10px;
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

    $totalPago     = (float)($statusTotais['pago']     ?? 0);
    $totalPendente = (float)($statusTotais['pendente'] ?? 0);
@endphp

<!-- ==========================
          HEADER PROFISSIONAL
     ========================== -->
<div class="header">
    <div class="header-info">
        <div class="title">Relatório de Comissões</div>
        <div class="subtitle">Estética PRO — Sistema de Gestão</div>

        <div class="meta">
            <span><strong>Período:</strong> {{ $inicioPeriodo }} até {{ $fimPeriodo }}</span>
            <span><strong>Gerado em:</strong> {{ now()->format('d/m/Y H:i') }}</span>
            @if(!empty($usuario?->nome))
                <span><strong>Usuário:</strong> {{ $usuario->nome }}</span>
            @endif
            @if(!empty($filtroFuncNome))
                <span><strong>Funcionário:</strong> {{ $filtroFuncNome }}</span>
            @endif
        </div>
    </div>
</div>

<!-- ==========================
            TABELA
     ========================== -->
<table class="table">
    <thead>
        <tr>
            <th style="width:14%;">Data</th>
            <th style="width:26%;">Funcionário</th>
            <th style="width:30%;">Serviço</th>
            <th style="width:15%;" class="text-right">Valor (R$)</th>
            <th style="width:15%;">Status</th>
        </tr>
    </thead>
    <tbody>

        @forelse($registros as $linha)
            @php
                $data = $linha->data
                    ? Carbon::parse($linha->data)->format('d/m/Y')
                    : '-';

                $status = strtolower($linha->status ?? 'pendente');
                $isPago = $status === 'pago';
            @endphp

            <tr>
                <td>{{ $data }}</td>
                <td>{{ $linha->funcionario_nome ?? '-' }}</td>
                <td>{{ $linha->servico_nome ?? '-' }}</td>
                <td class="text-right">
                    {{ number_format($linha->valor ?? 0, 2, ',', '.') }}
                </td>
                <td>
                    <span class="badge {{ $isPago ? 'badge-pago' : 'badge-pendente' }}">
                        {{ $isPago ? 'Paga' : 'Pendente' }}
                    </span>
                </td>
            </tr>

        @empty
            <tr>
                <td colspan="5">Nenhuma comissão encontrada para os filtros informados.</td>
            </tr>
        @endforelse

    </tbody>
</table>

<!-- ==========================
            TOTAIS
     ========================== -->
<div class="totais">
    <strong>Total de registros:</strong> {{ $registros->count() }} <br>
    <strong>Soma das comissões:</strong> R$ {{ number_format($total, 2, ',', '.') }}

    <div class="totais-list">
        <strong>Totais por status:</strong><br>
        • Pagas: R$ {{ number_format($totalPago, 2, ',', '.') }}<br>
        • Pendentes: R$ {{ number_format($totalPendente, 2, ',', '.') }}
    </div>
</div>

<!-- ==========================
            RODAPÉ
     ========================== -->
<div class="footer">
    Documento gerado automaticamente pelo sistema Estética PRO.
</div>

</body>
</html>