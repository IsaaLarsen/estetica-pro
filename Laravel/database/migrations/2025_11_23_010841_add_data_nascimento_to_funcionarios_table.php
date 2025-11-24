<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('funcionarios', function (Blueprint $table) {
            // só adiciona se ainda não existir
            if (!Schema::hasColumn('funcionarios', 'data_nascimento')) {
                $table->date('data_nascimento')->nullable()->after('endereco');
            }
        });
    }

    public function down(): void
    {
        Schema::table('funcionarios', function (Blueprint $table) {
            if (Schema::hasColumn('funcionarios', 'data_nascimento')) {
                $table->dropColumn('data_nascimento');
            }
        });
    }
};
