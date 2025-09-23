<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class ClienteController extends Controller
{
    public function index()
    {
        if (!Session::has('usuario')) {
            return redirect()->route('login');
        }

        // Se quiser paginação, troque ->get() por ->paginate(10)
        $clientes = DB::table('clientes')->orderBy('nome')->get();

        return view('clientes.index', [
            'usuario'  => Session::get('usuario'),
            'clientes' => $clientes,
        ]);
    }

    public function create()
    {
        if (!Session::has('usuario')) {
            return redirect()->route('login');
        }

        return view('clientes.create', [
            'usuario' => Session::get('usuario'),
            'cliente' => null,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nome'            => 'required|string|max:255',
            'cpf'             => 'required|string|max:18|unique:clientes,cpf',
            'telefone'        => 'nullable|string|max:30',
            'email'           => 'nullable|email|max:255|unique:clientes,email',
            'endereco'        => 'nullable|string|max:255',
            'data_nascimento' => 'nullable|date',
            'ativo'           => 'nullable|boolean',
        ]);

        $cpf      = preg_replace('/\D+/', '', (string) $request->cpf);
        $telefone = $request->telefone ? preg_replace('/\D+/', '', (string) $request->telefone) : null;

        DB::table('clientes')->insert([
            'nome'            => $request->nome,
            'cpf'             => $cpf,
            'telefone'        => $telefone,
            'email'           => $request->email,
            'endereco'        => $request->endereco,
            'data_nascimento' => $request->data_nascimento,
            // importante: usa boolean('ativo') + hidden input na view
            'ativo'           => $request->boolean('ativo') ? 1 : 0,
            'created_at'      => now(),
            'updated_at'      => now(),
        ]);

        return redirect()->route('clientes.index')->with('success', 'Cliente cadastrado com sucesso!');
    }

    public function edit($id)
    {
        if (!Session::has('usuario')) {
            return redirect()->route('login');
        }

        $cliente = DB::table('clientes')->where('id', $id)->first();
        if (!$cliente) {
            return redirect()->route('clientes.index')->with('error', 'Cliente não encontrado.');
        }

        // Reutiliza a MESMA view de cadastro para edição
        return view('clientes.create', [
            'usuario' => Session::get('usuario'),
            'cliente' => $cliente,
        ]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nome'            => 'required|string|max:255',
            'cpf'             => 'required|string|max:18|unique:clientes,cpf,' . $id,
            'telefone'        => 'nullable|string|max:30',
            'email'           => 'nullable|email|max:255|unique:clientes,email,' . $id,
            'endereco'        => 'nullable|string|max:255',
            'data_nascimento' => 'nullable|date',
            'ativo'           => 'nullable|boolean',
        ]);

        $cpf      = preg_replace('/\D+/', '', (string) $request->cpf);
        $telefone = $request->telefone ? preg_replace('/\D+/', '', (string) $request->telefone) : null;

        DB::table('clientes')->where('id', $id)->update([
            'nome'            => $request->nome,
            'cpf'             => $cpf,
            'telefone'        => $telefone,
            'email'           => $request->email,
            'endereco'        => $request->endereco,
            'data_nascimento' => $request->data_nascimento,
            // idem aqui
            'ativo'           => $request->boolean('ativo') ? 1 : 0,
            'updated_at'      => now(),
        ]);

        return redirect()->route('clientes.index')->with('success', 'Cliente atualizado com sucesso!');
    }

    public function destroy($id)
    {
        DB::table('clientes')->where('id', $id)->delete();

        return redirect()->route('clientes.index')->with('success', 'Cliente excluído com sucesso!');
    }
}
