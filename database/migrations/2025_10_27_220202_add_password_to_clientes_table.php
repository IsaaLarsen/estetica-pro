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
    Schema::table('clientes', function (Blueprint $table) {
        if (!Schema::hasColumn('clientes','password')) {
            $table->string('password')->nullable()->after('email'); // vamos popular depois
        }
        if (!Schema::hasColumn('clientes','remember_token')) {
            $table->rememberToken()->nullable();
        }
    });
}

public function down(): void
{
    Schema::table('clientes', function (Blueprint $table) {
        if (Schema::hasColumn('clientes','password')) $table->dropColumn('password');
        if (Schema::hasColumn('clientes','remember_token')) $table->dropColumn('remember_token');
    });
}

};
