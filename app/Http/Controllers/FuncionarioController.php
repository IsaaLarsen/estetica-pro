<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class FuncionarioController extends Controller
{
    public function index()
    {
        if (!Session::has('usuario')) {
            return redirect()->route('login');
        }

        $funcionarios = DB::table('funcionarios')->get();

        return view('funcionarios.index', [
            'usuario'      => Session::get('usuario'),
            'funcionarios' => $funcionarios
        ]);
    }

    public function create()
    {
        if (!Session::has('usuario')) {
            return redirect()->route('login');
        }

        return view('funcionarios.create', [
            'usuario'     => Session::get('usuario'),
            'funcionario' => null
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nome'          => 'required|string|max:255',
            'cpf'           => 'required|string|max:14|unique:funcionarios,cpf',
            'email'         => 'required|email|unique:funcionarios,email',
            'cargo'         => 'required|string|max:100',
            'especialidade' => 'nullable|string|max:100',
            'telefone'      => 'nullable|string|max:20',
            'endereco'      => 'nullable|string|max:255',
            'ativo'         => 'nullable|boolean'
        ]);

        $cpf = preg_replace('/\D/', '', $request->cpf);

        DB::table('funcionarios')->insert([
            'nome'          => $request->nome,
            'cpf'           => $cpf,
            'email'         => $request->email,
            'cargo'         => $request->cargo,
            'especialidade' => $request->especialidade,
            'telefone'      => $request->telefone,
            'endereco'      => $request->endereco,
            'ativo'         => $request->has('ativo') ? 1 : 0,
            'created_at'    => now(),
            'updated_at'    => now(),
        ]);

        return redirect()->route('funcionarios.index')
            ->with('success', 'Funcionário cadastrado com sucesso!');
    }

    public function edit($id)
    {
        if (!Session::has('usuario')) {
            return redirect()->route('login');
        }

        $funcionario = DB::table('funcionarios')->where('id', $id)->first();

        return view('funcionarios.create', [
            'usuario'     => Session::get('usuario'),
            'funcionario' => $funcionario
        ]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nome'          => 'required|string|max:255',
            'cpf'           => 'required|string|max:14|unique:funcionarios,cpf,' . $id,
            'email'         => 'required|email|unique:funcionarios,email,' . $id,
            'cargo'         => 'required|string|max:100',
            'especialidade' => 'nullable|string|max:100',
            'telefone'      => 'nullable|string|max:20',
            'endereco'      => 'nullable|string|max:255',
            'ativo'         => 'nullable|boolean'
        ]);

        $cpf = preg_replace('/\D/', '', $request->cpf);

        DB::table('funcionarios')->where('id', $id)->update([
            'nome'          => $request->nome,
            'cpf'           => $cpf,
            'email'         => $request->email,
            'cargo'         => $request->cargo,
            'especialidade' => $request->especialidade,
            'telefone'      => $request->telefone,
            'endereco'      => $request->endereco,
            'ativo'         => $request->has('ativo') ? 1 : 0,
            'updated_at'    => now(),
        ]);

        return redirect()->route('funcionarios.index')
            ->with('success', 'Funcionário atualizado com sucesso!');
    }

    public function destroy($id)
    {
        DB::table('funcionarios')->where('id', $id)->delete();

        return redirect()->route('funcionarios.index')
            ->with('success', 'Funcionário excluído com sucesso!');
    }
}
