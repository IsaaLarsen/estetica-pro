<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Só adiciona a coluna se ainda não existir
        if (!Schema::hasColumn('clientes', 'senha')) {
            Schema::table('clientes', function (Blueprint $table) {
                $table->string('senha')
                      ->nullable()
                      ->after('data_nascimento');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clientes', function (Blueprint $table) {
            // remove a coluna senha caso exista
            $table->dropColumn('senha');
        });
    }
};
