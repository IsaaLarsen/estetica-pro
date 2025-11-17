@extends('layouts.app')

@section('title', 'Relatório de Agendamentos - Estética PRO')

@section('content')
    <style>
        :root {
            --primary:#ec4899; --secondary:#7e22ce;
            --text:#1f2937; --text-light:#6b7280;
            --success:#10b981; --warning:#f59e0b; --danger:#ef4444;
        }
        *{box-sizing:border-box;}

        .content{ padding:11px; flex:1; }

        .page-header{
            display:flex; justify-content:space-between; align-items:center;
            margin-bottom:24px; flex-wrap:wrap; gap:16px;
        }
        .page-title{
            font-size:28px; font-weight:700;
            background:linear-gradient(135deg,var(--primary) 0%,var(--secondary) 100%);
            -webkit-background-clip:text; -webkit-text-fill-color:transparent;
        }
        .header-actions{ display:flex; gap:10px; flex-wrap:wrap; }

        .btn{
            display:inline-flex; align-items:center; justify-content:center;
            padding:10px 16px; border-radius:12px; border:none;
            cursor:pointer; font-weight:500; text-decoration:none;
            transition:.2s;
            font-size:13px;
        }
        .btn-primary{
            background:linear-gradient(135deg,var(--primary) 0%,var(--secondary) 100%);
            color:#fff; box-shadow:0 4px 14px rgba(236,72,153,.35);
        }
        .btn-primary:hover{ transform:translateY(-1px); box-shadow:0 6px 20px rgba(236,72,153,.5); }
        .btn-light{
            background:#f3f4f6; color:var(--text);
        }
        .btn-light:hover{ background:#e5e7eb; }
        .btn-link{
            background:none; border:none;
            color:var(--primary);
            text-decoration:underline;
            padding:0 4px;
            cursor:pointer;
            font-size:13px;
        }
        .btn-icon{ margin-right:8px; }

        .filters-card{
            background:#fff; border-radius:16px;
            box-shadow:0 4px 20px rgba(0,0,0,.05);
            padding:16px; margin-bottom:18px;
        }
        .filters-grid{
            display:grid; grid-template-columns: repeat(4, minmax(0,1fr));
            gap:12px; align-items:flex-end;
        }
        .filters-actions{
            display:flex;
            justify-content:flex-end;
            gap:8px;
            margin-top:8px;
            flex-wrap:wrap;
        }
        label{ font-size:13px; font-weight:500; color:var(--text); margin-bottom:4px; display:block; }
        input[type="date"], select{
            width:100%; padding:10px 12px; border-radius:10px;
            border:2px solid #e5e7eb; font-size:13px; font-family:'Poppins',sans-serif;
            transition:.2s;
        }
        input:focus, select:focus{
            outline:none; border-color:var(--primary);
            box-shadow:0 0 0 3px rgba(236,72,153,.2);
        }

        .kpi-grid{
            display:grid; grid-template-columns: repeat(4, minmax(0,1fr));
            gap:12px; margin-bottom:18px;
        }
        .kpi-card{
            background:#fff; border-radius:16px; padding:14px;
            box-shadow:0 4px 16px rgba(0,0,0,.05);
        }
        .kpi-label{ font-size:12px; color:var(--text-light); margin-bottom:4px; }
        .kpi-value{ font-size:22px; font-weight:700; }
        .kpi-extra{ font-size:11px; color:var(--text-light); }

        .table-container{
            background:#fff; border-radius:16px;
            box-shadow:0 4px 20px rgba(0,0,0,.05);
            overflow:hidden; margin-bottom:18px;
        }
        table{ width:100%; border-collapse:collapse; min-width:800px; }
        thead{
            background:linear-gradient(135deg,var(--primary) 0%,var(--secondary) 100%);
            color:#fff;
        }
        th, td{ padding:10px 12px; text-align:left; font-size:13px; }
        tbody tr{ border-bottom:1px solid #f3f4f6; }
        tbody tr:hover{ background:#f9fafb; }
        .empty-row{
            text-align:center; padding:20px; color:var(--text-light);
        }

        .badge-status{
            padding:5px 10px; border-radius:20px;
            font-size:11px; font-weight:600; display:inline-block;
        }

        .split-grid{
            display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:16px;
        }
        .card{
            background:#fff; border-radius:16px; padding:14px;
            box-shadow:0 4px 16px rgba(0,0,0,.05);
        }
        .card h3{ font-size:14px; font-weight:600; margin-bottom:8px; }
        .mini-list{ font-size:12px; max-height:260px; overflow:auto; }
        .mini-list-item{
            display:flex; justify-content:space-between; padding:4px 0;
            border-bottom:1px dashed #f3f4f6;
        }

        @media(max-width:992px){
            .filters-grid{ grid-template-columns:repeat(2,minmax(0,1fr)); }
            .kpi-grid{ grid-template-columns:repeat(2,minmax(0,1fr)); }
            .split-grid{ grid-template-columns:1fr; }
        }
        @media(max-width:640px){
            .filters-grid{ grid-template-columns:1fr; }
            .kpi-grid{ grid-template-columns:1fr; }
        }
    </style>

    <div class="content">
        {{-- Cabeçalho --}}
        <div class="page-header">
            <h1 class="page-title">Relatório de Agendamentos</h1>
            <div class="header-actions">
                <a href="{{ route('agenda.index') }}" class="btn btn-light">
                    <i class="fas fa-calendar-alt btn-icon"></i> Ir para Agenda
                </a>
            </div>
        </div>

        {{-- Filtros --}}
        <div class="filters-card">
            <form method="GET" action="{{ route('relatorios.agendamentos') }}">
                <div class="filters-grid">
                    <div>
                        <label for="data_inicio">Data inicial</label>
                        <input type="date" id="data_inicio" name="data_inicio"
                               value="{{ request('data_inicio', $dataInicio) }}">
                    </div>
                    <div>
                        <label for="data_fim">Data final</label>
                        <input type="date" id="data_fim" name="data_fim"
                               value="{{ request('data_fim', $dataFim) }}">
                    </div>
                    <div>
                        <label for="funcionario_id">Funcionário</label>
                        <select id="funcionario_id" name="funcionario_id">
                            <option value="">Todos</option>
                            @foreach($funcionarios as $f)
                                <option value="{{ $f->id }}"
                                    {{ (string)request('funcionario_id', $filtroFunc) === (string)$f->id ? 'selected' : '' }}>
                                    {{ $f->nome }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="servico_id">Serviço</label>
                        <select id="servico_id" name="servico_id">
                            <option value="">Todos</option>
                            @foreach($servicos as $s)
                                <option value="{{ $s->id }}"
                                    {{ (string)request('servico_id', $filtroServico) === (string)$s->id ? 'selected' : '' }}>
                                    {{ $s->nome }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="status">Status</label>
                        @php $stSel = request('status', $filtroStatus); @endphp
                        <select id="status" name="status">
                            <option value="">Todos</option>
                            <option value="agendado"   {{ $stSel === 'agendado'   ? 'selected' : '' }}>Agendado</option>
                            <option value="confirmado" {{ $stSel === 'confirmado' ? 'selected' : '' }}>Confirmado</option>
                            <option value="concluido"  {{ $stSel === 'concluido'  ? 'selected' : '' }}>Concluído</option>
                            <option value="cancelado"  {{ $stSel === 'cancelado'  ? 'selected' : '' }}>Cancelado</option>
                        </select>
                    </div>
                </div>

                <p style="font-size:12px; color:var(--text-light); margin-top:8px;">
                    Período considerado: <strong>{{ $inicio->format('d/m/Y') }}</strong> a
                    <strong>{{ $fim->format('d/m/Y') }}</strong>
                </p>

                <div class="filters-actions">
                    <button type="submit" class="btn btn-light">
                        <i class="fas fa-filter btn-icon"></i> Aplicar filtros
                    </button>

                    {{-- Exportar PDF com os mesmos filtros --}}
                    <button type="submit"
                            class="btn btn-primary"
                            formaction="{{ route('relatorios.agendamentos.pdf') }}"
                            formtarget="_blank">
                        <i class="fas fa-file-pdf btn-icon"></i> Exportar PDF
                    </button>

                
                </div>
            </form>
        </div>

        {{-- KPIs do relatório --}}
        <div class="kpi-grid">
            <div class="kpi-card">
                <div class="kpi-label">Total de agendamentos</div>
                <div class="kpi-value">{{ $totalAgendamentos }}</div>
                <div class="kpi-extra">No período filtrado</div>
            </div>
            <div class="kpi-card">
                <div class="kpi-label">Confirmados</div>
                <div class="kpi-value">{{ $porStatus['confirmado'] ?? 0 }}</div>
                <div class="kpi-extra">Status = confirmado</div>
            </div>
            <div class="kpi-card">
                <div class="kpi-label">Concluídos</div>
                <div class="kpi-value">{{ $porStatus['concluido'] ?? 0 }}</div>
                <div class="kpi-extra">Atendimentos realizados</div>
            </div>
            <div class="kpi-card">
                <div class="kpi-label">Cancelados</div>
                <div class="kpi-value">{{ $porStatus['cancelado'] ?? 0 }}</div>
                <div class="kpi-extra">Perdidos no período</div>
            </div>
        </div>

        {{-- Tabelas resumo (lado a lado) --}}
        <div class="split-grid">
            <div class="card">
                <h3>Agendamentos por dia</h3>
                <div class="mini-list">
                    @forelse($porDia as $dia => $qtde)
                        <div class="mini-list-item">
                            <span>{{ $dia }}</span>
                            <span><strong>{{ $qtde }}</strong></span>
                        </div>
                    @empty
                        <p style="color:var(--text-light); font-style:italic;">Nenhum agendamento neste período.</p>
                    @endforelse
                </div>
            </div>
            <div class="card">
                <h3>Top serviços / profissionais</h3>
                <div class="mini-list">
                    <p class="kpi-label" style="margin-bottom:4px;">Serviços</p>
                    @forelse($porServico as $nome => $qtde)
                        <div class="mini-list-item">
                            <span>{{ $nome }}</span>
                            <span><strong>{{ $qtde }}</strong></span>
                        </div>
                    @empty
                        <p style="color:var(--text-light); font-style:italic;">Sem dados de serviços.</p>
                    @endforelse

                    <p class="kpi-label" style="margin:10px 0 4px;">Profissionais</p>
                    @forelse($porFuncionario as $nome => $qtde)
                        <div class="mini-list-item">
                            <span>{{ $nome }}</span>
                            <span><strong>{{ $qtde }}</strong></span>
                        </div>
                    @empty
                        <p style="color:var(--text-light); font-style:italic;">Sem dados de profissionais.</p>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Tabela detalhada --}}
        <div class="table-container">
            <div style="padding:10px 14px; border-bottom:1px solid #f3f4f6; display:flex; justify-content:space-between; align-items:center;">
                <h3 style="font-size:14px; font-weight:600; color:var(--text);">Detalhamento dos agendamentos</h3>
                <span style="font-size:12px; color:var(--text-light);">
                    {{ $totalAgendamentos }} registro(s) encontrado(s)
                </span>
            </div>

            <div style="overflow-x:auto;">
                <table>
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
                            function badgeColors($st){
                                $s = strtolower($st ?? '');
                                return match($s){
                                    'agendado'   => ['#f5f3ff','#7c3aed'],
                                    'confirmado' => ['#ecfeff','#0891b2'],
                                    'concluido'  => ['#ecfdf5','#10b981'],
                                    'cancelado'  => ['#fee2e2','#ef4444'],
                                    default      => ['#f3f4f6','#6b7280'],
                                };
                            }
                        @endphp

                        @forelse($registros as $item)
                            @php
                                $dataHora = \Carbon\Carbon::parse($item->inicio)->format('d/m/Y H:i');
                                [$bg,$fg] = badgeColors($item->status);
                            @endphp
                            <tr>
                                <td>{{ $dataHora }}</td>
                                <td>{{ $item->cliente_nome ?? '—' }}</td>
                                <td>{{ $item->servico_nome ?? '—' }}</td>
                                <td>{{ $item->funcionario_nome ?? '—' }}</td>
                                <td>
                                    <span class="badge-status" style="background:{{ $bg }}; color:{{ $fg }};">
                                        {{ strtolower($item->status ?? 'indefinido') }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="empty-row">
                                    Nenhum agendamento encontrado com os filtros selecionados.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @include('partials.change_password_modal')
    @include('partials.toast')
@endsection
