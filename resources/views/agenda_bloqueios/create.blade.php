@extends('layouts.app')

@section('content')
<h2 style="font-size:1.4rem; margin-bottom:1rem;">Novo Bloqueio</h2>

@if ($errors->any())
  <div style="background:#fee2e2; color:#b91c1c; padding:.75rem; border-radius:.5rem; margin-bottom:1rem;">
    <ul style="margin:0; padding-left:1.2rem;">
      @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
      @endforeach
    </ul>
  </div>
@endif

<form method="POST" action="{{ route('agenda.bloqueios.store') }}" style="display:grid; gap:12px; max-width:560px;">
  @csrf

  <label>Funcionário</label>
  <select name="funcionario_id" required>
    <option value="">Selecione...</option>
    @foreach($funcionarios as $f)
      <option value="{{ $f->id }}" @selected(old('funcionario_id')==$f->id)>{{ $f->nome }}</option>
    @endforeach
  </select>

  <div style="display:flex; gap:12px;">
    <div style="flex:1;">
      <label>Data início</label>
      <input type="date" name="data_inicio" value="{{ old('data_inicio') }}" required>
    </div>
    <div style="flex:1;">
      <label>Hora início</label>
      <input type="time" name="hora_inicio" value="{{ old('hora_inicio') }}" required>
    </div>
  </div>

  <div style="display:flex; gap:12px;">
    <div style="flex:1;">
      <label>Data fim</label>
      <input type="date" name="data_fim" value="{{ old('data_fim') }}" required>
    </div>
    <div style="flex:1;">
      <label>Hora fim</label>
      <input type="time" name="hora_fim" value="{{ old('hora_fim') }}" required>
    </div>
  </div>

  <label>Motivo (opcional)</label>
  <input type="text" name="motivo" value="{{ old('motivo') }}" placeholder="Férias, treinamento, manutenção...">

  <div style="display:flex; gap:8px;">
    <button type="submit" style="padding:.6rem 1rem; border:none; background:#7e22ce; color:#fff; border-radius:.6rem; cursor:pointer;">Salvar</button>
    <a href="{{ route('agenda.bloqueios.index') }}" style="padding:.6rem 1rem; border:1px solid #ddd; border-radius:.6rem; text-decoration:none;">Cancelar</a>
  </div>
</form>
@endsection
