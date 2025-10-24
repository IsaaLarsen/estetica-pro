<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

// IMPORTS dos seus middlewares
use App\Http\Middleware\CorsSimple;
use App\Http\Middleware\Autenticacao;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        api: __DIR__ . '/../routes/api.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Aliases que você já usa nas rotas
        $middleware->alias([
            'auth.sistema' => Autenticacao::class,
        ]);

        // Aplica o CORS em todas as rotas (global)
        $middleware->append(CorsSimple::class);

        // Se preferir só no grupo API, troque a linha acima por:
        // $middleware->group('api', [ CorsSimple::class ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })
    ->create();
