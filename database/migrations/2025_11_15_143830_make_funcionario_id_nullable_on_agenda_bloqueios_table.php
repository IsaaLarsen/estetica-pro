<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('agenda_bloqueios', function (Blueprint $table) {
            // torna funcionario_id opcional
            $table->unsignedBigInteger('funcionario_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('agenda_bloqueios', function (Blueprint $table) {
            // volta a ser obrigatório (se precisar fazer rollback)
            $table->unsignedBigInteger('funcionario_id')->nullable(false)->change();
        });
    }
};
