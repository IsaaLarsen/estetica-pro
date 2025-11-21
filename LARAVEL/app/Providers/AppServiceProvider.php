<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Injeta $usuario e $nomeUsuario em TODAS as views
        View::composer('*', function ($view) {
            $u = session('usuario'); // pode ser objeto ou array

            // normaliza nome
            $nome = 'Convidado';
            if ($u) {
                if (is_object($u)) {
                    $nome = $u->nome ?? ($u->name ?? 'Usuário');
                } elseif (is_array($u)) {
                    $nome = $u['nome'] ?? ($u['name'] ?? 'Usuário');
                }
            }

            $view->with('usuario', $u);
            $view->with('nomeUsuario', $nome);
        });
    }
}
