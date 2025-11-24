<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('usuarios', function (Blueprint $table) {

            // se ainda não existir, cria
            if (!Schema::hasColumn('usuarios', 'precisa_trocar_senha')) {
                $table->boolean('precisa_trocar_senha')->default(false);
            }

            if (!Schema::hasColumn('usuarios', 'tentativas_falhas')) {
                $table->integer('tentativas_falhas')->default(0);
            }

            if (!Schema::hasColumn('usuarios', 'bloqueado')) {
                $table->boolean('bloqueado')->default(false);
            }
        });
    }

    public function down(): void
    {
        Schema::table('usuarios', function (Blueprint $table) {

            if (Schema::hasColumn('usuarios', 'precisa_trocar_senha')) {
                $table->dropColumn('precisa_trocar_senha');
            }

            if (Schema::hasColumn('usuarios', 'tentativas_falhas')) {
                $table->dropColumn('tentativas_falhas');
            }

            if (Schema::hasColumn('usuarios', 'bloqueado')) {
                $table->dropColumn('bloqueado');
            }
        });
    }
};
