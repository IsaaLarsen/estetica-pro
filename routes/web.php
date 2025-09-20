<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\FuncionarioController;
use App\Http\Controllers\ServicoController;

Route::get('/', function () {
    return redirect()->route('login');
});

// Login
Route::get('/login', [LoginController::class, 'index'])->name('login');
Route::post('/login', [LoginController::class, 'autenticar'])->name('login.autenticar');
Route::get('/dashboard', [LoginController::class, 'dashboard'])->name('dashboard');
Route::get('/logout', [LoginController::class, 'logout'])->name('logout');

// (opcional) garante que {funcionario} e {servico} sejam numéricos
Route::pattern('funcionario', '[0-9]+');
Route::pattern('servico', '[0-9]+');

// Funcionários CRUD (sem show)
Route::resource('funcionarios', FuncionarioController::class)->except(['show']);

// Serviços CRUD (sem show)
Route::resource('servicos', ServicoController::class)->except(['show']);
