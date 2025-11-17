<?php
// app/Services/ComissaoService.php
namespace App\Services;

use App\Models\Comissao;
use App\Models\Agenda;
use Illuminate\Support\Facades\DB;

class ComissaoService
{
    /**
     * Gera comissão quando uma agenda vira "concluído".
     */
    public static function gerarParaAgenda(Agenda $agenda): Comissao
    {
        return DB::transaction(function () use ($agenda) {

            // EVITA DUPLICAR COMISSÃO PARA A MESMA AGENDA
            if ($existente = Comissao::where('agenda_id', $agenda->id)->first()) {
                return $existente;
            }

            // CARREGA SERVIÇO
            $servico = DB::table('servicos')
                ->where('id', $agenda->servico_id)
                ->first();

            if (!$servico) {
                throw new \RuntimeException("Serviço não encontrado ao gerar comissão (agenda_id={$agenda->id}).");
            }

            /*
            |------------------------------------------------------------------
            | VALOR DO SERVIÇO
            | - Pode vir como decimal (80.00)
            | - Pode vir como string "80,00"
            | - Pode vir como string "1.234,56"
            |------------------------------------------------------------------
            */
            $valorServico = self::toFloat($servico->valor);

            /*
            |------------------------------------------------------------------
            | PERCENTUAL DE COMISSÃO
            | - Campo servicos.comissao_percent
            | - Pode vir como 15, 15.00, "15,00", "15%"
            |------------------------------------------------------------------
            */
            $percentual = self::toFloat($servico->comissao_percent);

            // garante mínimo razoável (não negativo)
            if ($valorServico < 0) {
                $valorServico = 0.0;
            }
            if ($percentual < 0) {
                $percentual = 0.0;
            }

            // CÁLCULO FINAL
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

    /**
     * Estorna uma comissão quando um atendimento concluído é cancelado.
     */
    public static function estornarPorAgendaId(int $agendaId): void
    {
        DB::transaction(function () use ($agendaId) {
            $c = Comissao::where('agenda_id', $agendaId)->first();

            if ($c && $c->status !== 'estornado') {
                $c->update([
                    'status'  => 'estornado',
                    'pago_em' => null,
                ]);
            }
        });
    }

    /**
     * Converte valor vindo do BD ou string para float de forma segura.
     *
     * Regras:
     * - Se já for numérico (decimal do MySQL, float, int), só faz cast.
     * - Se tiver vírgula, assume formato BR: "1.234,56" -> 1234.56
     * - Se NÃO tiver vírgula, mantém ponto como decimal: "80.00" -> 80.0
     * - Remove "%" se existir.
     */
    protected static function toFloat($value): float
    {
        if ($value === null) {
            return 0.0;
        }

        // Se já é número (ex: decimal do MySQL), só converte
        if (is_int($value) || is_float($value) || is_numeric($value)) {
            return (float) $value;
        }

        $v = trim((string) $value);

        // Remove símbolo de porcentagem se houver
        $v = str_replace('%', '', $v);

        // Se tiver vírgula, tratamos como formato BR: 1.234,56
        if (strpos($v, ',') !== false) {
            // remove separador de milhar
            $v = str_replace('.', '', $v);
            // troca vírgula por ponto
            $v = str_replace(',', '.', $v);
        }
        // Se NÃO tiver vírgula, pode ser "80.00" ou "80"
        // Nesse caso não mexemos no ponto, pra não transformar "80.00" em "8000"

        return (float) $v;
    }
}
