@extends('layouts.app')

@section('content')
<h2 style="font-size:1.4rem; margin-bottom:1rem;">Configurações da Agenda</h2>

@if (session('success'))
  <div style="background:#dcfce7; color:#166534; padding:.6rem; border-radius:.5rem; margin-bottom:1rem;">
    {{ session('success') }}
  </div>
@endif

@if ($errors->any())
  <div style="background:#fee2e2; color:#b91c1c; padding:.75rem; border-radius:.5rem; margin-bottom:1rem;">
    <ul style="margin:0; padding-left:1.2rem;">
      @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
      @endforeach
    </ul>
  </div>
@endif

<form method="POST" action="{{ route('settings.update') }}" style="display:grid; gap:12px; max-width:480px;">
  @csrf

  <div style="display:flex; gap:12px;">
    <div style="flex:1;">
      <label>Início do expediente</label>
      <input type="time" name="expediente_inicio" value="{{ old('expediente_inicio', $inicio) }}" required>
    </div>
    <div style="flex:1;">
      <label>Fim do expediente</label>
      <input type="time" name="expediente_fim" value="{{ old('expediente_fim', $fim) }}" required>
    </div>
  </div>

  <p style="color:#6b7280;">Dica: para permitir agendamentos após as 18:00, aumente o “Fim do expediente” (ex.: 20:00).</p>

  <div style="display:flex; gap:8px;">
    <button type="submit" style="padding:.6rem 1rem; border:none; background:#0ea5e9; color:#fff; border-radius:.6rem; cursor:pointer;">Salvar</button>
    <a href="{{ route('agenda.index') }}" style="padding:.6rem 1rem; border:1px solid #ddd; border-radius:.6rem; text-decoration:none;">Voltar à agenda</a>
  </div>
</form>
@endsection
