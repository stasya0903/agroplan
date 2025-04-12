<?php

namespace App\Domain;

enum SystemWorkType: int
{
    case HARVEST = 1;
    case IRRIGATION = 2;
    case FERTILIZATION = 3;
    case EVENING_WORK = 4;
    case PRUNING = 5;
    case OTHER = 6;

    public function label(): string
    {
        return match ($this) {
            self::HARVEST => 'corte',
            self::IRRIGATION => 'regada',
            self::FERTILIZATION => 'fumigada',
            self::EVENING_WORK => 'tardeada',
            self::PRUNING => 'podada',
            self::OTHER => 'otro'
        };
    }

    public static function ids(): array
    {
        return array_map(fn($enum) => $enum->value, self::cases());
    }
    public static function names(): array
    {
        return array_map(fn($enum) => $enum->label(), self::cases());
    }

    public static function isSystemId(int $id): bool
    {
        return in_array($id, self::ids(), true);
    }
    public static function isSystemName(string $name): bool
    {
        return in_array(strtolower($name), self::names(), true);
    }
}
