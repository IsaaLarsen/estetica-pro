@extends('layouts.app')

@section('content')
<div class="container" style="max-width: 1000px;">
    <h1 class="mb-3">Funcionários do cargo: <span class="text-primary">{{ $cargo->nome }}</span></h1>

    <a href="{{ route('cargos.index') }}" class="btn btn-outline-secondary mb-3">← Voltar aos Cargos</a>

    @if($funcionarios->isEmpty())
        <div class="alert alert-info">Nenhum funcionário cadastrado com este cargo.</div>
    @else
        <div class="table-responsive">
            <table class="table table-striped align-middle">
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>CPF</th>
                        <th>Email</th>
                        <th>Telefone</th>
                        <th>Ativo</th>
                        <th style="width: 120px;">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($funcionarios as $f)
                        <tr>
                            <td>{{ $f->nome }}</td>
                            <td>{{ $f->cpf }}</td>
                            <td>{{ $f->email }}</td>
                            <td>{{ $f->telefone }}</td>
                            <td>
                                @if($f->ativo)
                                    <span class="badge bg-success">Ativo</span>
                                @else
                                    <span class="badge bg-secondary">Inativo</span>
                                @endif
                            </td>
                            <td>
                                <a class="btn btn-sm btn-outline-primary"
                                   href="{{ route('funcionarios.edit', $f->id) }}">Editar</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection
