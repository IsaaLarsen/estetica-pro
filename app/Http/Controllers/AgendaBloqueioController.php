<?php

namespace App\Http\Controllers;

use App\Models\AgendaBloqueio;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AgendaBloqueioController extends Controller
{
    public function index()
    {
        $bloqueios = AgendaBloqueio::with('funcionario')->orderByDesc('inicio')->paginate(20);
        return view('agenda_bloqueios.index', compact('bloqueios'));
    }

    public function create()
    {
        $funcionarios = DB::table('funcionarios')->orderBy('nome')->get();
        return view('agenda_bloqueios.create', compact('funcionarios'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'funcionario_id' => 'required|exists:funcionarios,id',
            'data_inicio'    => 'required|date',
            'hora_inicio'    => 'required',
            'data_fim'       => 'required|date',
            'hora_fim'       => 'required',
            'motivo'         => 'nullable|string|max:255'
        ]);

        $inicio = Carbon::parse($request->data_inicio.' '.$request->hora_inicio);
        $fim    = Carbon::parse($request->data_fim   .' '.$request->hora_fim);

        if ($fim->lte($inicio)) {
            return back()->withErrors(['data_fim'=>'Fim deve ser após o início.'])->withInput();
        }

        AgendaBloqueio::create([
            'funcionario_id'=>$request->funcionario_id,
            'inicio'=>$inicio,
            'fim'=>$fim,
            'motivo'=>$request->motivo
        ]);

        return redirect()->route('agenda.bloqueios.index')->with('success','Bloqueio criado!');
    }

    public function destroy(AgendaBloqueio $bloqueio)
    {
        $bloqueio->delete();
        return redirect()->route('agenda.bloqueios.index')->with('success','Bloqueio removido.');
    }
}
