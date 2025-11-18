<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\FuncionarioController;
use App\Http\Controllers\ServicoController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\CargoController;
use App\Http\Controllers\AgendaController;
use App\Http\Controllers\AgendaBloqueioController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\ComissaoController;
use App\Http\Controllers\RelatorioController;
use App\Http\Controllers\FeedbackController;

Route::get('/', fn() => redirect()->route('login'));

// ===========================
// Login / Logout
// ===========================
Route::get('/login', [LoginController::class, 'index'])->name('login');
Route::post('/login', [LoginController::class, 'autenticar'])->name('login.autenticar');

Route::post('/me/senha', [LoginController::class, 'updatePassword'])
    ->name('me.senha.update')
    ->middleware(['panel']);

// Logout (POST preferido) + fallback GET para botões/link normais
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
Route::get('/logout', [LoginController::class, 'logout']);

// ===========================
// Busca rápida de clientes
// ===========================
Route::get('/clientes/search', [ClienteController::class, 'search'])
    ->name('clientes.search')
    ->middleware(['panel','role:admin,funcionario']);

// ===========================
// Dashboard (apenas admin e funcionário)
// ===========================
Route::get('/dashboard', [LoginController::class, 'dashboard'])
    ->middleware(['panel', 'role:admin,funcionario'])
    ->name('dashboard');

// (opcional) padrões de parâmetro numéricos
Route::pattern('funcionario', '[0-9]+');
Route::pattern('servico',     '[0-9]+');
Route::pattern('cliente',     '[0-9]+');
Route::pattern('cargo',       '[0-9]+');

// ===========================
// Funcionários CRUD (sem show) + Redefinir Senha
// ===========================
Route::resource('funcionarios', FuncionarioController::class)
    ->except(['show'])
    ->middleware(['panel', 'role:admin']);

// 🔐 Redefinição de senha do usuário vinculado ao funcionário
Route::post('/funcionarios/{funcionario}/reset-senha', [FuncionarioController::class, 'resetSenha'])
    ->name('funcionarios.resetSenha')
    ->middleware(['panel', 'role:admin']);

// ===========================
// Serviços CRUD (sem show)
// ===========================
Route::resource('servicos', ServicoController::class)
    ->except(['show'])
    ->middleware(['panel', 'role:admin,funcionario']);

// ===========================
// Clientes CRUD (sem show)
// ===========================
Route::resource('clientes', ClienteController::class)
    ->except(['show'])
    ->middleware(['panel', 'role:admin']);

// ===========================
// Cargos CRUD (sem show)
// ===========================
Route::get('/cargos',                [CargoController::class, 'index'])->name('cargos.index')->middleware(['panel','role:admin']);
Route::get('/cargos/create',         [CargoController::class, 'create'])->name('cargos.create')->middleware(['panel','role:admin']);
Route::post('/cargos',               [CargoController::class, 'store'])->name('cargos.store')->middleware(['panel','role:admin']);
Route::get('/cargos/{cargo}/edit',   [CargoController::class, 'edit'])->name('cargos.edit')->middleware(['panel','role:admin']);
Route::put('/cargos/{cargo}',        [CargoController::class, 'update'])->name('cargos.update')->middleware(['panel','role:admin']);
Route::delete('/cargos/{cargo}',     [CargoController::class, 'destroy'])->name('cargos.destroy')->middleware(['panel','role:admin']);
Route::get('/cargos/{cargo}/funcionarios', [CargoController::class, 'funcionarios'])->name('cargos.funcionarios')->middleware(['panel','role:admin']);

// 🔍 Rota de teste da sessão (debug opcional)
Route::get('/teste-sessao', fn() => response()->json(session()->all()));

// ===========================
// AGENDA - ROTAS SIMPLIFICADAS
// ===========================

// ROTA PARA MINHA AGENDA (FUNCIONÁRIOS) - ACESSO LIVRE PARA AUTENTICADOS
Route::get('/minha-agenda', [AgendaController::class, 'minhaAgenda'])
    ->name('minha.agenda')
    ->middleware('panel');

Route::get('/minha-agenda/events', [AgendaController::class, 'meusEvents'])
    ->name('minha.agenda.events')
    ->middleware('panel');

// ROTA PARA AGENDA COMPLETA (ADMIN) - ACESSO LIVRE PARA AUTENTICADOS
Route::get('/agenda', [AgendaController::class, 'index'])
    ->name('agenda.index')
    ->middleware('panel');

Route::get('/agenda/events', [AgendaController::class, 'events'])
    ->name('agenda.events')
    ->middleware('panel');

// ROTAS COMPARTILHADAS (admin e funcionário)
Route::middleware(['panel'])->group(function () {
    Route::get('/agenda/create', [AgendaController::class, 'create'])->name('agenda.create');
    Route::post('/agenda', [AgendaController::class, 'store'])->name('agenda.store');
    Route::get('/agenda/{agenda}/edit', [AgendaController::class, 'edit'])->name('agenda.edit');
    Route::put('/agenda/{agenda}', [AgendaController::class, 'update'])->name('agenda.update');
    Route::post('/agenda/{agenda}/status', [AgendaController::class, 'updateStatus'])->name('agenda.status.update');
    Route::put('/agenda/{agenda}/status', [AgendaController::class, 'updateStatus'])->name('agenda.updateStatus');
});

// ===========================
// Bloqueios de agenda (apenas admin)
// ===========================
Route::middleware(['panel', 'role:admin'])->group(function () {
    Route::get('/agenda/bloqueios', [AgendaBloqueioController::class, 'index'])->name('agenda.bloqueios.index');
    Route::get('/agenda/bloqueios/create', [AgendaBloqueioController::class, 'create'])->name('agenda.bloqueios.create');
    Route::post('/agenda/bloqueios', [AgendaBloqueioController::class, 'store'])->name('agenda.bloqueios.store');
    Route::get('/agenda/bloqueios/{bloqueio}/edit', [AgendaBloqueioController::class, 'edit'])->name('agenda.bloqueios.edit');
    Route::put('/agenda/bloqueios/{bloqueio}', [AgendaBloqueioController::class, 'update'])->name('agenda.bloqueios.update');
    Route::delete('/agenda/bloqueios/{bloqueio}', [AgendaBloqueioController::class, 'destroy'])->name('agenda.bloqueios.destroy');
});

// ===========================
// Configurações da agenda (apenas admin)
// ===========================
Route::middleware(['panel', 'role:admin'])->group(function () {
    Route::get('/settings/agenda', [SettingController::class, 'edit'])->name('settings.edit');
    Route::post('/settings/agenda', [SettingController::class, 'update'])->name('settings.update');

    // 👉 Dias especiais de expediente (exceções)
    Route::post('/settings/agenda/excecoes', [SettingController::class, 'storeExcecao'])->name('settings.excecoes.store');
    Route::delete('/settings/agenda/excecoes/{id}', [SettingController::class, 'destroyExcecao'])->name('settings.excecoes.destroy');
});

// ===========================
// Comissões (apenas admin)
// ===========================
Route::middleware(['panel', 'role:admin'])->group(function () {
    Route::get('/comissoes', [ComissaoController::class, 'index'])->name('comissoes.index');
    Route::post('/comissoes/{id}/pagar', [ComissaoController::class, 'marcarPago'])->name('comissoes.pagar');
    Route::post('/comissoes/{id}/estornar', [ComissaoController::class, 'estornar'])->name('comissoes.estornar');
});

// ===========================
// Relatórios (agendamentos, comissões)
// ===========================
Route::prefix('relatorios')
    ->middleware(['panel','role:admin,funcionario'])
    ->group(function () {
        Route::get('/', [RelatorioController::class, 'index'])->name('relatorios.index');

        Route::get('/agendamentos', [RelatorioController::class, 'agendamentos'])->name('relatorios.agendamentos');
        Route::get('/agendamentos/pdf', [RelatorioController::class, 'agendamentosPdf'])->name('relatorios.agendamentos.pdf');

        Route::get('/comissoes', [RelatorioController::class, 'comissoes'])->name('relatorios.comissoes');
        Route::get('/comissoes/pdf', [RelatorioController::class, 'comissoesPdf'])->name('relatorios.comissoes.pdf');
    });

// ===========================
// Relatórios financeiros (APENAS ADMIN)
// ===========================
Route::middleware(['panel','role:admin'])->group(function () {
    // Faturamento
    Route::get('/relatorios/faturamento', [RelatorioController::class, 'faturamento'])
        ->name('relatorios.faturamento');

    Route::get('/feedbacks', [FeedbackController::class, 'index'])->name('feedbacks.index');
    Route::get('/feedbacks/{feedback}', [FeedbackController::class, 'show'])->name('feedbacks.show');


    Route::get('/relatorios/faturamento/pdf', [RelatorioController::class, 'faturamentoPdf'])
        ->name('relatorios.faturamento.pdf');

    // Relatório de Serviços
    Route::get('/relatorios/servicos', [RelatorioController::class, 'servicos'])
        ->name('relatorios.servicos');

    Route::get('/relatorios/servicos/pdf', [RelatorioController::class, 'servicosPdf'])
        ->name('relatorios.servicos.pdf');
});

// EOF
