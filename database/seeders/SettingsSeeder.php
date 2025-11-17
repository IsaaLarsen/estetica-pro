<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Setting;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        Setting::set('expediente_inicio', '08:00'); // HH:MM
        Setting::set('expediente_fim',    '18:00'); // HH:MM (pode mudar depois na tela)
    }
}
