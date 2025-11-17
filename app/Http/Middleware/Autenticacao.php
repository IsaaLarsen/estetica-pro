<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class Autenticacao
{
    /**
     * Protege rotas do sistema, mas libera /login (GET e POST) e /logout.
     */
    public function handle(Request $request, Closure $next)
    {
        // Rotas públicas liberadas
        $pathsPublicos = [
            'login',        // GET
            'logout',       // GET/POST (se você quiser liberar GET também)
        ];

        // Se a rota atual é pública, segue o fluxo
        if ($request->is('login') || $request->is('logout')) {
            return $next($request);
        }

        // Se for POST /login, também libera
        if ($request->isMethod('post') && $request->is('login')) {
            return $next($request);
        }

        // A partir daqui, exige sessão 'usuario'
        if (!Session::has('usuario')) {
            return redirect()->route('login');
        }

        return $next($request);
    }
}
