<?php

namespace App\Domain\Enums;

enum SpendingType: int
{
    case CHEMICALS = 1;                // Productos químicos
    case DIESEL = 2;                      // Diésel
    case GASOLINE = 3;                  // Gasolina
    case PROTECTIVE_EQUIPMENT = 4; // Equipo de protección
    case MACHINERY = 5;                // Maquinaria y Equipo
    case OTHER = 6;                        // Otros
    case FERTILIZER = 7;          // Fertilizante

    case WORK = 8;

    public function label(): string
    {
        return match ($this) {
            self::CHEMICALS => 'Productos químicos',
            self::DIESEL => 'Diesel',
            self::GASOLINE => 'Gasolina',
            self::PROTECTIVE_EQUIPMENT => 'Equipo de protección',
            self::MACHINERY => 'Maquinaria y Equipo',
            self::OTHER => 'Other',
            self::FERTILIZER => 'Fertilizante',
            self::WORK => 'Obra de mano',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function options(): array
    {
        return array_map(fn($case) => [
            'value' => $case->value,
            'label' => $case->label(),
        ], self::cases());
    }
}
