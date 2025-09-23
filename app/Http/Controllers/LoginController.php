<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;

class LoginController extends Controller
{
    /** Tela de login */
    public function index()
    {
        // Se já estiver logado, manda pro dashboard
        if (Session::has('usuario')) {
            return redirect()->route('dashboard');
        }

        return view('auth.login');
    }

    /** Autentica CPF + senha e salva usuário normalizado na sessão */
    public function autenticar(Request $request)
    {
        $request->validate([
            'cpf'   => 'required|string',
            'senha' => 'required|string',
        ]);

        // Remove tudo que não for número (aceita 00000000000 e 000.000.000-00)
        $cpf = preg_replace('/\D/', '', $request->cpf);

        // Busca usuário por CPF "limpo"
        $usuario = DB::table('usuarios')->where('cpf', $cpf)->first();

        // Verifica senha
        if ($usuario && Hash::check($request->senha, $usuario->senha)) {
            // Normaliza estrutura para SEMPRE existir "nome"
            $sessao = (object)[
                'id'    => $usuario->id ?? null,
                'nome'  => $usuario->nome ?? ($usuario->name ?? 'Usuário'),
                'cpf'   => $usuario->cpf ?? $cpf,
                'email' => $usuario->email ?? null,
            ];

            // Salva na sessão
            Session::put('usuario', $sessao);

            // Regenera ID de sessão por segurança (previne fixation)
            $request->session()->regenerate();

            return redirect()->route('dashboard');
        }

        return back()->withErrors(['cpf' => 'CPF ou senha inválidos.'])->withInput([
            'cpf' => $request->cpf,
        ]);
    }

    /** Dashboard protegido por sessão (cheque simples) */
    public function dashboard()
    {
        if (!Session::has('usuario')) {
            return redirect()->route('login');
        }

        // Você pode ou não passar $usuario; o layout lê da Session de qualquer forma
        return view('dashboard', [
            'usuario' => Session::get('usuario'),
        ]);
    }

    /** Logout (POST) */
    public function logout(Request $request)
    {
        Session::forget('usuario');
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
