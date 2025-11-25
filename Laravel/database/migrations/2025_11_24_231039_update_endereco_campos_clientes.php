<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clientes', function (Blueprint $table) {
            // Remove a coluna antiga
            if (Schema::hasColumn('clientes', 'endereco')) {
                $table->dropColumn('endereco');
            }

            // Adiciona os novos campos
            $table->string('cep', 9)->nullable()->after('telefone');
            $table->string('rua')->nullable()->after('cep');
            $table->string('bairro')->nullable()->after('rua');
            $table->string('numero', 10)->nullable()->after('bairro');
        });
    }

    public function down(): void
    {
        Schema::table('clientes', function (Blueprint $table) {
            // Restaura o campo antigo
            $table->string('endereco')->nullable();

            // Remove os novos campos
            $table->dropColumn(['cep', 'rua', 'bairro', 'numero']);
        });
    }
};
