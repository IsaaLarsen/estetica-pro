<?php
// app/Services/ComissaoService.php
namespace App\Services;

use App\Models\Comissao;
use Illuminate\Support\Facades\DB;

class ComissaoService
{
    public static function gerarParaAgenda(object $agenda): Comissao
    {
        return DB::transaction(function() use ($agenda) {
            // não duplica
            if ($existente = Comissao::where('agenda_id', $agenda->id)->first()) {
                return $existente;
            }

            // carrega snapshot do serviço no momento da conclusão
            $servico = DB::table('servicos')->where('id', $agenda->servico_id)->first();

            // (1) Preço: servicos.valor (decimal BR), já salvo no BD como 2 casas
            $valorServico = (float) ($servico->valor ?? 0);

            // (2) % Comissão: servicos.comissao_percent (ex.: 40.00)
            // fallback seguro pra 40.00 se não vier nada
            $percentual = isset($servico->comissao_percent)
                ? (float) $servico->comissao_percent
                : 40.00;

            $valorComissao = round($valorServico * ($percentual / 100), 2);

            return Comissao::create([
                'agenda_id'      => $agenda->id,
                'funcionario_id' => $agenda->funcionario_id,
                'servico_id'     => $agenda->servico_id,
                'valor_servico'  => $valorServico,
                'percentual'     => $percentual,
                'valor_comissao' => $valorComissao,
                'status'         => 'pendente',
                'obs'            => null,
            ]);
        });
    }

    public static function estornarPorAgendaId(int $agendaId): void
    {
        DB::transaction(function() use ($agendaId) {
            $c = Comissao::where('agenda_id', $agendaId)->first();
            if ($c && $c->status !== 'estornado') {
                $c->update(['status' => 'estornado', 'pago_em' => null]);
            }
        });
    }
}
