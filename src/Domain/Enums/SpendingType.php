<?php

namespace App\Domain\Enums;

enum SpendingType: string
{
    case CHEMICALS = 'chemicals';                // Productos químicos
    case DIESEL = 'diesel';                      // Diésel
    case GASOLINE = 'gasoline';                  // Gasolina
    case PROTECTIVE_EQUIPMENT = 'protective_equipment'; // Equipo de protección
    case MACHINERY = 'machinery';                // Maquinaria y Equipo
    case OTHER = 'other';                        // Otros
    case FERTILIZER = 'fertilizer';              // Fertilizante


    public function label(): string
    {
        return match($this) {
            self::CHEMICALS => 'Productos químicos',
            self::DIESEL => 'Diesel',
            self::GASOLINE => 'Gasolina',
            self::PROTECTIVE_EQUIPMENT => 'Equipo de protección',
            self::MACHINERY => 'Maquinaria y Equipo',
            self::OTHER => 'Other',
            self::FERTILIZER => 'Fertilizante',
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
