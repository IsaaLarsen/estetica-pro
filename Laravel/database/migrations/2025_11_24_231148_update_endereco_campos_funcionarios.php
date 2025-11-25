<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('funcionarios', function (Blueprint $table) {
            // Remove coluna antiga
            if (Schema::hasColumn('funcionarios', 'endereco')) {
                $table->dropColumn('endereco');
            }

            // Novas colunas
            $table->string('cep', 9)->nullable()->after('telefone');
            $table->string('rua')->nullable()->after('cep');
            $table->string('bairro')->nullable()->after('rua');
            $table->string('numero', 10)->nullable()->after('bairro');
        });
    }

    public function down(): void
    {
        Schema::table('funcionarios', function (Blueprint $table) {
            // Restaura antigo
            $table->string('endereco')->nullable();

            // Remove novos
            $table->dropColumn(['cep', 'rua', 'bairro', 'numero']);
        });
    }
};
