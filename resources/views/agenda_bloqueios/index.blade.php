@extends('layouts.app')

@section('content')
<div class="flex items-center justify-between mb-4">
  <h2 style="font-size:1.4rem;">Bloqueios de Agenda</h2>
  <a href="{{ route('agenda.bloqueios.create') }}" class="btn" style="padding:.6rem 1rem; border-radius:.6rem; background:#7e22ce; color:#fff;">Novo bloqueio</a>
</div>

@if (session('success'))
  <div style="background:#dcfce7; color:#166534; padding:.6rem; border-radius:.5rem; margin-bottom:1rem;">
    {{ session('success') }}
  </div>
@endif

<table style="width:100%; border-collapse:collapse;">
  <thead>
    <tr style="text-align:left; border-bottom:1px solid #e5e7eb;">
      <th>Funcionário</th><th>Início</th><th>Fim</th><th>Motivo</th><th>Ações</th>
    </tr>
  </thead>
  <tbody>
    @forelse($bloqueios as $b)
    <tr style="border-bottom:1px solid #f3f4f6;">
      <td>{{ $b->funcionario->nome ?? '—' }}</td>
      <td>{{ $b->inicio->format('d/m/Y H:i') }}</td>
      <td>{{ $b->fim->format('d/m/Y H:i') }}</td>
      <td>{{ $b->motivo }}</td>
      <td>
        <form method="POST" action="{{ route('agenda.bloqueios.destroy',$b) }}" onsubmit="return confirm('Remover bloqueio?')">
          @csrf @method('DELETE')
          <button type="submit" style="border:none; background:#ef4444; color:#fff; padding:.4rem .6rem; border-radius:.5rem; cursor:pointer;">Excluir</button>
        </form>
      </td>
    </tr>
    @empty
    <tr><td colspan="5">Nenhum bloqueio cadastrado.</td></tr>
    @endforelse
  </tbody>
</table>

<div style="margin-top:1rem;">
  {{ $bloqueios->links() }}
</div>
@endsection
