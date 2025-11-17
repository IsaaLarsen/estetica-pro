<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clientes', function (Blueprint $table) {
            // cria a coluna "senha" se ainda não existir
            if (!Schema::hasColumn('clientes', 'senha')) {
                $table->string('senha')->after('email');
            }
        });
    }

    public function down(): void
    {
        Schema::table('clientes', function (Blueprint $table) {
            if (Schema::hasColumn('clientes', 'senha')) {
                $table->dropColumn('senha');
            }
        });
    }
};
