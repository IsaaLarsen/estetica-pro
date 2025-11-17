<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;

class AuthClienteController extends Controller
{
    /**
     * POST /api/auth/cliente/login
     * Body: { email, password }
     */
    public function login(Request $r)
    {
        $r->validate([
            'email'    => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        $cliente = Cliente::where('email', $r->email)->first();

        // compara password (texto) com coluna 'senha' (hash)
        if (!$cliente || !Hash::check($r->password, $cliente->senha ?? '')) {
            throw ValidationException::withMessages([
                'email' => ['Credenciais inválidas.'],
            ]);
        }

        $token = $cliente->createToken('mobile')->plainTextToken;

        return response()->json([
            'token'   => $token,
            'cliente' => [
                'id'    => $cliente->id,
                'nome'  => $cliente->nome,
                'email' => $cliente->email,
            ],
        ]);
    }

    /**
     * POST /api/auth/cliente/register
     * Body: { nome, email, password, telefone?, cpf?, endereco?, data_nascimento?, ativo? }
     * OBS: password (texto) será salvo na coluna 'senha' e hasheado via cast.
     */
    public function register(Request $r)
    {
        $r->validate([
            'nome'            => 'required|string|max:120',
            'email'           => ['required','email','max:150', Rule::unique('clientes','email')],
            'password'        => 'required|string|min:6',
            'telefone'        => 'nullable|string|max:30',
            'cpf'             => ['nullable','string','max:20', Rule::unique('clientes','cpf')->where(function($q){ return true; })],
            'endereco'        => 'nullable|string|max:255',
            'data_nascimento' => 'nullable|date_format:Y-m-d',
            'ativo'           => 'nullable|boolean',
        ]);

        $cliente = Cliente::create([
            'nome'            => $r->nome,
            'email'           => $r->email,
            'senha'           => $r->password, // cast 'hashed' fará o hash
            'telefone'        => $r->telefone,
            'cpf'             => $r->cpf,
            'endereco'        => $r->endereco,
            'data_nascimento' => $r->data_nascimento,
            'ativo'           => $r->boolean('ativo', true),
        ]);

        return response()->json([
            'id'    => $cliente->id,
            'nome'  => $cliente->nome,
            'senha'  => $cliente->senha,
            'email' => $cliente->email,
            'ativo' => $cliente->ativo,

        ], 201);
    }

    /**
     * GET /api/auth/cliente/me  (auth:sanctum)
     */
    public function me(Request $r)
    {
        return response()->json($r->user());
    }

    /**
     * POST /api/auth/cliente/logout  (auth:sanctum)
     */
    public function logout(Request $r)
    {
        // protege contra token nulo
        $token = $r->user()?->currentAccessToken();
        if ($token) {
            $token->delete();
        }
        return response()->json(['ok' => true]);
    }

    public function forgotPassword(Request $r)
{
    $r->validate([
        'email'                  => 'required|email',
        'cpf'                    => 'required|string',
        'endereco'               => 'required|string',
        'new_password'           => 'required|string|min:6|confirmed', // pede new_password_confirmation
    ]);

    // Normaliza CPF (só dígitos)
    $cpf = preg_replace('/\D+/', '', (string) $r->cpf);
    $enderecoBusca = mb_strtolower(trim($r->endereco));

    // Busca com email + cpf (normalizado) + endereço (case/trim insensitive)
    $cliente = \App\Models\Cliente::where('email', $r->email)
        ->where('cpf', $cpf)
        ->whereRaw('LOWER(TRIM(endereco)) = ?', [$enderecoBusca])
        ->first();

    if (!$cliente) {
        return response()->json(['message' => 'Dados não conferem. Verifique e tente novamente.'], 422);
    }

    // Atualiza a senha (coluna 'senha' com cast hashed no Model)
    $cliente->senha = $r->new_password;
    $cliente->save();

    return response()->json(['ok' => true], 200);
}

}
