<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Comissões - Estética PRO</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
:root {
  --primary:#ec4899; --secondary:#7e22ce; --text:#1f2937; --text-light:#6b7280;
  --success:#10b981; --warning:#f59e0b; --danger:#ef4444; --sidebar-width:260px;
}
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:'Poppins',sans-serif;background:#f9fafb;color:var(--text);min-height:100vh;display:flex}

/* Sidebar */
.sidebar{width:var(--sidebar-width);background:linear-gradient(180deg,var(--primary),var(--secondary));
  color:#fff;display:flex;flex-direction:column;box-shadow:0 0 25px rgba(0,0,0,.1)}
.sidebar-header{padding:24px;text-align:center;border-bottom:1px solid rgba(255,255,255,.1)}
.sidebar-header h1{font-size:24px;font-weight:700}
.sidebar-nav{flex:1;padding:20px 16px;display:flex;flex-direction:column}
.nav-item{display:flex;align-items:center;padding:14px 16px;border-radius:12px;margin-bottom:8px;
  transition:.3s;text-decoration:none;color:#fff;font-weight:500}
.nav-item:hover{background:rgba(255,255,255,.1)}
.nav-item.active{background:rgba(255,255,255,.15);box-shadow:0 4px 12px rgba(0,0,0,.1)}
.nav-item i{width:24px;margin-right:12px;font-size:18px}
.sidebar-footer{padding:16px;border-top:1px solid rgba(255,255,255,.1)}

/* Main */
.main{flex:1;display:flex;flex-direction:column}
.topbar{height:70px;background:#fff;box-shadow:0 2px 10px rgba(0,0,0,.05);display:flex;align-items:center;
  justify-content:space-between;padding:0 24px}
.user-info{display:flex;align-items:center}
.user-avatar{width:40px;height:40px;border-radius:50%;background:linear-gradient(135deg,var(--primary),var(--secondary));
  display:flex;align-items:center;justify-content:center;color:#fff;font-weight:600;margin-right:12px}
.user-details h3{font-size:16px;font-weight:600}
.user-details p{font-size:13px;color:var(--text-light)}
.content{padding:24px;flex:1}

/* Cards/controls */
.page-title{font-size:28px;font-weight:700;background:linear-gradient(135deg,var(--primary),var(--secondary));
  -webkit-background-clip:text;-webkit-text-fill-color:transparent}
.card{background:#fff;border-radius:16px;box-shadow:0 4px 20px rgba(0,0,0,.05);padding:20px;margin-bottom:24px}
.grid{display:grid;gap:16px}
.grid-3{grid-template-columns:repeat(3,minmax(0,1fr))}
.stat{display:flex;align-items:center;justify-content:space-between}
.stat h4{font-size:14px;color:var(--text-light);font-weight:600}
.stat .v{font-size:22px;font-weight:700}
.filter{display:flex;gap:12px;flex-wrap:wrap;margin-bottom:12px}
.input,.select{padding:10px 12px;border:2px solid #e5e7eb;border-radius:12px;background:#fff;font-family:inherit}
.btn{display:inline-flex;align-items:center;gap:8px;padding:10px 16px;border-radius:12px;border:none;cursor:pointer;font-weight:600;text-decoration:none}
.btn-primary{background:linear-gradient(135deg,var(--primary),var(--secondary));color:#fff;box-shadow:0 4px 14px rgba(236,72,153,.4)}
.btn-secondary{background:#0EA5E9;color:#fff}

/* Table */
.table{width:100%;border-collapse:separate;border-spacing:0 8px}
.table th{font-size:12px;text-transform:uppercase;color:var(--text-light);text-align:left;padding:8px 12px}
.table td{background:#fff;padding:14px 12px}
.row{border-radius:12px;box-shadow:0 1px 4px rgba(0,0,0,.04)}
.badge{padding:6px 10px;border-radius:999px;font-size:12px;font-weight:700;display:inline-block}
.badge-pendente{background:#fef3c7;color:#92400e}
.badge-pago{background:#dcfce7;color:#065f46}
.badge-estornado{background:#fee2e2;color:#991b1b}
.actions{display:flex;gap:8px;flex-wrap:wrap}

@media(max-width:768px){
  .sidebar{width:70px}
  .sidebar-header h1,.nav-item span{display:none}
  .nav-item{justify-content:center}
  .nav-item i{margin:0}
}
</style>
</head>
<body>

  <!-- Sidebar -->
  <aside class="sidebar">
    <div class="sidebar-header"><h1>Estética PRO</h1></div>
    <nav class="sidebar-nav">
      <a href="{{ route('dashboard') }}" class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
        <i class="fas fa-chart-line"></i><span>Dashboard</span>
      </a>
      <a href="{{ route('funcionarios.index') }}" class="nav-item {{ request()->routeIs('funcionarios.*') ? 'active' : '' }}">
        <i class="fas fa-users"></i><span>Funcionários</span>
      </a>
      <a href="{{ route('servicos.index') }}" class="nav-item {{ request()->routeIs('servicos.*') ? 'active' : '' }}">
        <i class="fas fa-scissors"></i><span>Serviços</span>
      </a>
      <a href="{{ route('agenda.index') }}" class="nav-item {{ request()->routeIs('agenda.*') ? 'active' : '' }}">
        <i class="fas fa-calendar-alt"></i><span>Agenda</span>
      </a>
      <a href="{{ route('comissoes.index') }}" class="nav-item {{ request()->routeIs('comissoes.*') ? 'active' : '' }}">
        <i class="fas fa-hand-holding-usd"></i><span>Comissões</span>
      </a>
      <a href="{{ route('clientes.index') }}" class="nav-item {{ request()->routeIs('clientes.*') ? 'active' : '' }}">
        <i class="fas fa-user"></i><span>Clientes</span>
      </a>
      <a href="{{ route('cargos.index') }}" class="nav-item {{ request()->routeIs('cargos.*') ? 'active' : '' }}">
        <i class="fas fa-briefcase"></i><span>Cargos</span>
      </a>
    </nav>
    <div class="sidebar-footer">
      <form method="POST" action="{{ route('logout') }}" style="width:100%;"> @csrf
        <button type="submit" class="nav-item" style="width:100%;background:none;border:none;color:#fff;cursor:pointer;">
          <i class="fas fa-sign-out-alt"></i><span>Sair</span>
        </button>
      </form>
    </div>
  </aside>

  <!-- Main -->
  <div class="main">
    <div class="topbar">
      <div class="user-info">
        <div class="user-avatar">EP</div>
        <div class="user-details">
          <h3>{{ $usuario->nome ?? 'Usuário' }}</h3>
          <p>Administrador</p>
        </div>
      </div>
    </div>

    <div class="content">
      <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px">
        <h1 class="page-title">Comissões</h1>
      </div>

      @if (session('success'))
        <div class="card" style="border-left:4px solid var(--success);color:#065f46;background:#ecfdf5">
          {{ session('success') }}
        </div>
      @endif
      @if (session('error'))
        <div class="card" style="border-left:4px solid var(--danger);color:#7f1d1d;background:#fee2e2">
          {{ session('error') }}
        </div>
      @endif

      <!-- Totais -->
      <div class="grid grid-3">
        <div class="card stat">
          <h4>Pendentes</h4>
          <div class="v">R$ {{ number_format($totais->pendente ?? 0, 2, ',', '.') }}</div>
        </div>
        <div class="card stat">
          <h4>Pagas</h4>
          <div class="v">R$ {{ number_format($totais->pago ?? 0, 2, ',', '.') }}</div>
        </div>
        <div class="card stat">
          <h4>Total</h4>
          <div class="v">R$ {{ number_format($totais->total ?? 0, 2, ',', '.') }}</div>
        </div>
      </div>

      <!-- Filtros -->
      <div class="card">
        <form method="GET" class="filter">
          <select name="funcionario_id" class="select">
            <option value="">Todos os funcionários</option>
            @foreach($funcionarios as $f)
              <option value="{{ $f->id }}" {{ request('funcionario_id')==$f->id?'selected':'' }}>
                {{ $f->nome }}
              </option>
            @endforeach
          </select>

          <select name="status" class="select">
            <option value="">Todos status</option>
            @foreach(['pendente','pago','estornado'] as $s)
              <option value="{{ $s }}" {{ request('status')===$s?'selected':'' }}>{{ ucfirst($s) }}</option>
            @endforeach
          </select>

          <input type="date" name="de"  value="{{ request('de') }}"  class="input">
          <input type="date" name="ate" value="{{ request('ate') }}" class="input">

          <button class="btn btn-primary"><i class="fas fa-filter"></i> Filtrar</button>
          <a href="{{ route('comissoes.index') }}" class="btn btn-secondary"><i class="fas fa-eraser"></i> Limpar</a>
        </form>
      </div>

      <!-- Tabela -->
      <div class="card">
        <table class="table">
          <thead>
            <tr>
              <th>Data</th>
              <th>Funcionário</th>
              <th>Cliente</th>
              <th>Serviço</th>
              <th>Preço</th>
              <th>%</th>
              <th>Comissão</th>
              <th>Status</th>
              <th style="width:220px">Ações</th>
            </tr>
          </thead>
          <tbody>
            @forelse($comissoes as $c)
              <tr class="row">
                <td>{{ \Carbon\Carbon::parse($c->data_atendimento)->format('d/m/Y H:i') }}</td>
                <td>{{ $c->funcionario_nome }}</td>
                <td>{{ $c->cliente_nome }}</td>
                <td>{{ $c->servico_nome }}</td>
                <td>R$ {{ number_format($c->valor_servico,2,',','.') }}</td>
                <td>{{ number_format($c->percentual,2,',','.') }}%</td>
                <td><strong>R$ {{ number_format($c->valor_comissao,2,',','.') }}</strong></td>
                <td>
                  @php $map=['pendente'=>'badge-pendente','pago'=>'badge-pago','estornado'=>'badge-estornado']; @endphp
                  <span class="badge {{ $map[$c->status] ?? 'badge-pendente' }}">{{ ucfirst($c->status) }}</span>
                  @if($c->pago_em)
                    <small style="display:block;color:var(--text-light)">em {{ \Carbon\Carbon::parse($c->pago_em)->format('d/m/Y') }}</small>
                  @endif
                </td>
                <td class="actions">
                  @if($c->status!=='pago')
                  <form method="POST" action="{{ route('comissoes.pagar',$c->id) }}">
                    @csrf
                    <button class="btn btn-primary" onclick="return confirm('Marcar como paga?')">
                      <i class="fas fa-check"></i> Pagar
                    </button>
                  </form>
                  @endif
                  @if($c->status!=='estornado')
                  <form method="POST" action="{{ route('comissoes.estornar',$c->id) }}">
                    @csrf
                    <button class="btn" style="background:#fee2e2;color:#991b1b" onclick="return confirm('Estornar esta comissão?')">
                      <i class="fas fa-undo-alt"></i> Estornar
                    </button>
                  </form>
                  @endif
                </td>
              </tr>
            @empty
              <tr><td colspan="9" style="color:var(--text-light)">Nenhuma comissão encontrada.</td></tr>
            @endforelse
          </tbody>
        </table>

        <div style="margin-top:16px">
          {{ $comissoes->links() }}
        </div>
      </div>
    </div>
  </div>
</body>
</html>
