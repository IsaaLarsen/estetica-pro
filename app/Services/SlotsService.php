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
     * @param Carbon $inicioDia
     * @param Carbon $fimDia
     * @param int $duracao minutos
     * @param Collection $ocupados itens com 'inicio','fim'
     * @param Collection $bloqueios itens com 'inicio','fim'
     * @return array<string>
     */
    public static function gerar(
        Carbon $inicioDia,
        Carbon $fimDia,
        int $duracao,
        Collection $ocupados,
        Collection $bloqueios
    ): array {
        $tz = $inicioDia->getTimezone();
        $slots = [];
        $cursor = $inicioDia->copy();

        // Normaliza ranges
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

        while ($cursor->lte($fimDia->copy()->subMinutes($duracao))) {
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

            $cursor->addMinutes($duracao);
        }

        return $slots;
    }

    private static function intersecta(Carbon $aIni, Carbon $aFim, Carbon $bIni, Carbon $bFim): bool
    {
        return $aIni->lt($bFim) && $aFim->gt($bIni);
    }
}
