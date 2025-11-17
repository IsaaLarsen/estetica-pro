<?php

namespace App\Http\Controllers;

use App\Models\AgendaBloqueio;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AgendaBloqueioController extends Controller
{
    public function index()
    {
        // Se algum dia você quiser usar os funcionários na tela de listagem,
        // eles já estão carregados aqui:
        $funcionarios = DB::table('funcionarios')
            ->orderBy('nome')
            ->get();

        // Carrega bloqueios com os funcionários relacionados
        $bloqueios = AgendaBloqueio::with('funcionarios')
            ->orderByDesc('inicio')
            ->paginate(20);

        return view('agenda_bloqueios.index', compact('bloqueios', 'funcionarios'));
    }

    public function create()
    {
        $funcionarios = DB::table('funcionarios')
            ->orderBy('nome')
            ->get();

        return view('agenda_bloqueios.create', compact('funcionarios'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'aplicar_todos'   => 'nullable|boolean',
            'funcionarios'    => 'array',
            'funcionarios.*'  => 'exists:funcionarios,id',
            'data_inicio'     => 'required|date',
            'hora_inicio'     => 'required',
            'data_fim'        => 'required|date',
            'hora_fim'        => 'required',
            'motivo'          => 'nullable|string|max:255',
        ]);

        // Se NÃO marcou "todos", precisa selecionar pelo menos 1 funcionário
        if (! $request->boolean('aplicar_todos') && empty($request->funcionarios)) {
            return back()
                ->withErrors(['funcionarios' => 'Selecione pelo menos um funcionário.'])
                ->withInput();
        }

        $inicio = Carbon::parse($request->data_inicio . ' ' . $request->hora_inicio);
        $fim    = Carbon::parse($request->data_fim . ' ' . $request->hora_fim);

        if ($fim->lte($inicio)) {
            return back()
                ->withErrors(['data_fim' => 'Fim deve ser após o início.'])
                ->withInput();
        }

        // Criar bloqueio
        $bloqueio = AgendaBloqueio::create([
            'aplicar_todos' => $request->boolean('aplicar_todos'),
            'inicio'        => $inicio,
            'fim'           => $fim,
            'motivo'        => $request->motivo,
        ]);

        // Se não for para todos, salvar funcionários na pivot
        if (! $bloqueio->aplicar_todos) {
            $bloqueio->funcionarios()->sync($request->funcionarios);
        }

        return redirect()
            ->route('agenda.bloqueios.index')
            ->with('success', 'Bloqueio criado!');
    }

    public function edit($id)
    {
        $bloqueio = AgendaBloqueio::with('funcionarios')->findOrFail($id);

        $funcionarios = DB::table('funcionarios')
            ->orderBy('nome')
            ->get();

        return view('agenda_bloqueios.edit', compact('bloqueio', 'funcionarios'));
    }

    public function update(Request $request, $id)
    {
        $bloqueio = AgendaBloqueio::findOrFail($id);

        $request->validate([
            'aplicar_todos'   => 'nullable|boolean',
            'funcionarios'    => 'array',
            'funcionarios.*'  => 'exists:funcionarios,id',
            'data_inicio'     => 'required|date',
            'hora_inicio'     => 'required',
            'data_fim'        => 'required|date',
            'hora_fim'        => 'required',
            'motivo'          => 'nullable|string|max:255',
        ]);

        if (! $request->boolean('aplicar_todos') && empty($request->funcionarios)) {
            return back()
                ->withErrors(['funcionarios' => 'Selecione pelo menos um funcionário.'])
                ->withInput();
        }

        $inicio = Carbon::parse($request->data_inicio . ' ' . $request->hora_inicio);
        $fim    = Carbon::parse($request->data_fim . ' ' . $request->hora_fim);

        if ($fim->lte($inicio)) {
            return back()
                ->withErrors(['data_fim' => 'Fim deve ser após o início.'])
                ->withInput();
        }

        // Atualiza bloqueio
        $bloqueio->update([
            'aplicar_todos' => $request->boolean('aplicar_todos'),
            'inicio'        => $inicio,
            'fim'           => $fim,
            'motivo'        => $request->motivo,
        ]);

        // Atualiza pivot
        if (! $request->boolean('aplicar_todos')) {
            $bloqueio->funcionarios()->sync($request->funcionarios);
        } else {
            // Se agora é "todos", remove os vínculos específicos
            $bloqueio->funcionarios()->detach();
        }

        return redirect()
            ->route('agenda.bloqueios.index')
            ->with('success', 'Bloqueio atualizado!');
    }

    public function destroy($id)
    {
        $bloqueio = AgendaBloqueio::findOrFail($id);
        $bloqueio->delete();

        return redirect()
            ->route('agenda.bloqueios.index')
            ->with('success', 'Bloqueio removido.');
    }
}
