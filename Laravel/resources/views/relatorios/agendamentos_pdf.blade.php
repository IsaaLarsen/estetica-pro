<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Relatório de Agendamentos</title>

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
            position:relative; /* necessário para posicionar a logo */
        }

        /* LOGO */
        .logo {
            width:80px;
            height:auto;
            position:absolute;
            top:-5px;
            right:0;
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

        /* BADGES STATUS */
        .badge{
            display:inline-block;
            padding:2px 6px;
            border-radius:999px;
            font-size:9px;
            font-weight:bold;
        }
        .badge-agendado{ background:#dbeafe; color:#1d4ed8; }
        .badge-confirmado{ background:#dcfce7; color:#166534; }
        .badge-concluido{ background:#ede9fe; color:#5b21b6; }
        .badge-cancelado{ background:#fee2e2; color:#b91c1c; }

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
            color:#374151;
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
@endphp

<!-- HEADER -->
<div class="header">

    <div class="header-info">
        <div class="title">Relatório de Agendamentos</div>
        <div class="subtitle">Estética PRO — Sistema de Gestão</div>

        <div class="meta">
            <span><strong>Período:</strong> {{ $inicioPeriodo }} até {{ $fimPeriodo }}</span>
            <span><strong>Gerado em:</strong> {{ now()->format('d/m/Y H:i') }}</span>

            @if(!empty($usuario?->nome))
                <span><strong>Usuário:</strong> {{ $usuario->nome }}</span>
            @endif

            @if(!empty($filtroFuncNome))
                <span><strong>Profissional:</strong> {{ $filtroFuncNome }}</span>
            @endif

            @if(!empty($filtroServicoNome))
                <span><strong>Serviço:</strong> {{ $filtroServicoNome }}</span>
            @endif

            @if(!empty($filtroStatus))
                <span><strong>Status:</strong> {{ ucfirst($filtroStatus) }}</span>
            @endif
        </div>
    </div>

    <!-- LOGO no canto superior direito -->
    <img src="{{ public_path('image/logoEP.png') }}" class="logo" alt="Logo Estética PRO">

</div>

<!-- TABELA -->
<table class="table">
    <thead>
        <tr>
            <th style="width:16%;">Data/Hora</th>
            <th style="width:24%;">Cliente</th>
            <th style="width:26%;">Serviço</th>
            <th style="width:19%;">Profissional</th>
            <th style="width:15%;">Status</th>
        </tr>
    </thead>
    <tbody>
        @forelse($registros as $linha)
            @php
                $dataHora = $linha->inicio
                    ? Carbon::parse($linha->inicio)->format('d/m/Y H:i')
                    : '-';

                $statusRaw = strtolower($linha->status ?? '');
                $statusClass = match($statusRaw) {
                    'agendado'   => 'badge-agendado',
                    'confirmado' => 'badge-confirmado',
                    'concluido'  => 'badge-concluido',
                    'cancelado'  => 'badge-cancelado',
                    default      => 'badge-agendado',
                };

                $statusLabel = $statusRaw !== '' ? ucfirst($statusRaw) : '-';
            @endphp

            <tr>
                <td>{{ $dataHora }}</td>
                <td>{{ $linha->cliente_nome ?? '-' }}</td>
                <td>{{ $linha->servico_nome ?? '-' }}</td>
                <td>{{ $linha->funcionario_nome ?? '-' }}</td>
                <td><span class="badge {{ $statusClass }}">{{ $statusLabel }}</span></td>
            </tr>
        @empty
            <tr>
                <td colspan="5">Nenhum agendamento encontrado para os filtros informados.</td>
            </tr>
        @endforelse
    </tbody>
</table>

<!-- TOTAIS -->
<div class="totais">
    <strong>Total de agendamentos:</strong> {{ $registros->count() }}
</div>

<!-- RODAPÉ -->
<div class="footer">
    Documento gerado automaticamente pelo sistema Estética PRO.
</div>

</body>
</html>
