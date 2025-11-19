<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UsuarioAdminSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('usuarios')->insert([
            'nome' => 'Administrador',
            'cpf' => '000.000.000-00',
            'senha' => Hash::make('admin123'),
            'tipo' => 'admin',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
