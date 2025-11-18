@extends('layouts.app')

@section('title', 'Feedback - Estética PRO')

@section('content')
<style>
    :root{
        --primary:#ec4899; --primary-dark:#db2777; --primary-light:#fbcfe8;
        --secondary:#7e22ce; --text:#1f2937; --text-light:#6b7280;
        --success:#10b981; --warning:#f59e0b; --danger:#ef4444;
    }

    body{font-family:'Poppins',sans-serif;background:#f9fafb;color:var(--text)}
    .content{padding:11px}

    .page-header{
        display:flex;justify-content:space-between;align-items:center;
        margin-bottom:24px;gap:16px;flex-wrap:wrap;
    }
    .page-title{
        font-size:26px;font-weight:700;
        background:linear-gradient(135deg,var(--primary) 0%,var(--secondary) 100%);
        -webkit-background-clip:text;-webkit-text-fill-color:transparent;
    }
    .btn-back{
        display:inline-flex;align-items:center;gap:8px;
        padding:10px 16px;border-radius:999px;border:none;
        background:#fff;color:var(--text-light);cursor:pointer;
        box-shadow:0 2px 12px rgba(15,23,42,.08);font-size:13px;font-weight:500;
        text-decoration:none;transition:.25s;
    }
    .btn-back i{font-size:13px}
    .btn-back:hover{
        transform:translateY(-1px);
        color:var(--primary);
        box-shadow:0 4px 18px rgba(236,72,153,.25);
    }

    .card{
        background:#fff;border-radius:18px;
        box-shadow:0 8px 30px rgba(15,23,42,.08);
        padding:24px;margin-bottom:24px;
    }

    .section-title{
        font-size:15px;font-weight:600;color:var(--text-light);
        text-transform:uppercase;letter-spacing:.06em;margin-bottom:10px;
    }

    .feedback-header{
        display:flex;flex-wrap:wrap;gap:18px;align-items:flex-start;
        margin-bottom:18px;
    }
    .client-info{display:flex;align-items:center;flex:1 1 260px;}
    .avatar{
        width:52px;height:52px;border-radius:50%;
        background:linear-gradient(135deg,var(--primary) 0%,var(--secondary) 100%);
        display:flex;align-items:center;justify-content:center;
        color:#fff;font-weight:700;font-size:18px;margin-right:14px;
    }
    .client-details h3{font-size:17px;font-weight:600;margin:0 0 4px}
    .client-details p{margin:0;font-size:13px;color:var(--text-light)}

    .meta-info{
        display:flex;flex-wrap:wrap;gap:12px;align-items:center;
    }
    .meta-item-label{
        font-size:11px;text-transform:uppercase;letter-spacing:.06em;
        color:var(--text-light);margin-bottom:2px;
    }
    .meta-pill{
        padding:6px 12px;border-radius:999px;
        font-size:13px;font-weight:500;
        background:#f3f4f6;color:var(--text);
        display:inline-flex;align-items:center;gap:6px;
    }

    .badge{
        padding:6px 12px;border-radius:20px;font-size:13px;font-weight:600;
        display:inline-flex;align-items:center;gap:4px;
    }
    .badge-green{background:#ecfdf5;color:var(--success)}
    .badge-yellow{background:#fef3c7;color:var(--warning)}
    .badge-red{background:#fee2e2;color:var(--danger)}

    .comment-box{
        margin-top:8px;border-radius:14px;
        border:1px solid #e5e7eb;
        background:#f9fafb;
        padding:14px 16px;
        font-size:14px;line-height:1.6;color:var(--text);
        white-space:pre-wrap;
    }

    .secondary-card{
        background:#fff;border-radius:16px;
        box-shadow:0 4px 18px rgba(15,23,42,.06);
        padding:18px 20px;margin-bottom:24px;
    }
    .secondary-list{
        display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));
        gap:14px;font-size:13px;
    }
    .secondary-label{
        font-size:12px;font-weight:600;color:var(--text-light);
        text-transform:uppercase;letter-spacing:.06em;margin-bottom:3px;
    }

    @media(max-width:768px){
        .feedback-header{flex-direction:column;align-items:flex-start}
    }
</style>

<div class="content">
    <div class="page-header">
        <h1 class="page-title">Feedback do cliente</h1>

        <a href="{{ route('feedbacks.index') }}" class="btn-back">
            <i class="fas fa-arrow-left"></i>
            Voltar para lista
        </a>
    </div>

    {{-- CARD PRINCIPAL --}}
    <div class="card">
        <div class="feedback-header">
            {{-- CLIENTE --}}
            <div class="client-info">
                <div class="avatar">
                    {{ strtoupper(mb_substr($feedback->cliente->nome ?? 'C', 0, 1)) }}
                </div>
                <div class="client-details">
                    <h3>{{ $feedback->cliente->nome ?? 'Cliente não informado' }}</h3>
                    <p>{{ $feedback->cliente->email ?? 'Sem e-mail cadastrado' }}</p>
                </div>
            </div>

            {{-- META (SERVIÇO / NOTA / DATA) --}}
            <div class="meta-info">
                <div>
                    <div class="meta-item-label">Serviço</div>
                    <div class="meta-pill">
                        <i class="fas fa-spa"></i>
                        <span>{{ $feedback->servico->nome ?? '—' }}</span>
                    </div>
                </div>

                <div>
                    <div class="meta-item-label">Data</div>
                    <div class="meta-pill">
                        <i class="far fa-calendar"></i>
                        <span>{{ $feedback->created_at->format('d/m/Y H:i') }}</span>
                    </div>
                </div>

                <div>
                    <div class="meta-item-label">Avaliação</div>
                    @php
                        $nota = $feedback->nota;
                        if ($nota) {
                            $badgeClass = $nota >= 4 ? 'badge-green' : ($nota >= 3 ? 'badge-yellow' : 'badge-red');
                            $labelNota  = "★ {$nota}/5";
                        } else {
                            $badgeClass = 'badge-yellow';
                            $labelNota  = 'Sem nota';
                        }
                    @endphp
                    <span class="badge {{ $badgeClass }}">
                        {{ $labelNota }}
                    </span>
                </div>
            </div>
        </div>

        {{-- COMENTÁRIO --}}
        <div>
            <div class="section-title">Comentário do cliente</div>
            <div class="comment-box">
                {{ $feedback->comentario ?: 'Nenhum comentário adicional foi registrado para este atendimento.' }}
            </div>
        </div>
    </div>

    {{-- CARD SECUNDÁRIO (INFORMAÇÕES EXTRAS / FUTURAS) --}}
    <div class="secondary-card">
        <div class="section-title">Informações do atendimento</div>

        <div class="secondary-list">
            <div>
                <div class="secondary-label">ID do feedback</div>
                <div>#{{ $feedback->id }}</div>
            </div>

            <div>
                <div class="secondary-label">Cliente</div>
                <div>{{ $feedback->cliente->nome ?? '—' }}</div>
            </div>

            <div>
                <div class="secondary-label">Serviço</div>
                <div>{{ $feedback->servico->nome ?? '—' }}</div>
            </div>

            @if (!empty($feedback->agenda_id))
                <div>
                    <div class="secondary-label">Agendamento</div>
                    <div>#{{ $feedback->agenda_id }}</div>
                </div>
            @endif

            @if (!empty($feedback->created_at))
                <div>
                    <div class="secondary-label">Registrado em</div>
                    <div>{{ $feedback->created_at->format('d/m/Y H:i') }}</div>
                </div>
            @endif

            @if (!empty($feedback->updated_at) && $feedback->updated_at->ne($feedback->created_at))
                <div>
                    <div class="secondary-label">Última atualização</div>
                    <div>{{ $feedback->updated_at->format('d/m/Y H:i') }}</div>
                </div>
            @endif
        </div>
    </div>
</div>

@include('partials.toast')
@endsection
