<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('logs', function (Blueprint $table) {
            // só adiciona se ainda não existir
            if (!Schema::hasColumn('logs', 'details')) {
                // pode ser json se teu MySQL suportar, mas longText é mais seguro
                $table->longText('details')->nullable()->after('action');
            }
        });
    }

    public function down(): void
    {
        Schema::table('logs', function (Blueprint $table) {
            if (Schema::hasColumn('logs', 'details')) {
                $table->dropColumn('details');
            }
        });
    }
};
