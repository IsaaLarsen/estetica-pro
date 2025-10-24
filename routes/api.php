<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

// Models (bypass nos controllers com auth)
use App\Models\Servico;
use App\Models\Funcionario;

// Controller ESPECÍFICO de API que você já tem nessa pasta (pelo seu print)
use App\Http\Controllers\Api\AgendaApiController;

// Ping
Route::get('/ping', fn () => response()->json(['ok' => true]));

// Preflight genérico (uma vez só)
Route::options('/{any}', fn () => response()->noContent())->where('any', '.*');

//
// === Rotas públicas da API (sem login) ===
//

/**
 * Catálogo de serviços — sem passar pelo ServicoController (que exige auth)
 * Ajuste os campos para bater com sua tabela real.
 */
Route::get('/servicos', function () {
    // Se seus nomes forem 'preco' e 'duracao' use exatamente estes.
    return Servico::select('id', 'nome', 'valor', 'descricao', 'duracao_minutos')
        ->where('ativo', 1)
        ->orderBy('nome')
        ->get();
})->withoutMiddleware([
    \App\Http\Middleware\Autenticacao::class,
    \Illuminate\Auth\Middleware\Authenticate::class,
    'auth', 'auth:sanctum', 'verified',
]);

/**
 * Lista de funcionários — sem passar pelo FuncionarioController (que exige auth)
 */
Route::get('/funcionarios', function () {
    return Funcionario::select('id', 'nome')
        ->where('ativo', 1)
        ->orderBy('nome')
        ->get();
})->withoutMiddleware([
    \App\Http\Middleware\Autenticacao::class,
    \Illuminate\Auth\Middleware\Authenticate::class,
    'auth', 'auth:sanctum', 'verified',
]);

/**
 * Slots e criação de agendamento — usamos sua AgendaApiController (na pasta Api)
 * IMPORTANTE: troque 'slots' e 'store' para os nomes reais se forem diferentes.
 */
Route::get('/agenda/slots', [AgendaApiController::class, 'slots'])->withoutMiddleware([
    \App\Http\Middleware\Autenticacao::class,
    \Illuminate\Auth\Middleware\Authenticate::class,
    'auth', 'auth:sanctum', 'verified',
]);

Route::post('/agendamentos', [AgendaApiController::class, 'store'])->withoutMiddleware([
    \App\Http\Middleware\Autenticacao::class,
    \Illuminate\Auth\Middleware\Authenticate::class,
    \Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class, // POST via API sem CSRF
    'auth', 'auth:sanctum', 'verified',
]);
