<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('agenda_bloqueios', function (Blueprint $table) {
            $table->boolean('aplicar_todos')
                  ->default(false)
                  ->after('id'); // pode mudar o position se quiser
        });
    }

    public function down(): void
    {
        Schema::table('agenda_bloqueios', function (Blueprint $table) {
            $table->dropColumn('aplicar_todos');
        });
    }
};
