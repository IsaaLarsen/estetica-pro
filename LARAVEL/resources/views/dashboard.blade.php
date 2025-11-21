@extends('layouts.app')

@section('title', 'Dashboard - Estética PRO')

@section('content')
    {{-- Cabeçalho --}}
    <div class="page-header" style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px;">
        <h1 class="page-title" style="font-size:28px; font-weight:700; background:linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%); -webkit-background-clip:text; -webkit-text-fill-color:transparent;">
            Olá, {{ $usuario->nome ?? 'Usuário' }} 👋
        </h1>
        <div style="display:flex; gap:10px; flex-wrap:wrap;">
            <a href="{{ route('agenda.create') }}" class="btn btn-primary" style="text-decoration:none; background:linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%); color:#fff; padding:12px 16px; border-radius:12px; font-weight:600;">
                <i class="fas fa-plus" style="margin-right:8px;"></i> Novo Agendamento
            </a>
            <a href="{{ route('clientes.create') }}" class="btn" style="text-decoration:none; background:#f3f4f6; color:var(--text); padding:12px 16px; border-radius:12px; font-weight:600;">
                <i class="fas fa-user-plus" style="margin-right:8px;"></i> Novo Cliente
            </a>
        </div>
    </div>

    {{-- KPIs --}}
    <div class="stats-grid" style="display:grid; grid-template-columns: repeat(4, minmax(0,1fr)); gap:16px; margin-bottom:24px;">
        @php
            $kpis = [
                ['label' => 'Funcionários', 'icon' => 'fa-users', 'value' => $stats['total_funcionarios'] ?? 0, 'bg' => 'linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%)', 'color' => '#fff'],
                ['label' => 'Clientes', 'icon' => 'fa-user', 'value' => $stats['total_clientes'] ?? 0, 'bg' => '#fbcfe8', 'color' => 'var(--primary)'],
                ['label' => 'Serviços', 'icon' => 'fa-scissors', 'value' => $stats['total_servicos'] ?? 0, 'bg' => '#ede9fe', 'color' => '#7e22ce'],
                ['label' => 'Agendamentos hoje', 'icon' => 'fa-calendar-check', 'value' => $stats['agendamentos_hoje'] ?? 0, 'bg' => '#ecfdf5', 'color' => '#10b981'],
            ];
        @endphp
        @foreach($kpis as $k)
            <div class="stat-card" style="background:#fff; border-radius:16px; padding:18px; box-shadow:0 4px 18px rgba(0,0,0,.06); display:flex; align-items:center; gap:12px;">
                <div style="width:44px; height:44px; border-radius:12px; background:{{ $k['bg'] }}; color:{{ $k['color'] }}; display:flex; align-items:center; justify-content:center;">
                    <i class="fas {{ $k['icon'] }}"></i>
                </div>
                <div>
                    <div style="font-size:12px; color:var(--text-light);">{{ $k['label'] }}</div>
                    <div style="font-size:22px; font-weight:700;">{{ $k['value'] }}</div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Filtros rápidos --}}
    <div class="filters-bar" style="display:flex; gap:12px; align-items:center; margin-bottom:18px;">
        <form method="GET" action="{{ route('dashboard') }}" style="display:flex; gap:12px; flex-wrap:wrap;">
            <select name="periodo" style="border:2px solid #f3d1e5; border-radius:12px; padding:10px 12px;">
                @php $periodo = request('periodo','30'); @endphp
                <option value="7"  {{ $periodo=='7'  ? 'selected' : '' }}>Últimos 7 dias</option>
                <option value="14" {{ $periodo=='14' ? 'selected' : '' }}>Últimos 14 dias</option>
                <option value="30" {{ $periodo=='30' ? 'selected' : '' }}>Últimos 30 dias</option>
                <option value="90" {{ $periodo=='90' ? 'selected' : '' }}>Últimos 90 dias</option>
            </select>
            <select name="funcionario_id" style="border:2px solid #f3d1e5; border-radius:12px; padding:10px 12px;">
                <option value="">Todos os funcionários</option>
                @foreach(($filtros['funcionarios'] ?? []) as $f)
                    <option value="{{ $f->id }}" {{ (string)request('funcionario_id')===(string)$f->id ? 'selected' : '' }}>
                        {{ $f->nome }}
                    </option>
                @endforeach
            </select>
            <select name="servico_id" style="border:2px solid #f3d1e5; border-radius:12px; padding:10px 12px;">
                <option value="">Todos os serviços</option>
                @foreach(($filtros['servicos'] ?? []) as $s)
                    <option value="{{ $s->id }}" {{ (string)request('servico_id')===(string)$s->id ? 'selected' : '' }}>
                        {{ $s->nome }}
                    </option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-primary" style="background:linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%); color:#fff; padding:10px 16px; border-radius:12px; border:none; cursor:pointer;">
                Aplicar filtros
            </button>
        </form>
    </div>

    {{-- Linha de gráficos --}}
    <div class="grid" style="display:grid; grid-template-columns: 2fr 1fr; gap:16px; margin-bottom:16px;">
        <div style="background:#fff; border-radius:16px; padding:16px; box-shadow:0 4px 18px rgba(0,0,0,.06);">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
                <h3 style="font-size:16px; font-weight:700;">Agendamentos por dia</h3>
                <small style="color:var(--text-light);">Período: {{ $meta['inicio_fmt'] ?? '' }} — {{ $meta['fim_fmt'] ?? '' }}</small>
            </div>
            <canvas id="chartAgendamentosDia" height="110"></canvas>
        </div>
        <div style="background:#fff; border-radius:16px; padding:16px; box-shadow:0 4px 18px rgba(0,0,0,.06);">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
                <h3 style="font-size:16px; font-weight:700;">Status dos agendamentos</h3>
            </div>
            <canvas id="chartStatus" height="110"></canvas>
        </div>
    </div>

    {{-- Segunda linha de gráficos --}}
    <div class="grid" style="display:grid; grid-template-columns: 1fr 1fr; gap:16px; margin-bottom:16px;">
        <div style="background:#fff; border-radius:16px; padding:16px; box-shadow:0 4px 18px rgba(0,0,0,.06);">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
                <h3 style="font-size:16px; font-weight:700;">Top serviços ({{ $meta['periodo_dias'] ?? 30 }} dias)</h3>
            </div>
            <canvas id="chartTopServicos" height="140"></canvas>
        </div>
        <div style="background:#fff; border-radius:16px; padding:16px; box-shadow:0 4px 18px rgba(0,0,0,.06);">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
                <h3 style="font-size:16px; font-weight:700;">Top funcionários ({{ $meta['periodo_dias'] ?? 30 }} dias)</h3>
            </div>
            <canvas id="chartTopFuncionarios" height="140"></canvas>
        </div>
    </div>

    {{-- Tabelas analíticas --}}
    <div class="grid" style="display:grid; grid-template-columns: 1fr 1fr; gap:16px;">
        <div class="table-container" style="background:#fff; border-radius:16px; overflow:hidden; box-shadow:0 4px 20px rgba(0,0,0,0.05);">
            <div style="display:flex; align-items:center; justify-content:space-between; padding:12px 16px; border-bottom:1px solid #f3f4f6;">
                <h3 style="font-size:16px; font-weight:700;">Próximos agendamentos</h3>
            
            </div>
            <div style="overflow-x:auto;">
                <table style="width:100%; border-collapse:collapse; min-width:600px;">
                    <thead style="background:linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%); color:#fff;">
                        <tr>
                            <th style="padding:10px 12px; text-align:left;">Data/Hora</th>
                            <th style="padding:10px 12px; text-align:left;">Cliente</th>
                            <th style="padding:10px 12px; text-align:left;">Serviço</th>
                            <th style="padding:10px 12px; text-align:left;">Funcionário</th>
                            <th style="padding:10px 12px; text-align:left;">Status</th>
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
                            <tr style="border-bottom:1px solid #f3f4f6;">
                                <td style="padding:10px 12px;">{{ $item->data_hora ?? $item->inicio ?? '' }}</td>
                                <td style="padding:10px 12px;">{{ $item->cliente_nome ?? '-' }}</td>
                                <td style="padding:10px 12px;">{{ $item->servico_nome ?? '-' }}</td>
                                <td style="padding:10px 12px;">{{ $item->funcionario_nome ?? '-' }}</td>
                                <td style="padding:10px 12px;">
                                    <span style="padding:6px 10px; border-radius:20px; font-size:12px; font-weight:600; background:{{ $bg }}; color:{{ $fg }};">
                                        {{ $statusVal }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" style="padding:12px; color:var(--text-light);">Nenhum agendamento futuro.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="table-container" style="background:#fff; border-radius:16px; overflow:hidden; box-shadow:0 4px 20px rgba(0,0,0,0.05);">
            <div style="display:flex; align-items:center; justify-content:space-between; padding:12px 16px; border-bottom:1px solid #f3f4f6;">
                <h3 style="font-size:16px; font-weight:700;">Cancelamentos recentes</h3>
            </div>
            <div style="overflow-x:auto;">
                <table style="width:100%; border-collapse:collapse; min-width:520px;">
                    <thead style="background:linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%); color:#fff;">
                        <tr>
                            <th style="padding:10px 12px; text-align:left;">Data/Hora</th>
                            <th style="padding:10px 12px; text-align:left;">Cliente</th>
                            <th style="padding:10px 12px; text-align:left;">Serviço</th>
                            <th style="padding:10px 12px; text-align:left;">Funcionário</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($canceladosRecentes as $item)
                            <tr style="border-bottom:1px solid #f3f4f6;">
                                <td style="padding:10px 12px;">{{ $item->data_hora ?? $item->inicio ?? '' }}</td>
                                <td style="padding:10px 12px;">{{ $item->cliente_nome ?? '-' }}</td>
                                <td style="padding:10px 12px;">{{ $item->servico_nome ?? '-' }}</td>
                                <td style="padding:10px 12px;">{{ $item->funcionario_nome ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" style="padding:12px; color:var(--text-light);">Nenhum cancelamento no período.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    {{-- Chart.js CDN --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

    <script>
    // Paleta Estética PRO
    const pink   = getComputedStyle(document.documentElement).getPropertyValue('--primary').trim()   || '#ec4899';
    const purple = getComputedStyle(document.documentElement).getPropertyValue('--secondary').trim() || '#7e22ce';

    // Dados vindos do controller
    const diaLabels   = @json(array_keys($agendamentosPorDia ?? []));
    const diaValues   = @json(array_values($agendamentosPorDia ?? []));
    const statusData  = @json($agendamentosPorStatus ?? []);
    const topServicos = @json($topServicos ?? []);
    const topFuncionarios = @json($topFuncionarios ?? []);

    // ===== Linha: Agendamentos por dia
    new Chart(document.getElementById('chartAgendamentosDia'), {
        type: 'line',
        data: {
            labels: diaLabels,
            datasets: [{
                label: 'Agendamentos',
                data: diaValues,
                fill: false,
                borderWidth: 3,
                tension: .35,
                borderColor: pink,
                pointRadius: 3
            }]
        },
        options: {
            plugins: { legend: { display: false } },
            scales: {
                x: { grid: { display: false }},
                y: { beginAtZero: true, grid: { color: '#f3f4f6' } }
            }
        }
    });

    // ===== Rosquinha: Status
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
        options: { plugins: { legend: { position: 'bottom' } } }
    });

    // ===== Barras: Top serviços
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
            plugins: { legend: { display: false } },
            scales: {
                x: { beginAtZero: true, grid:{ color:'#f3f4f6' }},
                y: { grid: { display:false } }
            }
        }
    });

    // ===== Barras: Top funcionários
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
            plugins: { legend: { display: false } },
            scales: {
                x: { beginAtZero: true, grid:{ color:'#f3f4f6' }},
                y: { grid: { display:false } }
            }
        }
    });
    </script>
@endsection
