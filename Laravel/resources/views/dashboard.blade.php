@extends('layouts.app')

@section('title', 'Dashboard - Estética PRO')

@section('content')
    @php
        $papel = strtolower($usuario->role ?? $usuario->tipo ?? '');
        $isFuncionario = $papel === 'funcionario';
    @endphp

    {{-- ===== ESTILOS RESPONSIVOS DO DASHBOARD (VERSÃO MAIS COMPACTA) ===== --}}
    <style>
        .dashboard-wrap {
            padding: 11px;
        }

        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            gap: 10px;
            flex-wrap: wrap;
        }

        .dashboard-greeting {
            font-size: 24px;
            font-weight: 700;
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin: 0;
        }

        .dashboard-header-actions {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .btn-dashboard-primary,
        .btn-dashboard-secondary {
            text-decoration: none;
            padding: 10px 14px;
            border-radius: 10px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: none;
            cursor: pointer;
            font-size: 13px;
        }

        .btn-dashboard-primary {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            color: #fff;
        }

        .btn-dashboard-secondary {
            background: #f3f4f6;
            color: var(--text);
        }

        .btn-dashboard-primary i,
        .btn-dashboard-secondary i {
            margin-right: 6px;
        }

        /* KPIs mais compactos */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 12px;
            margin-bottom: 18px;
        }

        .stat-card {
            background: #fff;
            border-radius: 14px;
            padding: 12px;
            box-shadow: 0 3px 12px rgba(0, 0, 0, .05);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .stat-icon {
            width: 34px;
            height: 34px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
        }

        .stat-card-label {
            font-size: 11px;
            color: var(--text-light);
        }

        .stat-card-value {
            font-size: 18px;
            font-weight: 700;
        }

        /* Filtros */
        .filters-bar {
            display: flex;
            gap: 10px;
            align-items: center;
            margin-bottom: 14px;
        }

        .filters-bar form {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .filters-bar select {
            border: 2px solid #f3d1e5;
            border-radius: 10px;
            padding: 8px 10px;
            font-size: 13px;
        }

        .filters-apply-btn {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            color: #fff;
            padding: 9px 14px;
            border-radius: 10px;
            border: none;
            cursor: pointer;
            font-weight: 600;
            font-size: 13px;
        }

        /* Grids de gráficos */
        .charts-row-large {
            display: grid;
            grid-template-columns: 1fr 1fr; /* antes 2fr 1fr */
            gap: 12px;
            margin-bottom: 12px;
        }

        .charts-row-small {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            margin-bottom: 12px;
        }

        .dashboard-card {
            background: #fff;
            border-radius: 14px;
            padding: 12px;
            box-shadow: 0 3px 12px rgba(0, 0, 0, .05);
        }

        /* Cartão específico de gráfico (altura controlada) */
        .chart-card {
            height: 290px; /* controla altura total */
            display: flex;
            flex-direction: column;
        }

        .chart-card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 6px;
        }

        .dashboard-card-title {
            font-size: 14px;
            font-weight: 700;
            margin: 0;
        }

        .chart-card small {
            font-size: 11px;
        }

        .chart-card canvas {
            flex: 1;
            max-height: 230px;
        }

        /* Tabelas analíticas */
        .tables-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }

        .table-container-dashboard {
            background: #fff;
            border-radius: 14px;
            overflow: hidden;
            box-shadow: 0 3px 12px rgba(0, 0, 0, 0.05);
        }

        .table-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 10px 12px;
            border-bottom: 1px solid #f3f4f6;
        }

        .table-header h3 {
            font-size: 14px;
            font-weight: 700;
            margin: 0;
        }

        .table-wrapper {
            overflow-x: auto;
        }

        .table-dashboard {
            width: 100%;
            border-collapse: collapse;
            min-width: 480px;
            font-size: 12px;
        }

        .table-dashboard thead {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            color: #fff;
        }

        .table-dashboard th,
        .table-dashboard td {
            padding: 8px 10px;
            text-align: left;
        }

        .table-dashboard tr {
            border-bottom: 1px solid #f3f4f6;
        }

        .status-pill {
            padding: 4px 8px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            display: inline-block;
        }

        @media (max-width: 1024px) {
            .charts-row-large,
            .charts-row-small,
            .tables-row {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .dashboard-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .dashboard-greeting {
                font-size: 20px;
            }

            .dashboard-header-actions {
                width: 100%;
                justify-content: flex-start;
            }

            .btn-dashboard-primary,
            .btn-dashboard-secondary {
                width: 100%;
            }

            .filters-bar {
                align-items: stretch;
            }

            .filters-bar form {
                flex-direction: column;
                align-items: stretch;
            }

            .filters-bar select,
            .filters-apply-btn {
                width: 100%;
            }

            .table-dashboard {
                min-width: 0;
            }

            .chart-card {
                height: 260px;
            }
        }
    </style>

    <div class="dashboard-wrap">
        {{-- Cabeçalho --}}
        <div class="dashboard-header">
            <h1 class="dashboard-greeting">
                Olá, {{ $usuario->nome ?? 'Usuário' }} 👋
            </h1>
            <div class="dashboard-header-actions">
                {{-- Ambos (admin e funcionário) podem criar agendamento --}}
                <a href="{{ route('agenda.create') }}" class="btn-dashboard-primary">
                    <i class="fas fa-plus"></i> Novo Agendamento
                </a>

                {{-- Botão "Novo Cliente" só para ADMIN --}}
                @if(!$isFuncionario)
                    <a href="{{ route('clientes.create') }}" class="btn-dashboard-secondary">
                        <i class="fas fa-user-plus"></i> Novo Cliente
                    </a>
                @endif
            </div>
        </div>

        {{-- KPIs --}}
        <div class="stats-grid">
            @php
                if ($isFuncionario) {
                    $kpis = [
                        ['label' => 'Clientes', 'icon' => 'fa-user', 'value' => $stats['total_clientes'] ?? 0, 'bg' => '#fbcfe8', 'color' => 'var(--primary)'],
                        ['label' => 'Serviços', 'icon' => 'fa-scissors', 'value' => $stats['total_servicos'] ?? 0, 'bg' => '#ede9fe', 'color' => '#7e22ce'],
                        ['label' => 'Agendamentos hoje', 'icon' => 'fa-calendar-check', 'value' => $stats['agendamentos_hoje'] ?? 0, 'bg' => '#ecfdf5', 'color' => '#10b981'],
                    ];
                } else {
                    $kpis = [
                        ['label' => 'Funcionários', 'icon' => 'fa-users', 'value' => $stats['total_funcionarios'] ?? 0, 'bg' => 'linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%)', 'color' => '#fff'],
                        ['label' => 'Clientes', 'icon' => 'fa-user', 'value' => $stats['total_clientes'] ?? 0, 'bg' => '#fbcfe8', 'color' => 'var(--primary)'],
                        ['label' => 'Serviços', 'icon' => 'fa-scissors', 'value' => $stats['total_servicos'] ?? 0, 'bg' => '#ede9fe', 'color' => '#7e22ce'],
                        ['label' => 'Agendamentos hoje', 'icon' => 'fa-calendar-check', 'value' => $stats['agendamentos_hoje'] ?? 0, 'bg' => '#ecfdf5', 'color' => '#10b981'],
                    ];
                }
            @endphp

            @foreach($kpis as $k)
                <div class="stat-card">
                    <div class="stat-icon" style="background:{{ $k['bg'] }}; color:{{ $k['color'] }};">
                        <i class="fas {{ $k['icon'] }}"></i>
                    </div>
                    <div>
                        <div class="stat-card-label">{{ $k['label'] }}</div>
                        <div class="stat-card-value">{{ $k['value'] }}</div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Filtros rápidos --}}
        <div class="filters-bar">
            <form method="GET" action="{{ route('dashboard') }}">
                @php $periodo = request('periodo','30'); @endphp

                <select name="periodo">
                    <option value="7"  {{ $periodo=='7'  ? 'selected' : '' }}>Últimos 7 dias</option>
                    <option value="14" {{ $periodo=='14' ? 'selected' : '' }}>Últimos 14 dias</option>
                    <option value="30" {{ $periodo=='30' ? 'selected' : '' }}>Últimos 30 dias</option>
                    <option value="90" {{ $periodo=='90' ? 'selected' : '' }}>Últimos 90 dias</option>
                </select>

                @if(!$isFuncionario)
                    <select name="funcionario_id">
                        <option value="">Todos os funcionários</option>
                        @foreach(($filtros['funcionarios'] ?? []) as $f)
                            <option value="{{ $f->id }}" {{ (string)request('funcionario_id')===(string)$f->id ? 'selected' : '' }}>
                                {{ $f->nome }}
                            </option>
                        @endforeach
                    </select>
                @endif

                <select name="servico_id">
                    <option value="">Todos os serviços</option>
                    @foreach(($filtros['servicos'] ?? []) as $s)
                        <option value="{{ $s->id }}" {{ (string)request('servico_id')===(string)$s->id ? 'selected' : '' }}>
                            {{ $s->nome }}
                        </option>
                    @endforeach
                </select>

                <button type="submit" class="filters-apply-btn">
                    Aplicar filtros
                </button>
            </form>
        </div>

        {{-- Linha de gráficos --}}
        <div class="charts-row-large">
            <div class="dashboard-card chart-card">
                <div class="chart-card-header">
                    <h3 class="dashboard-card-title">Agendamentos por dia</h3>
                    <small style="color:var(--text-light);">
                        Período: {{ $meta['inicio_fmt'] ?? '' }} — {{ $meta['fim_fmt'] ?? '' }}
                    </small>
                </div>
                <canvas id="chartAgendamentosDia"></canvas>
            </div>

            <div class="dashboard-card chart-card">
                <div class="chart-card-header">
                    <h3 class="dashboard-card-title">Status dos agendamentos</h3>
                </div>
                <canvas id="chartStatus"></canvas>
            </div>
        </div>

        {{-- Segunda linha de gráficos --}}
        <div class="charts-row-small">
            <div class="dashboard-card chart-card">
                <div class="chart-card-header">
                    <h3 class="dashboard-card-title">
                        Top serviços ({{ $meta['periodo_dias'] ?? 30 }} dias)
                    </h3>
                </div>
                <canvas id="chartTopServicos"></canvas>
            </div>

            <div class="dashboard-card chart-card">
                <div class="chart-card-header">
                    <h3 class="dashboard-card-title">
                        Top funcionários ({{ $meta['periodo_dias'] ?? 30 }} dias)
                    </h3>
                </div>
                <canvas id="chartTopFuncionarios"></canvas>
            </div>
        </div>

        {{-- Tabelas analíticas --}}
        <div class="tables-row">
            <div class="table-container-dashboard">
                <div class="table-header">
                    <h3>Próximos agendamentos</h3>
                </div>
                <div class="table-wrapper">
                    <table class="table-dashboard">
                        <thead>
                            <tr>
                                <th>Data/Hora</th>
                                <th>Cliente</th>
                                <th>Serviço</th>
                                <th>Funcionário</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                function statusBadge($st) {
                                    $s = strtolower((string)$st);
                                    return match($s) {
                                        'confirmado' => ['#ecfeff','#0891b2'],
                                        'concluido'  => ['#ecfdf5','#10b981'],
                                        'cancelado'  => ['#fee2e2','#ef4444'],
                                        'agendado'   => ['#f5f3ff','#7c3aed'],
                                        default      => ['#f3f4f6','#6b7280'],
                                    };
                                }
                            @endphp
                            @forelse($proximos as $item)
                                @php
                                    $statusVal = $item->status ?? $item->situacao ?? 'agendado';
                                    [$bg,$fg] = statusBadge($statusVal);
                                @endphp
                                <tr>
                                    <td>{{ $item->data_hora ?? $item->inicio ?? '' }}</td>
                                    <td>{{ $item->cliente_nome ?? '-' }}</td>
                                    <td>{{ $item->servico_nome ?? '-' }}</td>
                                    <td>{{ $item->funcionario_nome ?? '-' }}</td>
                                    <td>
                                        <span class="status-pill" style="background:{{ $bg }}; color:{{ $fg }};">
                                            {{ $statusVal }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" style="padding:10px; color:var(--text-light);">
                                        Nenhum agendamento futuro.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="table-container-dashboard">
                <div class="table-header">
                    <h3>Cancelamentos recentes</h3>
                </div>
                <div class="table-wrapper">
                    <table class="table-dashboard">
                        <thead>
                            <tr>
                                <th>Data/Hora</th>
                                <th>Cliente</th>
                                <th>Serviço</th>
                                <th>Funcionário</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($canceladosRecentes as $item)
                                <tr>
                                    <td>{{ $item->data_hora ?? $item->inicio ?? '' }}</td>
                                    <td>{{ $item->cliente_nome ?? '-' }}</td>
                                    <td>{{ $item->servico_nome ?? '-' }}</td>
                                    <td>{{ $item->funcionario_nome ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" style="padding:10px; color:var(--text-light);">
                                        Nenhum cancelamento no período.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    {{-- Chart.js CDN --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

    <script>
    const pink   = getComputedStyle(document.documentElement).getPropertyValue('--primary').trim()   || '#ec4899';
    const purple = getComputedStyle(document.documentElement).getPropertyValue('--secondary').trim() || '#7e22ce';

    const diaLabels        = @json(array_keys($agendamentosPorDia ?? []));
    const diaValues        = @json(array_values($agendamentosPorDia ?? []));
    const statusData       = @json($agendamentosPorStatus ?? []);
    const topServicos      = @json($topServicos ?? []);
    const topFuncionarios  = @json($topFuncionarios ?? []);

    // Linha: Agendamentos por dia
    new Chart(document.getElementById('chartAgendamentosDia'), {
        type: 'line',
        data: {
            labels: diaLabels,
            datasets: [{
                label: 'Agendamentos',
                data: diaValues,
                fill: false,
                borderWidth: 2,
                tension: .3,
                borderColor: pink,
                pointRadius: 3
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                x: { grid: { display: false }},
                y: { beginAtZero: true, grid: { color: '#f3f4f6' } }
            }
        }
    });

    // Rosquinha: Status
    const statusLabels = Object.keys(statusData || {});
    const statusValues = Object.values(statusData || {});
    new Chart(document.getElementById('chartStatus'), {
        type: 'doughnut',
        data: {
            labels: statusLabels,
            datasets: [{
                data: statusValues,
                borderWidth: 2,
                borderColor: '#fff',
                backgroundColor: ['#fde7f3', '#fbcfe8', '#ede9fe', '#fee2e2', '#f3f4f6']
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { position: 'bottom' } }
        }
    });

    // Barras: Top serviços
    new Chart(document.getElementById('chartTopServicos'), {
        type: 'bar',
        data: {
            labels: (topServicos || []).map(i => i.nome),
            datasets: [{
                label: 'Atendimentos',
                data: (topServicos || []).map(i => i.total),
                backgroundColor: pink
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                x: { beginAtZero: true, grid:{ color:'#f3f4f6' }},
                y: { grid: { display:false } }
            }
        }
    });

    // Barras: Top funcionários
    new Chart(document.getElementById('chartTopFuncionarios'), {
        type: 'bar',
        data: {
            labels: (topFuncionarios || []).map(i => i.nome),
            datasets: [{
                label: 'Atendimentos',
                data: (topFuncionarios || []).map(i => i.total),
                backgroundColor: purple
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                x: { beginAtZero: true, grid:{ color:'#f3f4f6' }},
                y: { grid: { display:false } }
            }
        }
    });
    </script>
@endsection
