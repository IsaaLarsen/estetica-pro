<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Collection;

class SlotService
{
    /**
     * Gera slots livres ("HH:MM") dentro de [inicioDia, fimDia],
     * removendo interseções com agendamentos e bloqueios.
     *
     * @param Carbon                   $inicioDia
     * @param Carbon                   $fimDia
     * @param int                      $duracao   minutos do serviço
     * @param Collection|array         $ocupados  itens com 'inicio','fim'
     * @param Collection|array         $bloqueios itens com 'inicio','fim'
     * @param int                      $passo     minutos entre sugestões (default: 15)
     * @return array<string>
     */
    public static function gerar(
        Carbon $inicioDia,
        Carbon $fimDia,
        int $duracao,
        Collection|array $ocupados = [],
        Collection|array $bloqueios = [],
        int $passo = 15
    ): array {
        $tz = $inicioDia->getTimezone();
        $slots = [];

        // Normaliza para array simples
        if ($ocupados instanceof Collection) $ocupados = $ocupados->all();
        if ($bloqueios instanceof Collection) $bloqueios = $bloqueios->all();

        // Monta lista de intervalos ocupados
        $ranges = [];
        foreach ($ocupados as $a) {
            $ranges[] = [
                'ini' => $a->inicio instanceof Carbon ? $a->inicio->copy() : Carbon::parse($a->inicio, $tz),
                'fim' => $a->fim    instanceof Carbon ? $a->fim->copy()    : Carbon::parse($a->fim,    $tz),
            ];
        }
        foreach ($bloqueios as $b) {
            $ranges[] = [
                'ini' => $b->inicio instanceof Carbon ? $b->inicio->copy() : Carbon::parse($b->inicio, $tz),
                'fim' => $b->fim    instanceof Carbon ? $b->fim->copy()    : Carbon::parse($b->fim,    $tz),
            ];
        }

        // Avança em PASSO fixo (15 min por padrão),
        // mas só considera slot válido quando [ini,ini+duracao] cabe inteiro no expediente
        $cursor = $inicioDia->copy();
        while ($cursor->copy()->addMinutes($duracao)->lte($fimDia)) {
            $ini = $cursor->copy();
            $fim = $cursor->copy()->addMinutes($duracao);

            $livre = true;
            foreach ($ranges as $r) {
                if (self::intersecta($ini, $fim, $r['ini'], $r['fim'])) {
                    $livre = false; break;
                }
            }

            if ($livre) {
                $slots[] = $ini->format('H:i');
            }

            $cursor->addMinutes($passo);
        }

        return $slots;
    }

    private static function intersecta(Carbon $aIni, Carbon $aFim, Carbon $bIni, Carbon $bFim): bool
    {
        // Interseção aberta: existe choque se início < fimDoOutro e fim > inícioDoOutro
        return $aIni->lt($bFim) && $aFim->gt($bIni);
    }
}
