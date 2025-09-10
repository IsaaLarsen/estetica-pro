<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;

class LoginController extends Controller
{
    public function index()
    {
        return view('auth.login');
    }

    public function autenticar(Request $request)
    {
        $request->validate([
            'cpf' => 'required|string',
            'senha' => 'required|string',
        ]);

        // Remove tudo que não for número (aceita 00000000000 ou 000.000.000-00)
        $cpf = preg_replace('/[^0-9]/', '', $request->cpf);

        // Busca no banco já com CPF limpo
        $usuario = DB::table('usuarios')->where('cpf', $cpf)->first();

        if ($usuario && Hash::check($request->senha, $usuario->senha)) {
            Session::put('usuario', $usuario);
            return redirect()->route('dashboard');
        }

        return back()->withErrors(['cpf' => 'CPF ou senha inválidos.']);
    }

    public function dashboard()
    {
        if (!Session::has('usuario')) {
            return redirect()->route('login');
        }

        return view('dashboard', ['usuario' => Session::get('usuario')]);
    }

    public function logout()
    {
        Session::forget('usuario');
        return redirect()->route('login');
    }
}
