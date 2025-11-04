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
        // novo: senha opcional no cadastro via painel
        'senha'           => 'nullable|string|min:6',
    ]);

    $cpf      = preg_replace('/\D+/', '', (string) $request->cpf);
    $telefone = $request->telefone ? preg_replace('/\D+/', '', (string) $request->telefone) : null;

    // se o campo senha vier vazio, usa padrão do .env (ou 123456)
    $senhaPura = $request->filled('senha')
        ? $request->senha
        : config('auth.defaults_cliente_password', '123456');

    DB::table('clientes')->insert([
        'nome'            => $request->nome,
        'cpf'             => $cpf,
        'telefone'        => $telefone,
        'email'           => $request->email,
        'endereco'        => $request->endereco,
        'data_nascimento' => $request->data_nascimento,
        'ativo'           => $request->boolean('ativo') ? 1 : 0,
        'senha'           => bcrypt($senhaPura), // <<< importante: hash aqui, pois estamos no Query Builder
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
            // novo: troca de senha opcional
            'senha'           => 'nullable|string|min:6',
        ]);

        $cpf      = preg_replace('/\D+/', '', (string) $request->cpf);
        $telefone = $request->telefone ? preg_replace('/\D+/', '', (string) $request->telefone) : null;

        $payload = [
            'nome'            => $request->nome,
            'cpf'             => $cpf,
            'telefone'        => $telefone,
            'email'           => $request->email,
            'endereco'        => $request->endereco,
            'data_nascimento' => $request->data_nascimento,
            'ativo'           => $request->boolean('ativo') ? 1 : 0,
            'updated_at'      => now(),
        ];

        // se o usuário preencher a senha no formulário de edição, atualiza (hash)
        if ($request->filled('senha')) {
            $payload['senha'] = bcrypt($request->senha);
        }

        DB::table('clientes')->where('id', $id)->update($payload);

        return redirect()->route('clientes.index')->with('success', 'Cliente atualizado com sucesso!');
    }
    public function destroy($id)
    {
        DB::table('clientes')->where('id', $id)->delete();

        return redirect()->route('clientes.index')->with('success', 'Cliente excluído com sucesso!');
    }

    public function register(Request $r)
{
    $r->validate([
        'nome' => 'required|string|max:100',
        'email' => 'required|email|unique:clientes,email',
        'telefone' => 'nullable|string|max:20',
        'senha' => 'required|string|min:6',
    ]);

    $cliente = \App\Models\Cliente::create([
        'nome' => $r->nome,
        'email' => $r->email,
        'telefone' => $r->telefone,
        'senha' => bcrypt($r->senha),
        'ativo' => 1,
    ]);

    return response()->json($cliente, 201);
}

}
