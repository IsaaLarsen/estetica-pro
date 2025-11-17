@extends('layouts.app')

@section('title', 'Relatório de Faturamento - Estética PRO')

@section('content')
    <style>
        .content {
            padding: 11px;
            flex: 1;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
            flex-wrap: wrap;
            gap: 12px;
        }

        .page-title {
            font-size: 26px;
            font-weight: 700;
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
            padding: 10px 16px;
            border: none;
            cursor: pointer;
            font-size: 13px;
            font-weight: 500;
            text-decoration: none;
            transition: all .2s ease;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            color: #fff;
            box-shadow: 0 4px 14px rgba(236,72,153,.4);
        }

        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(236,72,153,.5);
        }

        .btn-outline {
            background: #fff;
            border: 1px solid #e5e7eb;
            color: var(--text);
        }

        .btn-outline:hover {
            background: #f9fafb;
        }

        .btn-link {
            background: none;
            border: none;
            color: var(--primary);
            text-decoration: underline;
            padding: 0;
            font-size: 13px;
            cursor: pointer;
        }

        .btn-icon {
            margin-right: 6px;
        }

        /* CARD DE FILTROS / CARD GENÉRICO */
        .filters-card,
        .table-card {
            background: #fff;
            border-radius: 16px;
            padding: 18px 20px;
            box-shadow: 0 4px 18px rgba(0,0,0,.06);
            margin-bottom: 18px;
        }

        .filters-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 12px;
            margin-bottom: 12px;
        }

        .filters-grid label {
            display: block;
            font-size: 13px;
            font-weight: 500;
            color: var(--text);
            margin-bottom: 4px;
        }

        .filters-grid input,
        .filters-grid select {
            width: 100%;
            padding: 10px 12px;
            border-radius: 10px;
            border: 2px solid #e5e7eb;
            font-size: 13px;
            font-family: 'Poppins', sans-serif;
            transition: all .2s ease;
        }

        .filters-grid input:focus,
        .filters-grid select:focus {
            border-color: var(--primary);
            outline: none;
            box-shadow: 0 0 0 3px rgba(236,72,153,.2);
        }

        .filters-actions {
            display: flex;
            justify-content: flex-end;
            gap: 8px;
            margin-top: 4px;
            flex-wrap: wrap;
        }

        .text-muted {
            color: var(--text-light);
            font-size: 12px;
        }

        /* CARDS DE RESUMO */
        .cards-row {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 16px;
            margin-bottom: 18px;
        }

        .card-kpi {
            background: #fff;
            border-radius: 16px;
            padding: 14px 16px;
            box-shadow: 0 4px 18px rgba(0,0,0,.06);
        }

        .card-kpi-label {
            font-size: 13px;
            color: var(--text-light);
            margin-bottom: 6px;
        }

        .card-kpi-value {
            font-size: 22px;
            font-weight: 700;
            color: var(--primary-dark);
        }

        .card-kpi-sub {
            margin-top: 4px;
            font-size: 11px;
            color: var(--text-light);
        }

        /* TABELAS */
        .card-title {
            font-size: 16px;
            font-weight: 700;
            color: var(--text);
            margin-bottom: 6px;
            display:flex;
            justify-content:space-between;
            align-items:center;
        }

        .card-subtitle {
            font-size: 12px;
            color: var(--text-light);
            margin-bottom: 10px;
        }

        .table-wrapper {
            overflow-x: auto;
            border-radius: 12px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 720px;
        }

        thead {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            color: #fff;
        }

        th, td {
            padding: 10px 12px;
            font-size: 13px;
            text-align: left;
        }

        tbody tr {
            border-bottom: 1px solid #f3f4f6;
            transition: background .2s ease;
        }

        tbody tr:nth-child(even) {
            background: #fdf2f8;
        }

        tbody tr:hover {
            background: #f9fafb;
        }

        .text-right { text-align: right; }
        .text-center { text-align: center; }

        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 500;
        }

        .badge-meio {
            background: #eef2ff;
            color: #3730a3;
        }

        .empty {
            padding: 16px 0;
            text-align: center;
            font-size: 13px;
            color: var(--text-light);
            font-style: italic;
        }

        @media (max-width: 992px) {
            .filters-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
            .cards-row {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 640px) {
            .filters-grid {
                grid-template-columns: 1fr;
            }
            .filters-actions {
                flex-direction: column;
                align-items: stretch;
            }
            .cards-row {
                grid-template-columns: 1fr;
            }
        }
    </style>

    <div class="content">
        <div class="page-header">
            <h1 class="page-title">Relatório de Faturamento</h1>
            <a href="{{ route('relatorios.index') }}" class="btn btn-outline">
                <i class="fas fa-arrow-left btn-icon"></i> Voltar aos relatórios
            </a>
        </div>

        {{-- FILTROS --}}
        <div class="filters-card">
            <form method="GET" action="{{ route('relatorios.faturamento') }}" id="filtroFaturamentoForm">
                <div class="filters-grid">
                    {{-- Data início --}}
                    <div>
                        <label for="data_inicio">Data inicial</label>
                        <input
                            type="date"
                            id="data_inicio"
                            name="data_inicio"
                            value="{{ request('data_inicio', optional($inicio)->format('Y-m-d')) }}"
                        >
                    </div>

                    {{-- Data fim --}}
                    <div>
                        <label for="data_fim">Data final</label>
                        <input
                            type="date"
                            id="data_fim"
                            name="data_fim"
                            value="{{ request('data_fim', optional($fim)->format('Y-m-d')) }}"
                        >
                    </div>

                    {{-- Funcionário --}}
                    <div>
                        <label for="funcionario_id">Profissional</label>
                        <select name="funcionario_id" id="funcionario_id">
                            <option value="">Todos</option>
                            @foreach($funcionarios as $f)
                                <option value="{{ $f->id }}"
                                    {{ (string)request('funcionario_id', $filtroFunc) === (string)$f->id ? 'selected' : '' }}>
                                    {{ $f->nome }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Serviço --}}
                    <div>
                        <label for="servico_id">Serviço</label>
                        <select name="servico_id" id="servico_id">
                            <option value="">Todos</option>
                            @foreach($servicos as $s)
                                <option value="{{ $s->id }}"
                                    {{ (string)request('servico_id', $filtroServico) === (string)$s->id ? 'selected' : '' }}>
                                    {{ $s->nome }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Meio de pagamento --}}
                    @if($meioPagCol && $meiosPagamento && $meiosPagamento->count())
                        <div>
                            <label for="meio_pagamento">Meio de pagamento</label>
                            <select name="meio_pagamento" id="meio_pagamento">
                                <option value="">Todos</option>
                                @foreach($meiosPagamento as $meio)
                                    <option value="{{ $meio }}"
                                        {{ (string)request('meio_pagamento', $filtroMeio) === (string)$meio ? 'selected' : '' }}>
                                        {{ $meio }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    {{-- Info período (igual comissões) --}}
                    <div>
                        <label>Período selecionado</label>
                        <div class="text-muted" style="margin-top: 9px;">
                            {{ optional($inicio)->format('d/m/Y') }} até {{ optional($fim)->format('d/m/Y') }}
                        </div>
                    </div>
                </div>

                <div class="filters-actions">
                    <button type="submit" class="btn btn-outline">
                        <i class="fas fa-filter btn-icon"></i> Aplicar filtros
                    </button>

                    {{-- Exportar PDF usando MESMOS filtros do form --}}
                    <button type="submit"
                            class="btn btn-primary"
                            formaction="{{ route('relatorios.faturamento.pdf') }}"
                            formtarget="_blank">
                        <i class="fas fa-file-pdf btn-icon"></i> Exportar PDF
                    </button>
                </div>
            </form>
        </div>

        {{-- CARDS DE RESUMO (se tiver coluna de valor) --}}
        @if($valorCol && $totaisGerais)
            <div class="cards-row">
                <div class="card-kpi">
                    <div class="card-kpi-label">Total de atendimentos concluídos</div>
                    <div class="card-kpi-value">
                        {{ number_format($totaisGerais['total_atendimentos'] ?? 0, 0, ',', '.') }}
                    </div>
                    <div class="card-kpi-sub">
                        Período de {{ optional($inicio)->format('d/m/Y') }} a {{ optional($fim)->format('d/m/Y') }}
                    </div>
                </div>

                <div class="card-kpi">
                    <div class="card-kpi-label">Faturamento total</div>
                    <div class="card-kpi-value">
                        R$ {{ number_format($totaisGerais['total_faturamento'] ?? 0, 2, ',', '.') }}
                    </div>
                    <div class="card-kpi-sub">
                        Considerando apenas agendamentos com status <strong>concluído</strong>.
                    </div>
                </div>

                <div class="card-kpi">
                    <div class="card-kpi-label">Ticket médio</div>
                    <div class="card-kpi-value">
                        R$ {{ number_format($totaisGerais['ticket_medio'] ?? 0, 2, ',', '.') }}
                    </div>
                    <div class="card-kpi-sub">
                        Faturamento total ÷ nº de atendimentos concluídos.
                    </div>
                </div>
            </div>
        @endif

        {{-- RESUMO POR PROFISSIONAL --}}
        <div class="table-card">
            <div class="card-title">
                <span>Faturamento por profissional</span>
                <span class="text-muted">
                    {{ $resumoPorProfissional->count() }} linha(s)
                </span>
            </div>
            <div class="card-subtitle">
                Visão consolidada por colaborador — apenas atendimentos concluídos.
            </div>

            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Profissional</th>
                            <th class="text-center">Atendimentos</th>
                            @if($valorCol)
                                <th class="text-right">Faturamento (R$)</th>
                                <th class="text-right">Ticket médio (R$)</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($resumoPorProfissional as $row)
                            <tr>
                                <td>{{ $row->funcionario_nome }}</td>
                                <td class="text-center">
                                    {{ number_format($row->total_atendimentos ?? 0, 0, ',', '.') }}
                                </td>
                                @if($valorCol)
                                    <td class="text-right">
                                        {{ number_format($row->faturamento ?? 0, 2, ',', '.') }}
                                    </td>
                                    <td class="text-right">
                                        {{ number_format($row->ticket_medio ?? 0, 2, ',', '.') }}
                                    </td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $valorCol ? 4 : 2 }}" class="empty">
                                    Nenhum atendimento encontrado para os filtros informados.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- RESUMO POR MEIO DE PAGAMENTO --}}
        @if($valorCol && $meioPagCol)
            <div class="table-card">
                <div class="card-title">
                    <span>Faturamento por meio de pagamento</span>
                    <span class="text-muted">
                        {{ $porMeioPagamento->count() }} linha(s)
                    </span>
                </div>
                <div class="card-subtitle">
                    Distribuição do faturamento entre os meios de pagamento utilizados.
                </div>

                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th>Meio de pagamento</th>
                                <th class="text-center">Atendimentos</th>
                                <th class="text-right">Faturamento (R$)</th>
                                <th class="text-right">Participação (%)</th>
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
                                    <td colspan="4" class="empty">
                                        Nenhuma informação de meio de pagamento disponível para este período.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        {{-- DETALHES DOS ATENDIMENTOS --}}
        <div class="table-card">
            <div class="card-title">
                <span>Detalhamento dos atendimentos</span>
                <span class="text-muted">
                    {{ $detalhes->count() }} registro(s)
                </span>
            </div>
            <div class="card-subtitle">
                Lista completa dos atendimentos concluídos dentro do período filtrado.
            </div>

            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Data/Hora</th>
                            <th>Cliente</th>
                            <th>Serviço</th>
                            <th>Profissional</th>
                            @if($valorCol)
                                <th class="text-right">Valor (R$)</th>
                            @endif
                            @if($meioPagCol)
                                <th>Meio de pagamento</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($detalhes as $d)
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($d->inicio)->format('d/m/Y H:i') }}</td>
                                <td>{{ $d->cliente_nome ?? '-' }}</td>
                                <td>{{ $d->servico_nome ?? '-' }}</td>
                                <td>{{ $d->funcionario_nome ?? '-' }}</td>
                                @if($valorCol)
                                    <td class="text-right">
                                        {{ number_format($d->valor ?? 0, 2, ',', '.') }}
                                    </td>
                                @endif
                                @if($meioPagCol)
                                    <td>{{ $d->meio_pagamento ?? '—' }}</td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ 4 + ($valorCol ? 1 : 0) + ($meioPagCol ? 1 : 0) }}" class="empty">
                                    Nenhum atendimento encontrado para os filtros informados.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @include('partials.toast')
@endsection