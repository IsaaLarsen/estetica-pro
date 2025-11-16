@extends('layouts.app')

@section('title', 'Relatórios - Estética PRO')

@section('content')
    <style>
        .content { padding: 11px; }
        .page-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:24px; flex-wrap:wrap; gap:12px; }
        .page-title {
            font-size:28px; font-weight:700;
            background:linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            -webkit-background-clip:text; -webkit-text-fill-color:transparent;
        }
        .cards-grid {
            display:grid;
            grid-template-columns:repeat(auto-fit,minmax(260px,1fr));
            gap:18px;
        }
        .report-card {
            background:#fff;
            border-radius:16px;
            padding:18px 20px;
            box-shadow:0 4px 18px rgba(0,0,0,.06);
            display:flex;
            flex-direction:column;
            justify-content:space-between;
            transition:.3s;
            border:1px solid #f3f4f6;
        }
        .report-card:hover {
            transform:translateY(-3px);
            box-shadow:0 10px 25px rgba(0,0,0,.08);
        }
        .report-icon {
            width:40px;height:40px;border-radius:12px;
            display:flex;align-items:center;justify-content:center;
            background:rgba(236,72,153,.1);color:var(--primary);margin-bottom:10px;
        }
        .report-title { font-size:16px;font-weight:600;margin-bottom:4px; }
        .report-desc { font-size:13px;color:var(--text-light);margin-bottom:14px; }
        .report-meta { font-size:12px;color:var(--text-light);margin-bottom:10px; }
        .btn-report {
            margin-top:auto;
            align-self:flex-start;
            padding:10px 18px;
            border-radius:12px;
            border:none;
            text-decoration:none;
            font-size:13px;
            font-weight:500;
            background:linear-gradient(135deg,var(--primary) 0%,var(--secondary) 100%);
            color:#fff;
            display:inline-flex;
            align-items:center;
            gap:6px;
        }
        .btn-report i { font-size:12px; }
    </style>

    <div class="content">
        <div class="page-header">
            <h1 class="page-title">Relatórios</h1>
        </div>

        <div class="cards-grid">
            <div class="report-card">
                <div>
                    <div class="report-icon">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <div class="report-title">Relatório de Agendamentos</div>
                    <div class="report-desc">
                        Listagem detalhada de agendamentos com filtros por período,
                        funcionário e status, ideal para análises operacionais.
                    </div>
                    <div class="report-meta">
                        <i class="fas fa-filter"></i> Período · Funcionário · Status
                    </div>
                </div>
                <a href="{{ route('relatorios.agendamentos') }}" class="btn-report">
                    Abrir relatório <i class="fas fa-arrow-right"></i>
                </a>
            </div>

            <div class="report-card">
                <div>
                    <div class="report-icon" style="background:rgba(16,185,129,.08);color:#059669;">
                        <i class="fas fa-hand-holding-usd"></i>
                    </div>
                    <div class="report-title">Relatório de Comissões</div>
                    <div class="report-desc">
                        Consolidado de comissões por período e profissional,
                        com totalização dos valores e exportação em PDF profissional.
                    </div>
                    <div class="report-meta">
                        <i class="fas fa-filter"></i> Período · Funcionário · Status de pagamento
                    </div>
                </div>
                <a href="{{ route('relatorios.comissoes') }}" class="btn-report">
                    Abrir relatório <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        </div>
    </div>
@endsection
