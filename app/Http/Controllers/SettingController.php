<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function edit()
    {
        $inicio = Setting::get('expediente_inicio', '08:00');
        $fim    = Setting::get('expediente_fim',    '18:00');
        return view('settings.edit', compact('inicio','fim'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'expediente_inicio' => 'required|date_format:H:i',
            'expediente_fim'    => 'required|date_format:H:i',
        ]);

        // Opcional: validar que fim > inicio
        if (strtotime($request->expediente_fim) <= strtotime($request->expediente_inicio)) {
            return back()->withErrors(['expediente_fim'=>'O fim deve ser maior que o início.'])->withInput();
        }

        Setting::set('expediente_inicio', $request->expediente_inicio);
        Setting::set('expediente_fim',    $request->expediente_fim);

        return redirect()->route('settings.edit')->with('success','Configurações salvas!');
    }
}
