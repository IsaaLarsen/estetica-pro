<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RoleMiddleware
{
    /**
     * Verifica se o usuário tem um dos papéis permitidos (role ou tipo),
     * pegando o usuário de auth() OU das sessões 'usuario' / 'user'.
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        // Nunca bloquear a página de login (GET/POST) nem logout
        if ($request->is('login') || ($request->isMethod('post') && $request->is('login')) || $request->is('logout')) {
            return $next($request);
        }

        // 1) Guard padrão (se estiver usando)
        $user = auth()->user();

        // 2) Sessão usada pelo teu LoginController
        if (!$user && session()->has('usuario')) {
            $user = (object) session('usuario'); // estrutura normalizada
        }

        // 3) Fallback compatível (caso exista 'user')
        if (!$user && session()->has('user')) {
            $user = (object) session('user');
        }

        // Papel pode estar em 'role' (sessão normalizada) ou 'tipo' (tabela usuarios)
        $papel = strtolower($user->role ?? $user->tipo ?? '');

        if (!$user || !in_array($papel, array_map('strtolower', $roles))) {
            abort(403, 'Acesso negado.');
        }

        return $next($request);
    }
}
