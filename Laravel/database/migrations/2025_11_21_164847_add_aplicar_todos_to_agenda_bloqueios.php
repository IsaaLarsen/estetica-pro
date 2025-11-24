<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('agenda_bloqueios', function (Blueprint $table) {
            if (!Schema::hasColumn('agenda_bloqueios', 'aplicar_todos')) {
                $table->boolean('aplicar_todos')->default(0)->after('motivo');
            }
        });
    }

    public function down(): void
    {
        Schema::table('agenda_bloqueios', function (Blueprint $table) {
            if (Schema::hasColumn('agenda_bloqueios', 'aplicar_todos')) {
                $table->dropColumn('aplicar_todos');
            }
        });
    }
};
