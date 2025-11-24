@extends('layouts.app')

@section('title', 'Logs do Sistema - Estética PRO')

@section('content')
<div style="background:white; padding:24px; border-radius:16px; box-shadow:0 4px 20px rgba(0,0,0,0.08);">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
        <h1 style="font-size:24px; font-weight:700;
            background:linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            -webkit-background-clip:text; -webkit-text-fill-color:transparent;">
            Logs do Sistema
        </h1>
    </div>

    {{-- Filtros --}}
    <form method="GET" action="{{ route('logs.index') }}"
          style="margin-bottom:16px; display:grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap:12px;">
        
        {{-- Filtro por Usuário (Select de funcionários ativos) --}}
        <select name="usuario_id" style="padding:10px 12px; border-radius:8px; border:1px solid #e5e7eb; background:white;">
            <option value="">Todos os Usuários</option>
            @foreach($funcionariosAtivos as $funcionario)
                <option value="{{ $funcionario->id }}" {{ request('usuario_id') == $funcionario->id ? 'selected' : '' }}>
                    {{ $funcionario->nome }}
                </option>
            @endforeach
        </select>

        {{-- Filtro por Model --}}
        <select name="model" style="padding:10px 12px; border-radius:8px; border:1px solid #e5e7eb; background:white;">
            <option value="">Todos os Models</option>
            @foreach($models as $model)
                <option value="{{ $model }}" {{ request('model') == $model ? 'selected' : '' }}>
                    {{ $model }}
                </option>
            @endforeach
        </select>

        {{-- Filtro por Ação --}}
        <select name="action" style="padding:10px 12px; border-radius:8px; border:1px solid #e5e7eb; background:white;">
            <option value="">Todas as Ações</option>
            @foreach($actions as $action)
                <option value="{{ $action }}" {{ request('action') == $action ? 'selected' : '' }}>
                    {{ ucfirst($action) }}
                </option>
            @endforeach
        </select>

        {{-- Filtro por Data --}}
        <input type="date" name="data_de" value="{{ request('data_de') }}"
               style="padding:10px 12px; border-radius:8px; border:1px solid #e5e7eb;">

        <input type="date" name="data_ate" value="{{ request('data_ate') }}"
               style="padding:10px 12px; border-radius:8px; border:1px solid #e5e7eb;">

        {{-- Botões --}}
        <div style="grid-column:1 / -1; display:flex; justify-content:flex-end; gap:8px; margin-top:4px;">
            <a href="{{ route('logs.index') }}"
               style="padding:10px 16px; border-radius:8px; background:#f3f4f6; text-decoration:none; color:#374151; font-size:14px;">
               Limpar
            </a>
            <button type="submit"
                    style="padding:10px 16px; border-radius:8px; background:linear-gradient(135deg, var(--primary), var(--secondary)); color:white; border:none; font-size:14px; cursor:pointer;">
                Filtrar
            </button>
        </div>
    </form>

    {{-- Tabela --}}
    <div style="overflow-x:auto;">
        <table style="width:100%; border-collapse:collapse; font-size:14px;">
            <thead>
                <tr style="background:#f9fafb;">
                    <th style="padding:8px; text-align:left;">Data/Hora</th>
                    <th style="padding:8px; text-align:left;">Usuário</th>
                    <th style="padding:8px; text-align:left;">Model</th>
                    <th style="padding:8px; text-align:left;">ID Registro</th>
                    <th style="padding:8px; text-align:left;">Ação</th>
                    <th style="padding:8px; text-align:left;">Detalhes</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                    <tr style="border-bottom:1px solid #e5e7eb;">
                        <td style="padding:8px;">
                            {{ $log->created_at->format('d/m/Y H:i') }}
                        </td>
                        <td style="padding:8px;">
                            {{ $log->usuario_nome ?? '-' }}<br>
                            <small style="color:#6b7280;">{{ $log->usuario_role ?? '' }}</small>
                        </td>
                        <td style="padding:8px;">{{ $log->model ?? '-' }}</td>
                        <td style="padding:8px;">{{ $log->model_id ?? '-' }}</td>
                        <td style="padding:8px;">{{ $log->action }}</td>
                        <td style="padding:8px;">
                            <a href="{{ route('logs.show', $log->id) }}"
                               style="font-size:13px; text-decoration:none; color:var(--primary-dark);">
                                Ver detalhes
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="padding:12px; text-align:center; color:#6b7280;">
                            Nenhum log encontrado.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Paginação custom Estética PRO --}}
    @if($logs instanceof \Illuminate\Contracts\Pagination\Paginator)
        <div style="margin-top:16px; display:flex; justify-content:center;">
            @include('partials.pagination', ['paginator' => $logs])
        </div>
    @endif
</div>
@endsection