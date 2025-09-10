<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class Autenticacao
{
    public function handle(Request $request, Closure $next)
    {
        if (!Session::has('usuario')) {
            return redirect()->route('login');
        }

        return $next($request);
    }
}
