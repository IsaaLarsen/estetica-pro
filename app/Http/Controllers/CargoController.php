<?php

namespace App\Http\Controllers;

use App\Models\Cargo;
use App\Models\Funcionario;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Session;

class CargoController extends Controller
{
    /** LISTA */
    public function index()
    {
        // protege por sessão
        if (!Session::has('usuario')) {
            return redirect()->route('login');
        }

        $cargos = Cargo::orderBy('nome')->get();

        return view('cargos.index', [
            'cargos'  => $cargos,
            'usuario' => Session::get('usuario'), // <-- envia o logado pra view
        ]);
    }

    /** FORM (CRIAR) – usa a mesma view do editar */
    public function create()
    {
        if (!Session::has('usuario')) {
            return redirect()->route('login');
        }

        $cargo = null; // indica modo "criar" na view
        return view('cargos.form', [
            'cargo'   => $cargo,
            'usuario' => Session::get('usuario'),
        ]);
    }

    /** SALVAR NOVO */
    public function store(Request $request)
    {
        if (!Session::has('usuario')) {
            return redirect()->route('login');
        }

        $data = $request->validate([
            'nome'      => ['required','string','max:255','unique:cargos,nome'],
            'descricao' => ['nullable','string','max:255'],
            'ativo'     => ['nullable','boolean'],
        ]);

        $data['ativo'] = $request->has('ativo'); // checkbox
        Cargo::create($data);

        return redirect()->route('cargos.index')->with('success', 'Cargo criado com sucesso!');
    }

    /** FORM (EDITAR) – mesma view do criar, porém com $cargo preenchido */
    public function edit(Cargo $cargo)
    {
        if (!Session::has('usuario')) {
            return redirect()->route('login');
        }

        return view('cargos.form', [
            'cargo'   => $cargo,
            'usuario' => Session::get('usuario'),
        ]);
    }

    /** ATUALIZAR */
    public function update(Request $request, Cargo $cargo)
    {
        if (!Session::has('usuario')) {
            return redirect()->route('login');
        }

        $data = $request->validate([
            'nome'      => ['required','string','max:255', Rule::unique('cargos','nome')->ignore($cargo->id)],
            'descricao' => ['nullable','string','max:255'],
            'ativo'     => ['nullable','boolean'],
        ]);

        $data['ativo'] = $request->has('ativo');
        $cargo->update($data);

        return redirect()->route('cargos.index')->with('success', 'Cargo atualizado com sucesso!');
    }

    /** REMOVER */
    public function destroy(Cargo $cargo)
    {
        if (!Session::has('usuario')) {
            return redirect()->route('login');
        }

        $cargo->delete();
        return redirect()->route('cargos.index')->with('success', 'Cargo excluído com sucesso!');
    }

    /** PÁGINA: Funcionários por cargo (duplo clique na linha da lista) */
    public function funcionarios(Cargo $cargo)
    {
        if (!Session::has('usuario')) {
            return redirect()->route('login');
        }

        // funcionarios.cargo guarda o NOME do cargo (se depois migrar pra cargo_id, ajuste aqui)
        $funcionarios = Funcionario::where('cargo', $cargo->nome)
            ->orderBy('nome')
            ->get();

        return view('cargos.funcionarios', [
            'cargo'        => $cargo,
            'funcionarios' => $funcionarios,
            'usuario'      => Session::get('usuario'),
        ]);
    }
}
