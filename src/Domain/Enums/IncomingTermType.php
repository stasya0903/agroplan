<?php

namespace App\Domain\Enums;

enum IncomingTermType: int
{
    case CONTADO = 1;
    case OCHO_DIAS = 2;
    case QUINCE_DIAS = 3;
    case VEINTIDOS_DIAS = 4;

    public static function values(): array
    {
        return array_map(fn(self $term) => $term->value, self::cases());
    }

    public function label(): string
    {
        return match($this) {
            self::CONTADO => 'Contado',
            self::OCHO_DIAS => '8 días',
            self::QUINCE_DIAS => '15 días',
            self::VEINTIDOS_DIAS => '22 días',
        };
    }
}
