<?php

namespace App\Http\Controllers;

use App\Models\Cargo;
use App\Models\Funcionario;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Session;

class CargoController extends Controller
{
    /**
     * Verifica se o usuário logado é ADMIN
     */
    private function requireAdmin()
    {
        if (!Session::has('usuario')) {
            return redirect()->route('login');
        }

        $usuario = Session::get('usuario');
        $papel = strtolower($usuario->role ?? $usuario->tipo ?? '');

        if ($papel !== 'admin') {
            abort(403, 'Acesso permitido apenas para administradores.');
        }

        return null;
    }

    /** LISTA */
    public function index()
    {
        if ($resp = $this->requireAdmin()) return $resp;

        $cargos = Cargo::orderBy('nome')->get();

        return view('cargos.index', [
            'cargos'  => $cargos,
            'usuario' => Session::get('usuario'),
        ]);
    }

    /** FORM (CRIAR) – usa a mesma view do editar */
    public function create()
    {
        if ($resp = $this->requireAdmin()) return $resp;

        $cargo = null;

        return view('cargos.form', [
            'cargo'   => $cargo,
            'usuario' => Session::get('usuario'),
        ]);
    }

    /** SALVAR NOVO */
    public function store(Request $request)
    {
        if ($resp = $this->requireAdmin()) return $resp;

        $data = $request->validate([
            'nome'      => ['required', 'string', 'max:255', 'unique:cargos,nome'],
            'descricao' => ['nullable', 'string', 'max:255'],
            'ativo'     => ['nullable', 'boolean'],
        ]);

        $data['ativo'] = $request->has('ativo');
        Cargo::create($data);

        return redirect()->route('cargos.index')
            ->with('success', 'Cargo criado com sucesso!');
    }

    /** FORM (EDITAR) – mesma view do criar, porém com $cargo preenchido */
    public function edit(Cargo $cargo)
    {
        if ($resp = $this->requireAdmin()) return $resp;

        return view('cargos.form', [
            'cargo'   => $cargo,
            'usuario' => Session::get('usuario'),
        ]);
    }

    /** ATUALIZAR */
    public function update(Request $request, Cargo $cargo)
    {
        if ($resp = $this->requireAdmin()) return $resp;

        $data = $request->validate([
            'nome'      => ['required', 'string', 'max:255', Rule::unique('cargos', 'nome')->ignore($cargo->id)],
            'descricao' => ['nullable', 'string', 'max:255'],
            'ativo'     => ['nullable', 'boolean'],
        ]);

        $data['ativo'] = $request->has('ativo');
        $cargo->update($data);

        return redirect()->route('cargos.index')
            ->with('success', 'Cargo atualizado com sucesso!');
    }

    /** REMOVER */
    public function destroy(Cargo $cargo)
    {
        if ($resp = $this->requireAdmin()) return $resp;

        $cargo->delete();

        return redirect()->route('cargos.index')
            ->with('success', 'Cargo excluído com sucesso!');
    }

    /** PÁGINA: Funcionários por cargo (duplo clique na linha da lista) */
    public function funcionarios(Cargo $cargo)
    {
        if ($resp = $this->requireAdmin()) return $resp;

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
